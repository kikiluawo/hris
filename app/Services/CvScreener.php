<?php

namespace App\Services;

use App\Models\JobOpening;

class CvScreener
{
    public const VERSION = 3;

    private const WEIGHTS = [
        'skills' => 60,
        'education' => 25,
        'experience' => 15,
    ];

    private const EDUCATION_RANK = [
        'SMA/SMK' => 1,
        'D1' => 2,
        'D2' => 3,
        'D3' => 4,
        'D4' => 5,
        'S1' => 6,
        'S2' => 7,
        'S3' => 8,
    ];

    private const EDUCATION_PATTERNS = [
        'S3' => ['/\bs[\s\-]?3\b/', '/\bdoktor\b/', '/\bdoctoral\b/', '/\bdoctorate\b/'],
        'S2' => ['/\bs[\s\-]?2\b/', '/\bmagister\b/', '/\bmaster\b/', '/\bm\.?\s?sc\b/', '/\bpascasarjana\b/'],
        'S1' => ['/\bs[\s\-]?1\b/', '/\bsarjana(?!\s+terapan)\b/', '/\bbachelor\b/', '/\bundergraduate\b/', '/\bs\.?\s?kom\b/', '/\bs\.?\s?t\b/', '/\bs\.?\s?e\b/', '/\bs\.?\s?pd\b/', '/\bs\.?\s?h\b/', '/\bs\.?\s?sos\b/', '/\bs\.?\s?ab\b/'],
        'D4' => ['/\bd[\s\-]?4\b/', '/\bdiploma\s*\(?\s*iv\s*\)?/', '/\bdiploma\s*4\b/', '/\bsarjana\s+terapan\b/', '/\bd[\s\-]?iv\b/'],
        'D3' => ['/\bd[\s\-]?3\b/', '/\bdiploma\s*\(?\s*iii\s*\)?/', '/\bdiploma\s*3\b/', '/\bahli\s+madya\b/'],
        'D2' => ['/\bd[\s\-]?2\b/', '/\bdiploma\s*\(?\s*ii\s*\)?/', '/\bdiploma\s*2\b/'],
        'D1' => ['/\bd[\s\-]?1\b/', '/\bdiploma\s*\(?\s*i\s*\)?/', '/\bdiploma\s*1\b/'],
        'SMA/SMK' => ['/\bsma\b/', '/\bsmk\b/', '/\bsmu\b/', '/\bsekolah\s+menengah\s+atas\b/', '/\bhigh\s+school\b/', '/\bsenior\s+high\b/'],
    ];

    private const STOPWORDS = [
        'dan', 'atau', 'serta', 'dengan', 'untuk', 'dari', 'yang', 'pada', 'dalam', 'secara',
        'di', 'ke', 'the', 'and', 'or', 'with', 'for', 'of', 'to', 'in',
        'basic', 'dasar', 'menguasai', 'mahir', 'mampu', 'terampil', 'level', 'junior', 'senior', 'staff', 'staf',
        'memahami', 'memiliki', 'melakukan', 'menjelaskan', 'mengoperasikan', 'menawarkan', 'kemampuan', 'mengenal',
        'having', 'able', 'understanding', 'knowledge', 'skill', 'skills', 'strong', 'good',
    ];

    private ?array $synonymTerms = null;

    private ?array $synonymPhrases = null;

    public function __construct(private CvTextExtractor $extractor)
    {
    }

    public function screen(string $path, string $extension, JobOpening $opening): array
    {
        $screening = $this->baseResult($extension);

        $extraction = $this->extractor->extract($path, $extension);

        if ($extraction['text'] === null) {
            return $this->fail($screening, $extraction['reason'] ?? 'CV tidak bisa dibaca otomatis. Tinjau manual.');
        }

        return $this->screenText($extraction['text'], $extraction['source'], $opening, $screening);
    }

    public function screenText(string $text, ?string $source, JobOpening $opening, ?array $screening = null): array
    {
        $screening ??= $this->baseResult('text');
        $screening['text_extracted'] = true;
        $screening['text_source'] = $source;
        $screening['text_chars'] = mb_strlen($text);
        $haystack = mb_strtolower($text);

        $skills = $this->screenSkills($haystack, $opening);
        $education = $this->screenEducation($haystack, $opening);
        $experience = $this->screenExperience($haystack, $opening);

        $screening['skills'] = $skills;
        $screening['education'] = $education;
        $screening['experience'] = $experience;

        $score = $this->score($skills, $education, $experience);

        if ($score === null) {
            $screening['reason'] = 'Lowongan belum punya kriteria kualifikasi, skor tidak dihitung.';
        }

        $screening['score'] = $score;
        $screening['needs_review'] = $score === null;
        $screening['notes'] = $this->notes($skills, $education, $experience);

        return $screening;
    }

    private function baseResult(string $extension): array
    {
        return [
            'version' => self::VERSION,
            'file_type' => $extension,
            'text_extracted' => false,
            'text_source' => null,
            'text_chars' => 0,
            'needs_review' => false,
            'score' => null,
            'skills' => [
                'required' => [],
                'matched' => [],
                'missing' => [],
                'ratio' => null,
            ],
            'education' => [
                'required' => null,
                'detected' => null,
                'passed' => null,
            ],
            'experience' => [
                'required' => null,
                'detected' => null,
                'passed' => null,
            ],
            'notes' => [],
        ];
    }

    private function fail(array $screening, string $reason): array
    {
        $screening['score'] = null;
        $screening['needs_review'] = true;
        $screening['reason'] = $reason;
        $screening['notes'] = [$reason];

        return $screening;
    }

    private function screenSkills(string $haystack, JobOpening $opening): array
    {
        $required = array_values(array_filter(array_map(
            fn ($skill) => trim(preg_replace('/^[\s\-•*·]+/u', '', (string) $skill) ?? ''),
            (array) ($opening->required_skills ?? [])
        ), fn ($skill) => $skill !== ''));

        if ($required === []) {
            return [
                'required' => [],
                'matched' => [],
                'missing' => [],
                'ratio' => null,
            ];
        }

        $haystack = $this->normalizeForMatch($haystack);
        $matched = [];
        $missing = [];

        foreach ($required as $skill) {
            $skill = trim((string) $skill);

            if ($skill === '') {
                continue;
            }

            if ($this->matchesSkill($haystack, $skill)) {
                $matched[] = $skill;
            } else {
                $missing[] = $skill;
            }
        }

        $total = count($matched) + count($missing);

        return [
            'required' => $required,
            'matched' => $matched,
            'missing' => $missing,
            'ratio' => $total > 0 ? round(count($matched) / $total, 2) : null,
        ];
    }

    private function normalizeForMatch(string $value): string
    {
        $value = str_replace(['–', '—', '−'], '-', mb_strtolower($value));
        $value = preg_replace('/[^a-z0-9+#]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function matchesSkill(string $haystack, string $skill): bool
    {
        $lower = $this->normalizeForMatch($skill);

        if ($lower === '') {
            return false;
        }

        if ($this->matchesAny($haystack, array_merge([$lower], $this->synonymPhrases()[$lower] ?? []))) {
            return true;
        }

        $words = preg_split('/[^a-z0-9+#]+/u', $lower, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) === 1) {
            return $this->matchesAny($haystack, $this->wordVariants($words[0]));
        }

        $tokens = array_values(array_unique(array_filter(
            $words,
            fn ($word) => mb_strlen($word) >= 4 && ! in_array($word, self::STOPWORDS, true)
        )));

        if (count($tokens) < 2) {
            return false;
        }

        $found = 0;

        foreach ($tokens as $token) {
            if ($this->matchesAny($haystack, $this->wordVariants($token))) {
                $found++;
            }
        }

        if (count($words) < 5) {
            return $found === count($tokens);
        }

        return $found >= max(2, (int) ceil(count($tokens) * 0.6));
    }

    private function wordVariants(string $word): array
    {
        return array_values(array_unique(array_merge([$word], $this->synonymTerms()[$word] ?? [])));
    }

    private function matchesAny(string $haystack, array $variants): bool
    {
        foreach ($variants as $variant) {
            if ($this->containsPhrase($haystack, $variant)) {
                return true;
            }
        }

        return false;
    }

    private function synonymTerms(): array
    {
        if ($this->synonymTerms !== null) {
            return $this->synonymTerms;
        }

        $terms = [];

        foreach ((array) config('skill-synonyms', []) as $group) {
            foreach ((array) ($group['terms'] ?? []) as $term => $synonyms) {
                $key = $this->normalizeForMatch((string) $term);

                if ($key === '') {
                    continue;
                }

                $variants = array_values(array_filter(array_map(
                    fn ($synonym) => $this->normalizeForMatch((string) $synonym),
                    (array) $synonyms
                )));

                $terms[$key] = array_values(array_unique(array_merge($terms[$key] ?? [], $variants)));
            }
        }

        return $this->synonymTerms = $terms;
    }

    private function synonymPhrases(): array
    {
        if ($this->synonymPhrases !== null) {
            return $this->synonymPhrases;
        }

        $phrases = [];

        foreach ((array) config('skill-synonyms', []) as $group) {
            foreach ((array) ($group['phrases'] ?? []) as $phrase => $synonyms) {
                $key = $this->normalizeForMatch((string) $phrase);

                if ($key === '') {
                    continue;
                }

                $variants = array_values(array_filter(array_map(
                    fn ($synonym) => $this->normalizeForMatch((string) $synonym),
                    (array) $synonyms
                )));

                $phrases[$key] = array_values(array_unique(array_merge($phrases[$key] ?? [], $variants)));
            }
        }

        return $this->synonymPhrases = $phrases;
    }

    private function containsPhrase(string $haystack, string $phrase): bool
    {
        $phrase = trim($phrase);

        if ($phrase === '') {
            return false;
        }

        $pattern = str_replace(' ', '\s+', preg_quote($phrase, '/'));

        return (bool) preg_match('/(?<![a-z0-9])' . $pattern . '(?![a-z0-9])/u', $haystack);
    }

    private function cleanHaystackForEducation(string $haystack): string
    {
        $lines = preg_split('/\r\n|\r|\n/u', $haystack) ?: [];
        $cleanedLines = [];
        $inSignatureBlock = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/\b(?:direktur|wakil\s+direktur|dekan|rektor|kaprodi|ketua\s+jurusan|dosen\s+pembimbing|pembimbing|penguji|mengetahui)\b/ui', $trimmed)) {
                $inSignatureBlock = true;
                continue;
            }

            if ($inSignatureBlock) {
                if (preg_match('/\b(?:ir\b|prof\b|dr\b|nip\b|\d{10,})/ui', $trimmed) || preg_match('/,[A-Za-z\.,\s]+/', $trimmed)) {
                    continue;
                } else {
                    $inSignatureBlock = false;
                }
            }

            if (preg_match('/\b(?:ir\s*\.?|prof\s*\.?)\s+[a-z\s]+(?:,\s*[a-z\.]+)+/ui', $trimmed)) {
                continue;
            }

            if (preg_match('/\b(?:nip|nidn)\s*[:\.]?\s*\d+/ui', $trimmed)) {
                continue;
            }

            $cleanedLines[] = $line;
        }

        $cleaned = implode("\n", $cleanedLines);

        $cleaned = preg_replace('/\b(?:ir\s*\.?|prof\s*\.?)\s+[a-z\s\.]+(?:,\s*[a-z\.\s]+){1,}/ui', ' ', $cleaned) ?? $cleaned;

        return $cleaned;
    }

    private function screenEducation(string $haystack, JobOpening $opening): array
    {
        $required = $opening->min_education ?: null;
        $detected = null;

        $cleanedHaystack = $this->cleanHaystackForEducation($haystack);

        if (preg_match('/\b(?:jenjang\s+studi|program\s+studi|pendidikan(?:\s+terakhir)?)\s*[:\-]?\s*([^\n\r,]+)/ui', $cleanedHaystack, $m)) {
            $explicitText = mb_strtolower(trim($m[1]));
            foreach (self::EDUCATION_PATTERNS as $level => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $explicitText)) {
                        $detected = $level;
                        break 2;
                    }
                }
            }
        }

        if ($detected === null) {
            foreach (self::EDUCATION_PATTERNS as $level => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $cleanedHaystack)) {
                        $detected = $level;
                        break 2;
                    }
                }
            }
        }

        $passed = null;

        if ($required !== null) {
            $passed = $detected !== null
                && (self::EDUCATION_RANK[$detected] ?? 0) >= (self::EDUCATION_RANK[$required] ?? PHP_INT_MAX);
        }

        return [
            'required' => $required,
            'detected' => $detected,
            'passed' => $passed,
        ];
    }

    private function screenExperience(string $haystack, JobOpening $opening): array
    {
        $required = $opening->min_experience !== null ? (int) $opening->min_experience : null;
        $detected = $this->detectExperience($haystack);

        $passed = null;

        if ($required !== null) {
            $passed = $detected !== null && $detected >= $required;
        }

        return [
            'required' => $required,
            'detected' => $detected,
            'passed' => $passed,
        ];
    }

    private function detectExperience(string $haystack): ?float
    {
        $explicitText = preg_replace('/(?:usia|umur)\s*[:\-]?\s*\d{1,2}\s*tahun/u', ' ', $haystack) ?? $haystack;
        $explicit = 0.0;

        if (preg_match_all('/(\d{1,2})(?:[.,](\d))?\s*(?:\+)?\s*(?:tahun|thn|th\b|years?|yrs?)/u', $explicitText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $value = (float) $match[1] + (isset($match[2]) && $match[2] !== '' ? (float) ('0.' . $match[2]) : 0.0);
                $explicit = max($explicit, $value);
            }
        }

        $lines = array_values(array_filter(array_map('trim', explode("\n", $haystack))));
        $intervals = [];
        $month = '(?:jan(?:uari|uary)?|feb(?:ruari|ruary)?|mar(?:et|ch)?|apr(?:il)?|mei|may|jun(?:i|e)?|jul(?:i|y)?|agu(?:stus)?|aug(?:ust)?|sep(?:tember)?|okt(?:ober)?|oct(?:ober)?|nov(?:ember)?|des(?:ember)?|dec(?:ember)?)';
        $rangePattern = '/\b(?:(?<start_month>' . $month . ')\.?\s*)?(?<start_year>(?:19|20)\d{2})\s*(?:-|–|—|to|sampai|hingga|s\.?d\.?)\s*(?:(?<end_month>' . $month . ')\.?\s*)?(?<end_year>(?:19|20)\d{2}|sekarang|present|now|current|saat ini)\b/u';

        foreach ($lines as $index => $line) {
            if (! preg_match_all($rangePattern, $line, $ranges, PREG_SET_ORDER)) {
                continue;
            }

            if ($this->looksLikeEducation(mb_strtolower($line))) {
                continue;
            }

            $bare = preg_match('/[a-z]{3,}/u', preg_replace('/\b(?:sekarang|present|now|current|saat ini|to|sd|s\.d\.?)\b/u', '', $line) ?? '') !== 1;

            if ($bare && $index > 0 && $this->looksLikeEducation(mb_strtolower($lines[$index - 1]))) {
                continue;
            }

            foreach ($ranges as $range) {
                $startMonth = ($range['start_month'] ?? '') !== '' ? ($this->monthIndex($range['start_month']) ?? 0) : 0;
                $endYear = mb_strtolower($range['end_year']);

                if (in_array($endYear, ['sekarang', 'present', 'now', 'current', 'saat ini'], true)) {
                    $endMonth = (int) now()->format('n') - 1;
                    $endYearValue = (int) now()->format('Y');
                } else {
                    $endMonth = ($range['end_month'] ?? '') !== '' ? ($this->monthIndex($range['end_month']) ?? 11) : 11;
                    $endYearValue = (int) $endYear;
                }

                $startIndex = (int) $range['start_year'] * 12 + $startMonth;
                $endIndex = $endYearValue * 12 + $endMonth;

                if ($endIndex < $startIndex) {
                    continue;
                }

                $intervals[] = [$startIndex, $endIndex];
            }
        }

        $months = 0;

        if ($intervals !== []) {
            usort($intervals, fn ($a, $b) => $a[0] <=> $b[0]);
            [$start, $end] = array_shift($intervals);

            foreach ($intervals as [$nextStart, $nextEnd]) {
                if ($nextStart <= $end + 1) {
                    $end = max($end, $nextEnd);
                } else {
                    $months += $end - $start + 1;
                    [$start, $end] = [$nextStart, $nextEnd];
                }
            }

            $months += $end - $start + 1;
        }

        $detected = min(45, max($explicit, $months / 12));

        return $detected > 0 ? round($detected, 1) : null;
    }

    private function monthIndex(string $value): ?int
    {
        $key = mb_substr(mb_strtolower(trim($value)), 0, 3);

        return [
            'jan' => 0, 'feb' => 1, 'mar' => 2, 'apr' => 3,
            'mei' => 4, 'may' => 4, 'jun' => 5, 'jul' => 6,
            'agu' => 7, 'aug' => 7, 'sep' => 8, 'okt' => 9,
            'oct' => 9, 'nov' => 10, 'des' => 11, 'dec' => 11,
        ][$key] ?? null;
    }

    private function looksLikeEducation(string $window): bool
    {
        return (bool) preg_match('/\b(?:pendidikan|education|universitas|university|politeknik|sekolah|sma|smk|smu|sarjana|bachelor|magister|master|diploma|kuliah|college|academy|akademi|fakultas|s[\s\-]?[123]|d[\s\-]?[1-4])\b/u', $window);
    }

    private function score(array $skills, array $education, array $experience): ?int
    {
        $totalWeight = 0.0;
        $earned = 0.0;

        if ($skills['ratio'] !== null) {
            $totalWeight += self::WEIGHTS['skills'];
            $earned += self::WEIGHTS['skills'] * $skills['ratio'];
        }

        if ($education['required'] !== null) {
            $totalWeight += self::WEIGHTS['education'];
            $earned += $education['passed'] ? self::WEIGHTS['education'] : 0;
        }

        if ($experience['required'] !== null) {
            $totalWeight += self::WEIGHTS['experience'];
            $ratio = 1.0;

            if ($experience['required'] > 0) {
                $ratio = min(1.0, ($experience['detected'] ?? 0) / $experience['required']);
            }

            $earned += self::WEIGHTS['experience'] * $ratio;
        }

        if ($totalWeight <= 0) {
            return null;
        }

        return (int) round($earned / $totalWeight * 100);
    }

    private function notes(array $skills, array $education, array $experience): array
    {
        $notes = [];

        if ($skills['required'] !== []) {
            $matched = count($skills['matched']);
            $total = count($skills['required']);
            $note = "Keahlian cocok {$matched}/{$total}.";

            if ($skills['missing'] !== []) {
                $missing = array_map(fn ($skill) => rtrim($skill, '. '), $skills['missing']);
                $note .= ' Belum terdeteksi: ' . implode('; ', $missing) . '.';
            }

            $notes[] = $note;
        }

        if ($education['required'] !== null) {
            if ($education['detected'] === null) {
                $notes[] = "Pendidikan minimal {$education['required']} belum terdeteksi pada CV.";
            } elseif ($education['passed']) {
                $notes[] = "Pendidikan terdeteksi {$education['detected']}, memenuhi minimal {$education['required']}.";
            } else {
                $notes[] = "Pendidikan terdeteksi {$education['detected']}, di bawah minimal {$education['required']}.";
            }
        }

        if ($experience['required'] !== null) {
            $detected = $experience['detected'] !== null ? $experience['detected'] . ' tahun' : null;

            if ($detected === null) {
                $notes[] = "Pengalaman minimal {$experience['required']} tahun belum terdeteksi.";
            } elseif ($experience['passed']) {
                $notes[] = "Pengalaman terdeteksi {$detected}, memenuhi minimal {$experience['required']} tahun.";
            } else {
                $notes[] = "Pengalaman terdeteksi {$detected}, di bawah minimal {$experience['required']} tahun.";
            }
        }

        return $notes;
    }
}
