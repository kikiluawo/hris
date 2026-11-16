<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;

class AptitudeScorer
{
    
    public const DOMAIN_VERBAL = 'verbal';
    public const DOMAIN_NUMERIC = 'numeric';
    public const DOMAIN_LOGIC = 'logic';
    public const DOMAIN_SPATIAL = 'spatial';

    protected const NORM_30_SCALE = [
        30 => 142,
        29 => 138,
        28 => 134,
        27 => 130,
        26 => 127,
        25 => 124,
        24 => 121,
        23 => 118,
        22 => 115,
        21 => 112,
        20 => 110,
        19 => 107,
        18 => 105,
        17 => 103,
        16 => 100,
        15 => 98,
        14 => 95,
        13 => 92,
        12 => 89,
        11 => 86,
        10 => 83,
        9  => 80,
        8  => 77,
        7  => 74,
        6  => 71,
        5  => 68,
        4  => 65,
        3  => 63,
        2  => 61,
        1  => 60,
        0  => 58,
    ];

    public function calculate(AssessmentAttempt $attempt): ?array
    {
        $assessment = $attempt->invitation?->assessment;
        if ($assessment === null) {
            return null;
        }

        $isAptitude = $attempt->hasAptitudeQuestions();
        if (! $isAptitude) {
            return null;
        }

        $query = $assessment->questions()
            ->where('type', AssessmentQuestion::TYPE_MULTIPLE_CHOICE)
            ->whereNotNull('correct_option');

        if ($attempt->hasAccuracyQuestions() || $assessment->questions()->where('dimension', 'like', '%ketelitian%')->exists()) {
            $query->where(function ($q) {
                $q->where('dimension', 'like', '%verbal%')
                    ->orWhere('dimension', 'like', '%numerik%')
                    ->orWhere('dimension', 'like', '%angka%')
                    ->orWhere('dimension', 'like', '%logika%')
                    ->orWhere('dimension', 'like', '%penalaran%')
                    ->orWhere('dimension', 'like', '%spasial%')
                    ->orWhere('dimension', 'like', '%figural%');
            });
        }

        $questions = $query->orderBy('position')->orderBy('id')->get();

        if ($questions->isEmpty()) {
            return null;
        }

        $answers = $attempt->answers()
            ->whereIn('assessment_question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('assessment_question_id');

        $categories = [
            self::DOMAIN_VERBAL => [
                'key' => self::DOMAIN_VERBAL,
                'name' => 'Kemampuan Verbal',
                'dimension' => 'Verbal',
                'icon' => 'document-text',
                'description' => 'Mengukur pemahaman bahasa, perbendaharaan kata, analogi konsep, dan ketepatan komunikasi tertulis.',
                'total' => 0,
                'correct' => 0,
                'points_earned' => 0,
                'points_possible' => 0,
            ],
            self::DOMAIN_NUMERIC => [
                'key' => self::DOMAIN_NUMERIC,
                'name' => 'Kemampuan Numerik',
                'dimension' => 'Numerik',
                'icon' => 'calculator',
                'description' => 'Mengukur kelincahan berhitung, penalaran kuantitatif, analisis deret angka, dan akurasi data finansial/matematis.',
                'total' => 0,
                'correct' => 0,
                'points_earned' => 0,
                'points_possible' => 0,
            ],
            self::DOMAIN_LOGIC => [
                'key' => self::DOMAIN_LOGIC,
                'name' => 'Logika & Penalaran',
                'dimension' => 'Logika',
                'icon' => 'sparkles',
                'description' => 'Mengukur daya analisis silogistis, penarikan kesimpulan deduktif/induktif, dan pemecahan masalah kritis.',
                'total' => 0,
                'correct' => 0,
                'points_earned' => 0,
                'points_possible' => 0,
            ],
            self::DOMAIN_SPATIAL => [
                'key' => self::DOMAIN_SPATIAL,
                'name' => 'Spasial & Figural',
                'dimension' => 'Spasial',
                'icon' => 'cube',
                'description' => 'Mengukur visualisasi bentuk, identifikasi pola abstrak, rotasi mental objek, dan penalaran non-verbal.',
                'total' => 0,
                'correct' => 0,
                'points_earned' => 0,
                'points_possible' => 0,
            ],
        ];

        $answeredCount = 0;
        $totalCorrect = 0;
        $totalQuestions = $questions->count();
        $totalPointsEarned = 0;
        $totalPointsPossible = 0;

        foreach ($questions as $question) {
            $domain = $this->classifyDomain($question);
            $answer = $answers->get($question->id);

            $isAnswered = $answer !== null && $answer->selected_option !== null;
            $isCorrect = $isAnswered && $answer->selected_option === $question->correct_option;

            if ($isAnswered) {
                $answeredCount++;
            }

            if ($isCorrect) {
                $totalCorrect++;
                $categories[$domain]['correct']++;
            }

            $categories[$domain]['total']++;
            $points = max(1, (int) $question->points);
            $categories[$domain]['points_possible'] += $points;
            $totalPointsPossible += $points;

            if ($isCorrect) {
                $categories[$domain]['points_earned'] += $points;
                $totalPointsEarned += $points;
            }
        }

        foreach ($categories as $key => $cat) {
            $total = $cat['total'];
            $correct = $cat['correct'];
            $pct = $total > 0 ? round(($correct / $total) * 100, 1) : 0.0;

            $status = $this->evaluateDomainStatus($pct);

            $categories[$key]['accuracy_percent'] = $pct;
            $categories[$key]['status_label'] = $status['label'];
            $categories[$key]['status_tone'] = $status['tone'];
            $categories[$key]['insight'] = $status['insight'];
        }

        $activeCategories = array_filter($categories, fn ($cat) => $cat['total'] > 0);

        if (empty($activeCategories)) {
            return null;
        }

        $estimatedIq = $this->calculateIq($totalCorrect, $totalQuestions);
        $classification = $this->classifyIq($estimatedIq);
        $recommendations = $this->generateRecommendations($estimatedIq, $activeCategories);

        return [
            'total_questions' => $totalQuestions,
            'answered_count' => $answeredCount,
            'correct_count' => $totalCorrect,
            'accuracy_percent' => $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0.0,
            'estimated_iq' => $estimatedIq,
            'iq_classification' => $classification['label'],
            'iq_tone' => $classification['tone'],
            'iq_description' => $classification['description'],
            'iq_percentile' => $classification['percentile'],
            'domains' => $activeCategories,
            'cognitive_profile' => $recommendations['cognitive_profile'],
            'recommended_roles' => $recommendations['recommended_roles'],
            'hr_summary' => $recommendations['hr_summary'],
        ];
    }

    protected function classifyDomain(AssessmentQuestion $question): string
    {
        $dim = strtolower((string) $question->dimension);
        $text = strtolower((string) $question->question);

        if (str_contains($dim, 'verbal') || str_contains($dim, 'bahasa') || str_contains($dim, 'sinonim') || str_contains($dim, 'analogi') || str_contains($text, 'sinonim') || str_contains($text, 'antonim') || str_contains($text, 'analogi')) {
            return self::DOMAIN_VERBAL;
        }

        if (str_contains($dim, 'numerik') || str_contains($dim, 'angka') || str_contains($dim, 'hitung') || str_contains($dim, 'kuantitatif') || str_contains($dim, 'matematika') || str_contains($text, 'deret') || str_contains($text, 'aritmatika')) {
            return self::DOMAIN_NUMERIC;
        }

        if (str_contains($dim, 'spasial') || str_contains($dim, 'figural') || str_contains($dim, 'gambar') || str_contains($dim, 'visual') || str_contains($dim, 'pola ruang') || str_contains($text, 'bangun') || str_contains($text, 'rotasi') || str_contains($text, 'cermin')) {
            return self::DOMAIN_SPATIAL;
        }

        if (str_contains($dim, 'logika') || str_contains($dim, 'penalaran') || str_contains($dim, 'silogisme') || str_contains($dim, 'analitis') || str_contains($text, 'silogisme') || str_contains($text, 'kesimpulan')) {
            return self::DOMAIN_LOGIC;
        }

        return match ($question->position % 4) {
            0 => self::DOMAIN_VERBAL,
            1 => self::DOMAIN_NUMERIC,
            2 => self::DOMAIN_LOGIC,
            default => self::DOMAIN_SPATIAL,
        };
    }

    public function calculateIq(int $correct, int $total): int
    {
        if ($total <= 0) {
            return 100;
        }

        if ($total === 30 && isset(self::NORM_30_SCALE[$correct])) {
            return self::NORM_30_SCALE[$correct];
        }

        $ratio = max(0.0, min(1.0, $correct / $total));

        

        
        if ($ratio >= 0.533) {
            $iq = 100 + round((($ratio - 0.533) / (1.0 - 0.533)) * 42);
        } else {
            $iq = 100 - round(((0.533 - $ratio) / 0.533) * 42);
        }

        return (int) max(55, min(145, $iq));
    }

    protected function classifyIq(int $iq): array
    {
        if ($iq >= 130) {
            return [
                'label' => 'Sangat Unggul (Very Superior)',
                'tone' => 'success',
                'percentile' => 'Top 2.2% Populasi (Persentil 98+)',
                'description' => 'Kapasitas intelektual dan daya abstraksi sangat luar biasa. Mampu memecahkan problem kompleks lintas disiplin dan merancang strategi inovatif dengan cepat.',
            ];
        }

        if ($iq >= 120) {
            return [
                'label' => 'Unggul (Superior)',
                'tone' => 'success',
                'percentile' => 'Top 9% Populasi (Persentil 91-97)',
                'description' => 'Kapasitas penalaran kognitif tinggi di atas rata-rata. Cepat menguasai sistem rumit, tajam dalam menganalisis data, dan mandiri dalam problem-solving.',
            ];
        }

        if ($iq >= 110) {
            return [
                'label' => 'Di Atas Rata-Rata (High Average)',
                'tone' => 'primary',
                'percentile' => 'Persentil 75-90',
                'description' => 'Daya tangkap kognitif baik dan tanggap. Mampu memproses instruksi kerja baru dengan cepat serta memiliki ketepatan berpikir analitis yang solid.',
            ];
        }

        if ($iq >= 90) {
            return [
                'label' => 'Rata-Rata (Average)',
                'tone' => 'neutral',
                'percentile' => 'Persentil 25-74 (Mayoritas Populasi)',
                'description' => 'Kapasitas intelektual umum yang stabil dan seimbang. Sangat baik dalam menjalankan prosedur operasional standar (SOP) dan tugas yang berorientasi tim.',
            ];
        }

        if ($iq >= 80) {
            return [
                'label' => 'Di Bawah Rata-Rata (Low Average)',
                'tone' => 'warning',
                'percentile' => 'Persentil 9-24',
                'description' => 'Memerlukan waktu lebih lama untuk memahami instruksi yang kompleks. Lebih cocok untuk penugasan kerja yang bersifat rutin, jelas, dan terstruktur.',
            ];
        }

        return [
            'label' => 'Batas Bawah (Borderline)',
            'tone' => 'danger',
            'percentile' => 'Di Bawah Persentil 9',
            'description' => 'Membutuhkan supervisi dan panduan langkah-demi-langkah yang intensif. Disarankan untuk tugas praktis langsung tanpa beban analisa konseptual.',
        ];
    }

    protected function evaluateDomainStatus(float $pct): array
    {
        if ($pct >= 80) {
            return [
                'label' => 'Sangat Kuat',
                'tone' => 'success',
                'insight' => 'Penguasaan tinggi, sangat akurat dan tajam dalam memproses informasi domain ini.',
            ];
        }

        if ($pct >= 65) {
            return [
                'label' => 'Kuat',
                'tone' => 'primary',
                'insight' => 'Kapasitas di atas rata-rata, mampu menangani tantangan domain ini secara mandiri.',
            ];
        }

        if ($pct >= 50) {
            return [
                'label' => 'Cukup',
                'tone' => 'warning',
                'insight' => 'Kapasitas memadai untuk tugas standar, namun membutuhkan ketelitian tambahan untuk kasus rumit.',
            ];
        }

        return [
            'label' => 'Perlu Ditingkatkan',
            'tone' => 'danger',
            'insight' => 'Menunjukkan kesulitan dalam menyelesaikan persoalan pada domain ini, butuh bantuan/arahan berkala.',
        ];
    }

    protected function generateRecommendations(int $iq, array $domains): array
    {
        
        uasort($domains, fn ($a, $b) => $b['accuracy_percent'] <=> $a['accuracy_percent']);
        $domainKeys = array_keys($domains);
        $topDomain = $domainKeys[0] ?? self::DOMAIN_LOGIC;
        $lowestDomain = end($domainKeys) ?: self::DOMAIN_SPATIAL;

        $learningSpeed = match (true) {
            $iq >= 120 => 'Sangat Cepat & Mandiri',
            $iq >= 110 => 'Cepat & Responsif',
            $iq >= 90 => 'Normal / Sesuai Kurva Belajar Standar',
            default => 'Butuh Waktu & Panduan Intensif',
        };

        $problemSolving = match (true) {
            $iq >= 125 => 'Strategis, Konseptual & Komprehensif',
            $iq >= 110 => 'Analitis & Sistematis',
            $iq >= 90 => 'Praktis & Berorientasi Solusi SOP',
            default => 'Rutin & Berpola Tetap',
        };

        $roles = match ($topDomain) {
            self::DOMAIN_NUMERIC => ['Data Analyst', 'Financial Planner / Accounting', 'Engineer', 'Auditor', 'Inventory / Supply Planner'],
            self::DOMAIN_VERBAL => ['Corporate Communications', 'Public Relations', 'Legal Officer', 'Senior Copywriter', 'HR Recruiter / Trainer'],
            self::DOMAIN_LOGIC => ['Business Analyst', 'Software Engineer', 'Project Manager', 'Risk & Compliance Officer', 'Management Consultant'],
            self::DOMAIN_SPATIAL => ['UI/UX Designer', 'Architect / CAD Drafter', 'Product Designer', 'Creative Director', 'Operations Planner'],
            default => ['General Operations', 'Administrative Specialist', 'Customer Care'],
        };

        $hrSummary = "Kandidat memiliki estimasi IQ {$iq} yang tergolong {$this->classifyIq($iq)['label']}. Keunggulan kognitif paling menonjol berada pada {$domains[$topDomain]['name']} (akurasi {$domains[$topDomain]['accuracy_percent']}%).";

        if ($domains[$lowestDomain]['accuracy_percent'] < 50) {
            $hrSummary .= " Area yang memerlukan supervisi lebih lanjut adalah {$domains[$lowestDomain]['name']} (akurasi {$domains[$lowestDomain]['accuracy_percent']}%).";
        }

        return [
            'cognitive_profile' => [
                'learning_speed' => $learningSpeed,
                'problem_solving' => $problemSolving,
                'strongest_domain' => $domains[$topDomain]['name'],
                'area_for_growth' => $domains[$lowestDomain]['name'],
            ],
            'recommended_roles' => array_slice($roles, 0, 4),
            'hr_summary' => $hrSummary,
        ];
    }
}
