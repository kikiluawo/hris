<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;

class DiscScorer
{
    public const TRAITS = ['D', 'I', 'S', 'C'];

    public const MIDLINE = 14;

    protected const GRAPH_1_CONVERSION = [
        'D' => [0 => 2, 1 => 4, 2 => 6, 3 => 8, 4 => 10, 5 => 12, 6 => 14, 7 => 16, 8 => 18, 9 => 20, 10 => 22, 11 => 24, 12 => 25, 13 => 26, 14 => 27, 15 => 28, 16 => 28, 17 => 28, 18 => 28, 19 => 28, 20 => 28, 21 => 28, 22 => 28, 23 => 28, 24 => 28],
        'I' => [0 => 3, 1 => 5, 2 => 7, 3 => 9, 4 => 11, 5 => 13, 6 => 15, 7 => 17, 8 => 19, 9 => 21, 10 => 23, 11 => 25, 12 => 26, 13 => 27, 14 => 28, 15 => 28, 16 => 28, 17 => 28, 18 => 28, 19 => 28, 20 => 28, 21 => 28, 22 => 28, 23 => 28, 24 => 28],
        'S' => [0 => 2, 1 => 4, 2 => 6, 3 => 8, 4 => 10, 5 => 12, 6 => 14, 7 => 16, 8 => 18, 9 => 20, 10 => 22, 11 => 24, 12 => 25, 13 => 26, 14 => 27, 15 => 28, 16 => 28, 17 => 28, 18 => 28, 19 => 28, 20 => 28, 21 => 28, 22 => 28, 23 => 28, 24 => 28],
        'C' => [0 => 3, 1 => 5, 2 => 7, 3 => 9, 4 => 11, 5 => 13, 6 => 15, 7 => 17, 8 => 19, 9 => 21, 10 => 23, 11 => 24, 12 => 25, 13 => 26, 14 => 27, 15 => 28, 16 => 28, 17 => 28, 18 => 28, 19 => 28, 20 => 28, 21 => 28, 22 => 28, 23 => 28, 24 => 28],
    ];

    protected const GRAPH_2_CONVERSION = [
        'D' => [0 => 27, 1 => 25, 2 => 23, 3 => 21, 4 => 19, 5 => 17, 6 => 15, 7 => 13, 8 => 11, 9 => 9, 10 => 7, 11 => 5, 12 => 3, 13 => 2, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1, 19 => 1, 20 => 1, 21 => 1, 22 => 1, 23 => 1, 24 => 1],
        'I' => [0 => 28, 1 => 26, 2 => 24, 3 => 22, 4 => 20, 5 => 18, 6 => 16, 7 => 14, 8 => 12, 9 => 10, 10 => 8, 11 => 6, 12 => 4, 13 => 2, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1, 19 => 1, 20 => 1, 21 => 1, 22 => 1, 23 => 1, 24 => 1],
        'S' => [0 => 27, 1 => 25, 2 => 23, 3 => 21, 4 => 19, 5 => 17, 6 => 15, 7 => 13, 8 => 11, 9 => 9, 10 => 7, 11 => 5, 12 => 3, 13 => 2, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1, 19 => 1, 20 => 1, 21 => 1, 22 => 1, 23 => 1, 24 => 1],
        'C' => [0 => 28, 1 => 26, 2 => 24, 3 => 22, 4 => 20, 5 => 18, 6 => 16, 7 => 14, 8 => 12, 9 => 10, 10 => 8, 11 => 6, 12 => 4, 13 => 2, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1, 19 => 1, 20 => 1, 21 => 1, 22 => 1, 23 => 1, 24 => 1],
    ];

    public function calculate(AssessmentAttempt $attempt): ?array
    {
        $questions = $attempt->invitation?->assessment?->questions()
            ->where('type', AssessmentQuestion::TYPE_DISC)
            ->get();

        if ($questions === null || $questions->isEmpty()) {
            return null;
        }

        $answers = $attempt->answers()
            ->whereIn('assessment_question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('assessment_question_id');

        $tallyMost = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
        $tallyLeast = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
        $answeredCount = 0;

        foreach ($questions as $question) {
            $answer = $answers->get($question->id);
            if ($answer === null) {
                continue;
            }

            $options = $question->options ?? [];
            $mostIndex = $answer->most_option;
            $leastIndex = $answer->least_option;

            if (($mostIndex === null || $leastIndex === null) && filled($answer->answer)) {
                $decoded = json_decode((string) $answer->answer, true);
                if (is_array($decoded)) {
                    $mostIndex ??= $decoded['most'] ?? null;
                    $leastIndex ??= $decoded['least'] ?? null;
                }
            }

            if ($mostIndex === null && $leastIndex === null) {
                continue;
            }

            $answeredCount++;

            if ($mostIndex !== null && isset($options[$mostIndex])) {
                $trait = $this->extractTrait($options[$mostIndex], $mostIndex, $question->dimension);
                if ($trait && in_array($trait, self::TRAITS, true)) {
                    $tallyMost[$trait]++;
                }
            }

            if ($leastIndex !== null && isset($options[$leastIndex])) {
                $trait = $this->extractTrait($options[$leastIndex], $leastIndex, $question->dimension);
                if ($trait && in_array($trait, self::TRAITS, true)) {
                    $tallyLeast[$trait]++;
                }
            }
        }

        if ($answeredCount === 0) {
            return null;
        }

        $tallyChange = [];
        foreach (self::TRAITS as $trait) {
            $tallyChange[$trait] = $tallyMost[$trait] - $tallyLeast[$trait];
        }

        $graph1 = [];
        $graph2 = [];
        $graph3 = [];

        foreach (self::TRAITS as $trait) {
            $graph1[$trait] = $this->scoreToSegment(1, $trait, $tallyMost[$trait]);
            $graph2[$trait] = $this->scoreToSegment(2, $trait, $tallyLeast[$trait]);
            $graph3[$trait] = $this->changeToSegment($trait, $tallyChange[$trait]);
        }

        $profile = $this->determineProfile($graph3, $graph1, $graph2);
        $adaptationAnalysis = $this->analyzeAdaptation($graph1, $graph2);

        return [
            'total_questions' => $questions->count(),
            'answered_questions' => $answeredCount,
            'is_complete' => $answeredCount >= $questions->count(),
            'tally' => [
                'most' => $tallyMost,
                'least' => $tallyLeast,
                'change' => $tallyChange,
            ],
            'graphs' => [
                'graph_1' => [
                    'name' => 'Grafik 1: Mask Profile (Perilaku Adaptasi / Lingkungan Kerja)',
                    'short_name' => 'Adaptasi (Mask)',
                    'scores' => $graph1,
                    'description' => 'Menggambarkan bagaimana kandidat menyesuaikan perilakunya untuk memenuhi tuntutan pekerjaan atau persepsi lingkungan kerja.',
                ],
                'graph_2' => [
                    'name' => 'Grafik 2: Core Profile (Karakter Asli / Di Bawah Tekanan)',
                    'short_name' => 'Karakter Asli (Core)',
                    'scores' => $graph2,
                    'description' => 'Menggambarkan karakter alami kandidat saat dalam kondisi rileks tanpa kontrol sadar atau saat berada di bawah tekanan tinggi.',
                ],
                'graph_3' => [
                    'name' => 'Grafik 3: Mirror Profile (Citra Diri Terintegrasi)',
                    'short_name' => 'Citra Diri (Mirror)',
                    'scores' => $graph3,
                    'description' => 'Integrasi antara karakter asli dan adaptasi, menjadi acuan utama pembacaan pola kepribadian DISC.',
                ],
            ],
            'midline' => self::MIDLINE,
            'profile' => $profile,
            'adaptation_analysis' => $adaptationAnalysis,
        ];
    }

    protected function extractTrait(mixed $option, int $fallbackIndex, ?string $dimension = null): string
    {
        if (is_array($option) && isset($option['trait'])) {
            return strtoupper(trim((string) $option['trait']));
        }

        if (filled($dimension)) {
            $traits = array_map('trim', explode(',', $dimension));
            if (isset($traits[$fallbackIndex]) && in_array(strtoupper($traits[$fallbackIndex]), self::TRAITS, true)) {
                return strtoupper($traits[$fallbackIndex]);
            }
        }

        return self::TRAITS[$fallbackIndex % 4] ?? 'D';
    }

    protected function scoreToSegment(int $graph, string $trait, int $score): int
    {
        $score = max(0, min(24, $score));

        if ($graph === 1) {
            return self::GRAPH_1_CONVERSION[$trait][$score] ?? (int) round(14 + ($score - 6) * 1.5);
        }

        return self::GRAPH_2_CONVERSION[$trait][$score] ?? (int) round(14 - ($score - 6) * 1.5);
    }

    protected function changeToSegment(string $trait, int $diff): int
    {

        $segment = 14 + (int) round($diff * 0.7);

        return max(1, min(28, $segment));
    }

    protected function determineProfile(array $graph3, array $graph1, array $graph2): array
    {
        
        arsort($graph3);
        $sortedTraits = array_keys($graph3);

        $primary = $sortedTraits[0];
        $secondary = $sortedTraits[1];

        $primaryScore = $graph3[$primary];
        $secondaryScore = $graph3[$secondary];

        $code = ($secondaryScore >= self::MIDLINE && ($primaryScore - $secondaryScore) <= 6)
            ? $primary . $secondary
            : $primary;

        $patterns = $this->profilePatterns();
        $detail = $patterns[$code] ?? $patterns[$primary] ?? $patterns['D'];

        return [
            'code' => $code,
            'primary_trait' => $primary,
            'secondary_trait' => $secondaryScore >= self::MIDLINE ? $secondary : null,
            'title' => $detail['title'],
            'subtitle' => $detail['subtitle'],
            'overview' => $detail['overview'],
            'strengths' => $detail['strengths'],
            'weaknesses' => $detail['weaknesses'],
            'work_style' => $detail['work_style'],
            'communication_tips' => $detail['communication_tips'],
            'recommended_roles' => $detail['recommended_roles'],
        ];
    }

    protected function analyzeAdaptation(array $graph1, array $graph2): array
    {
        $shifts = [];
        $totalShift = 0;

        $traitLabels = [
            'D' => 'Dominance (Ketegasan / Kendali)',
            'I' => 'Influence (Komunikasi / Persuasi)',
            'S' => 'Steadiness (Kesabaran / Harmoni)',
            'C' => 'Compliance (Ketelitian / Aturan)',
        ];

        foreach (self::TRAITS as $trait) {
            $mask = $graph1[$trait];
            $core = $graph2[$trait];
            $diff = $mask - $core;
            $totalShift += abs($diff);

            if ($diff >= 4) {
                $type = 'increase';
                $interpretation = match ($trait) {
                    'D' => 'Kandidat berupaya tampil jauh lebih tegas, mandiri, dan berani mengambil keputusan di tempat kerja dibanding sifat alaminya.',
                    'I' => 'Kandidat berusaha ekstra ramah, aktif bergaul, dan komunikatif demi tuntutan peran sosial di kantor.',
                    'S' => 'Kandidat berusaha menahan diri, lebih sabar, dan mengalah demi menjaga kestabilan dan kedamaian kerja.',
                    'C' => 'Kandidat berusaha sangat cermat, disiplin, dan patuh pada SOP kerja melebihi kebiasaan aslinya.',
                };
            } elseif ($diff <= -4) {
                $type = 'decrease';
                $interpretation = match ($trait) {
                    'D' => 'Kandidat menahan agresivitas atau kontrol alaminya di tempat kerja agar lebih akomodatif terhadap atasan/tim.',
                    'I' => 'Kandidat menahan sisi ekspresif alaminya dan memilih lebih pendiam/serius saat menghadapi tugas pekerjaan.',
                    'S' => 'Kandidat merasa dituntut untuk bergerak lebih cepat, fleksibel, dan responsif dari ritme kerja alaminya yang tenang.',
                    'C' => 'Kandidat merasa harus melonggarkan keteraturan demi mengejar kecepatan atau fleksibilitas situasi kerja.',
                };
            } else {
                $type = 'stable';
                $interpretation = "Perilaku pada aspek {$traitLabels[$trait]} berjalan seimbang dan selaras antara karakter asli dan adaptasi di tempat kerja.";
            }

            $shifts[$trait] = [
                'trait' => $trait,
                'label' => $traitLabels[$trait],
                'mask' => $mask,
                'core' => $core,
                'diff' => $diff,
                'type' => $type,
                'interpretation' => $interpretation,
            ];
        }

        $effortLevel = match (true) {
            $totalShift >= 22 => 'Tinggi (Kandidat melakukan perubahan perilaku yang sangat signifikan, berpotensi cepat lelah bila tanpa dukungan tim).',
            $totalShift >= 12 => 'Moderat (Kandidat menyesuaikan diri secara sehat dan proporsional dengan tuntutan pekerjaannya).',
            default => 'Rendah (Kandidat merasa sangat nyaman menjadi dirinya sendiri tanpa perlu banyak topeng di lingkungan kerja).',
        };

        return [
            'total_shift' => $totalShift,
            'effort_level' => $effortLevel,
            'shifts' => $shifts,
        ];
    }

    protected function profilePatterns(): array
    {
        return [
            'D' => [
                'title' => 'Dominance (The Commander / Driver)',
                'subtitle' => 'Pemimpin Tegas & Berorientasi Hasil',
                'overview' => 'Kandidat tipe High D adalah individu yang percaya diri, kompetitif, berani mengambil risiko, dan fokus pada pencapaian target. Mereka tidak suka bertele-tele dan menyukai tantangan besar.',
                'strengths' => [
                    'Cepat mengambil keputusan dalam situasi genting',
                    'Fokus tinggi pada pencapaian target dan efisiensi kerja',
                    'Berani mengatasi hambatan dan menantang status quo',
                    'Mandiri dan tidak bergantung pada arahan orang lain',
                ],
                'weaknesses' => [
                    'Cenderung tidak sabar dengan orang yang bekerja lambat',
                    'Kurang memperhatikan detail administratif dan perasaan rekan kerja',
                    'Bisa terkesan otoriter bila berada di bawah tekanan',
                ],
                'work_style' => 'Menyukai otonomi luas, kebebasan menentukan cara kerja, dan tantangan yang jelas indikator keberhasilannya.',
                'communication_tips' => 'Bicaralah secara lugas, langsung ke pokok masalah (to the point), dan fokus pada hasil/solusi, bukan pada hambatan.',
                'recommended_roles' => ['Project Manager', 'Branch Manager', 'Sales Lead', 'Business Development', 'Operations Head'],
            ],
            'I' => [
                'title' => 'Influence (The Influencer / Promoter)',
                'subtitle' => 'Komunikator Persuasif & Pembangun Relasi',
                'overview' => 'Kandidat tipe High I adalah pribadi yang ramah, antusias, terbuka, dan memiliki kemampuan persuasif yang alami. Mereka adalah pembawa energi positif di dalam tim dan pandai mencairkan suasana.',
                'strengths' => [
                    'Kemampuan komunikasi verbal dan negosiasi yang luar biasa',
                    'Cepat membangun hubungan akrab dengan orang baru',
                    'Mampu memotivasi dan membangkitkan semangat rekan kerja',
                    'Kreatif dan kaya akan ide-ide segar',
                ],
                'weaknesses' => [
                    'Kurang teliti dalam dokumentasi dan hal-hal administratif',
                    'Cenderung menghindari pembicaraan konfrontatif atau berita buruk',
                    'Mudah kehilangan fokus pada rutinitas jangka panjang',
                ],
                'work_style' => 'Menyukai lingkungan kerja yang dinamis, kolaboratif, tidak kaku, dan memberikan ruang untuk berekspresi.',
                'communication_tips' => 'Bangun percakapan yang hangat, beri pengakuan/pujian atas ide-idenya, dan buat suasana diskusi menyenangkan.',
                'recommended_roles' => ['Public Relations', 'Marketing Communication', 'Recruiter / HR People Partner', 'Customer Relations', 'Account Executive'],
            ],
            'S' => [
                'title' => 'Steadiness (The Supporter / Relater)',
                'subtitle' => 'Pribadi Loyal, Sabar, & Pendengar yang Baik',
                'overview' => 'Kandidat tipe High S adalah sosok yang tenang, dapat diandalkan, setia, dan mengutamakan keharmonisan tim. Mereka bekerja konsisten dan menyukai keteraturan.',
                'strengths' => [
                    'Tingkat loyalitas dan dedikasi yang tinggi terhadap organisasi',
                    'Sabar, kooperatif, dan sangat baik dalam kerja sama tim',
                    'Mampu bekerja stabil di tengah rutinitas tanpa mudah bosan',
                    'Pendengar yang baik dan penjaga kedamaian lingkungan kerja',
                ],
                'weaknesses' => [
                    'Cenderung enggan dan lambat menerima perubahan mendadak',
                    'Sulit berkata "tidak" sehingga rawan terbebani tugas orang lain',
                    'Cenderung pasif dalam situasi konflik terbuka',
                ],
                'work_style' => 'Sangat menyukai lingkungan yang stabil, aman, harmonis, dengan instruksi dan ekspektasi tugas yang jelas.',
                'communication_tips' => 'Gunakan pendekatan personal yang santun, dengarkan pandangannya, dan beri waktu untuk mencerna perubahan.',
                'recommended_roles' => ['Customer Support', 'HR Generalist / Administrator', 'Operations Support', 'Administration Specialist', 'Customer Care'],
            ],
            'C' => [
                'title' => 'Compliance (The Analyst / Thinker)',
                'subtitle' => 'Ahli Presisi, Data, & Standar Mutu',
                'overview' => 'Kandidat tipe High C adalah individu yang sistematis, analitis, teliti, dan mengutamakan keakuratan. Mereka berpegang teguh pada aturan, SOP, dan logika objektif.',
                'strengths' => [
                    'Ketelitian tingkat tinggi dan minim kesalahan operasional',
                    'Kemampuan analisis data dan pemecahan masalah secara terstruktur',
                    'Disiplin tinggi terhadap kepatuhan SOP dan regulasi',
                    'Standar kualitas hasil kerja yang sangat rapi dan konsisten',
                ],
                'weaknesses' => [
                    'Bisa terjebak pada analisis berlebih (analysis paralysis)',
                    'Sangat kritis terhadap diri sendiri maupun rekan kerja',
                    'Cenderung kaku saat dituntut berimprovisasi di luar prosedur',
                ],
                'work_style' => 'Menyukai pekerjaan terstruktur dengan data valid, parameter jelas, dan lingkungan kerja yang tenang tanpa gangguan.',
                'communication_tips' => 'Sampaikan informasi secara logis, sertakan data/fakta pendukung, dan hindari instruksi yang ambigu atau berubah-ubah.',
                'recommended_roles' => ['Financial Auditor', 'Quality Assurance', 'Data Analyst', 'Legal Officer', 'Finance & Accounting Specialist'],
            ],
            'DI' => [
                'title' => 'The Persuader (D/I)',
                'subtitle' => 'Penggerak Bersemangat & Pemimpin Karismatik',
                'overview' => 'Kombinasi antara ketegasan D dan kehangatan I membuat kandidat ini sangat efektif dalam memimpin tim serta meyakinkan orang lain menuju sasaran besar.',
                'strengths' => ['Kharismatik dan menginspirasi', 'Cepat mengeksekusi ide', 'Negosiator ulung', 'Berani mengambil risiko'],
                'weaknesses' => ['Bisa bertindak impulsif', 'Kurang sabar dengan hal teknis rinci', 'Mengharapkan hasil instan'],
                'work_style' => 'Proaktif, berorientasi target dengan interaksi sosial yang tinggi.',
                'communication_tips' => 'Ajak bicara mengenai visi, strategi, dan peluang pencapaian target.',
                'recommended_roles' => ['Sales Director', 'Business Development Head', 'Branch Manager', 'General Manager'],
            ],
            'DC' => [
                'title' => 'The Achiever / Strategist (D/C)',
                'subtitle' => 'Eksekutor Disiplin & Berorientasi Mutu',
                'overview' => 'Kombinasi dorongan kuat D dan presisi tinggi C membuat kandidat ini selalu menghasilkan pekerjaan berkualitas tinggi dengan kecepatan prima.',
                'strengths' => ['Disiplin tinggi', 'Berpikir strategis dan sistematis', 'Kritis dan berorientasi hasil', 'Fokus pada standar tinggi'],
                'weaknesses' => ['Perfeksionis dan menuntut banyak dari tim', 'Terkesan dingin dan kaku', 'Kurang fleksibel'],
                'work_style' => 'Menyukai struktur yang jelas dengan target yang menantang akurasi serta efisiensi.',
                'communication_tips' => 'Gunakan logika tajam, fakta konkret, dan berikan kebebasan untuk menyusun rencana teknis.',
                'recommended_roles' => ['Operations Director', 'Supply Chain Manager', 'Engineering Manager', 'Management Consultant'],
            ],
            'DS' => [
                'title' => 'The Pioneer / Inquirer (D/S)',
                'subtitle' => 'Pekerja Gigih & Pantang Mundur',
                'overview' => 'Menggabungkan dorongan hasil D dan daya tahan S. Kandidat ini gigih, ulet, dan mampu bertahan menyelesaikan pekerjaan rumit hingga tuntas.',
                'strengths' => ['Konsisten dan pantang menyerah', 'Mampu bekerja stabil di bawah tekanan', 'Tegas namun dapat diandalkan'],
                'weaknesses' => ['Bisa keras kepala bila keputusannya ditentang', 'Kurang komunikatif dalam mengekspresikan kendala'],
                'work_style' => 'Fokus, tekun, dan menyukai tanggung jawab yang jelas dengan ritme kerja terukur.',
                'communication_tips' => 'Hargai komitmen kerjanya, komunikasikan target dengan jelas, dan berikan dukungan nyata.',
                'recommended_roles' => ['Plant Manager', 'Production Supervisor', 'Senior Project Coordinator'],
            ],
            'IS' => [
                'title' => 'The Counselor / Coach (I/S)',
                'subtitle' => 'Sahabat Tim yang Empatis & Harmonis',
                'overview' => 'Sangat peka terhadap dinamika tim. Menghubungkan orang lain dengan empati, kesabaran, dan kemampuan mendengarkan yang hangat.',
                'strengths' => ['Pembangun iklim tim yang sehat', 'Empatis dan suportif', 'Peredam konflik yang handal'],
                'weaknesses' => ['Sulit mengambil keputusan tegas yang tidak populer', 'Menghindari konfrontasi yang diperlukan'],
                'work_style' => 'Penuh kerja sama, saling dukung, dan menghargai nilai-nilai kekeluargaan di tempat kerja.',
                'communication_tips' => 'Bicara dengan nada hangat, tanyakan pandangan pribadinya, dan dukung kenyamanan emosionalnya.',
                'recommended_roles' => ['HR Employee Relations', 'Corporate Trainer', 'Customer Success Manager', 'Community Lead'],
            ],
            'IC' => [
                'title' => 'The Assessor / Diplomat (I/C)',
                'subtitle' => 'Komunikator Berbobot & Terukur',
                'overview' => 'Mampu memadukan data analitis C dengan gaya presentasi menarik I. Sangat baik dalam menjelaskan hal teknis rumit ke klien atau manajemen.',
                'strengths' => ['Presentasi data yang memikat', 'Diplomatis dan objektif', 'Peka terhadap detail sekaligus ramah'],
                'weaknesses' => ['Bisa ragu-ragu karena menimbang terlalu banyak sudut pandang', 'Sensitif terhadap kritik tajam'],
                'work_style' => 'Menyukai proyek riset atau representasi yang membutuhkan akurasi data serta komunikasi publik.',
                'communication_tips' => 'Sediakan fakta pendukung sambil mempertahankan komunikasi yang bersahabat.',
                'recommended_roles' => ['Product Marketing Manager', 'Market Researcher', 'Technical Sales', 'Compliance Trainer'],
            ],
            'SC' => [
                'title' => 'The Specialist / Peacekeeper (S/C)',
                'subtitle' => 'Pilar Konsistensi, Kerapian, & Integritas',
                'overview' => 'Kandidat ini adalah tulang punggung operasional. Mereka cermat, metodis, dapat dipercaya, dan setia menjalankan sistem dengan sempurna.',
                'strengths' => ['Sangat cermat dan teliti', 'Tertib administrasi dan SOP', 'Konsisten, stabil, dan berintegritas tinggi'],
                'weaknesses' => ['Enggan mengambil inisiatif di luar prosedur', 'Lambat beradaptasi pada perubahan mendadak'],
                'work_style' => 'Terencana, tenang, dengan aturan kerja dan alur birokrasi yang jelas.',
                'communication_tips' => 'Jelaskan prosedur langkah demi langkah dan berikan kepastian keamanan kerja.',
                'recommended_roles' => ['Senior Accountant', 'Compliance Specialist', 'Database Administrator', 'Administrative Head'],
            ],
        ];
    }
}
