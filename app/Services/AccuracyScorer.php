<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;

class AccuracyScorer
{
    
    public function calculate(AssessmentAttempt $attempt): ?array
    {
        $assessment = $attempt->invitation?->assessment;
        if ($assessment === null) {
            return null;
        }

        if (! $attempt->hasAccuracyQuestions() && ! $this->isAccuracyAssessment($assessment)) {
            return null;
        }

        $query = $assessment->questions()
            ->where('type', AssessmentQuestion::TYPE_MULTIPLE_CHOICE)
            ->whereNotNull('correct_option');

        if ($attempt->hasAptitudeQuestions() || $assessment->questions()->where('dimension', 'like', '%ketelitian%')->exists()) {
            $query->where(function ($q) {
                $q->where('dimension', 'like', '%ketelitian%')
                    ->orWhere('dimension', 'like', '%akurasi%')
                    ->orWhere('dimension', 'like', '%clerical%')
                    ->orWhere('dimension', 'like', '%checking%')
                    ->orWhere('dimension', 'like', '%pencocokan%');
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

        $totalItems = $questions->count();
        $attemptedCount = 0;
        $correctCount = 0;
        $errorCount = 0;

        $segmentSize = max(1, (int) ceil($totalItems / 3));
        $segments = [
            'phase_1' => ['name' => 'Fase Awal (Soal 1–' . $segmentSize . ')', 'total' => 0, 'attempted' => 0, 'correct' => 0],
            'phase_2' => ['name' => 'Fase Tengah (Soal ' . ($segmentSize + 1) . '–' . min($totalItems, $segmentSize * 2) . ')', 'total' => 0, 'attempted' => 0, 'correct' => 0],
            'phase_3' => ['name' => 'Fase Akhir (Soal ' . (min($totalItems, $segmentSize * 2) + 1) . '–' . $totalItems . ')', 'total' => 0, 'attempted' => 0, 'correct' => 0],
        ];

        foreach ($questions as $index => $question) {
            $answer = $answers->get($question->id);
            $phaseKey = match (true) {
                $index < $segmentSize => 'phase_1',
                $index < $segmentSize * 2 => 'phase_2',
                default => 'phase_3',
            };

            $segments[$phaseKey]['total']++;

            $isAnswered = $answer !== null && $answer->selected_option !== null;
            if ($isAnswered) {
                $attemptedCount++;
                $segments[$phaseKey]['attempted']++;

                if ($answer->selected_option === $question->correct_option) {
                    $correctCount++;
                    $segments[$phaseKey]['correct']++;
                } else {
                    $errorCount++;
                }
            }
        }

        if ($attemptedCount === 0) {
            return null;
        }

        $accuracyRate = round(($correctCount / $attemptedCount) * 100, 1);
        $completionRate = round(($attemptedCount / $totalItems) * 100, 1);
        $unattemptedCount = max(0, $totalItems - $attemptedCount);

        $durationSeconds = $attempt->started_at && $attempt->submitted_at
            ? max(30, $attempt->submitted_at->diffInSeconds($attempt->started_at))
            : ($assessment->duration_minutes * 60);

        $durationMinutes = max(0.5, $durationSeconds / 60);
        $speedPerMinute = round($attemptedCount / $durationMinutes, 1);

        foreach ($segments as $key => $phase) {
            $att = $phase['attempted'];
            $segments[$key]['accuracy_percent'] = $att > 0
                ? round(($phase['correct'] / $att) * 100, 1)
                : 0.0;
        }

        $stability = $this->evaluateStability($segments);

        $quadrant = $this->determineQuadrant($accuracyRate, $completionRate, $speedPerMinute);

        $riskProfile = $this->evaluateRiskProfile($accuracyRate, $errorCount, $stability['key']);
        $recommendations = $this->generateRecommendations($quadrant, $accuracyRate, $stability);

        return [
            'total_items' => $totalItems,
            'attempted_count' => $attemptedCount,
            'correct_count' => $correctCount,
            'error_count' => $errorCount,
            'unattempted_count' => $unattemptedCount,
            'accuracy_rate' => $accuracyRate,
            'completion_rate' => $completionRate,
            'duration_minutes_spent' => round($durationMinutes, 1),
            'speed_per_minute' => $speedPerMinute,
            'speed_evaluation' => $this->evaluateSpeed($speedPerMinute, $completionRate),
            'accuracy_evaluation' => $this->evaluateAccuracy($accuracyRate),
            'stability' => [
                'status_label' => $stability['label'],
                'status_tone' => $stability['tone'],
                'description' => $stability['description'],
                'segments' => $segments,
            ],
            'quadrant' => $quadrant,
            'risk_profile' => $riskProfile,
            'recommended_roles' => $recommendations['roles'],
            'hr_summary' => $recommendations['summary'],
        ];
    }

    protected function isAccuracyAssessment($assessment): bool
    {
        $title = strtolower((string) $assessment->title);

        return str_contains($title, 'ketelitian')
            || str_contains($title, 'akurasi')
            || str_contains($title, 'clerical')
            || str_contains($title, 'checking')
            || str_contains($title, 'pauli')
            || str_contains($title, 'kraepelin');
    }

    protected function evaluateSpeed(float $speedPerMinute, float $completionRate): array
    {
        if ($completionRate >= 95 || $speedPerMinute >= 4.0) {
            return [
                'label' => 'Sangat Cepat',
                'tone' => 'success',
                'description' => 'Irama kerja sangat tinggi, mampu memproses data dan dokumen dalam tempo cepat.',
            ];
        }

        if ($completionRate >= 80 || $speedPerMinute >= 2.8) {
            return [
                'label' => 'Cepat & Cekatan',
                'tone' => 'primary',
                'description' => 'Kecepatan kerja di atas rata-rata, produktif dalam menyelesaikan target volume harian.',
            ];
        }

        if ($completionRate >= 60 || $speedPerMinute >= 1.8) {
            return [
                'label' => 'Sedang / Moderat',
                'tone' => 'neutral',
                'description' => 'Irama kerja normal dan stabil, memerlukan alokasi waktu standar untuk verifikasi.',
            ];
        }

        return [
            'label' => 'Lambat / Hati-hati',
            'tone' => 'warning',
            'description' => 'Kecepatan pemrosesan data terbatas, butuh waktu lebih panjang untuk menyelesaikan tugas.',
        ];
    }

    protected function evaluateAccuracy(float $accuracyRate): array
    {
        if ($accuracyRate >= 95.0) {
            return [
                'label' => 'Sangat Teliti (High Precision)',
                'tone' => 'success',
                'badge' => 'Presisi Tinggi',
                'description' => 'Tingkat akurasi luar biasa. Sangat peka terhadap anomali angka, salah ketik, dan ketidaksinkronan data.',
            ];
        }

        if ($accuracyRate >= 88.0) {
            return [
                'label' => 'Teliti & Cermat',
                'tone' => 'primary',
                'badge' => 'Cermat',
                'description' => 'Ketelitian baik di atas rata-rata. Menghasilkan pekerjaan yang rapi dan minim kesalahan.',
            ];
        }

        if ($accuracyRate >= 75.0) {
            return [
                'label' => 'Cukup Teliti (Standar)',
                'tone' => 'neutral',
                'badge' => 'Standar',
                'description' => 'Tingkat ketelitian rata-rata. Masih terdapat sedikit kekeliruan kecil pada data yang rumit.',
            ];
        }

        if ($accuracyRate >= 60.0) {
            return [
                'label' => 'Kurang Teliti (Rentan Keliru)',
                'tone' => 'warning',
                'badge' => 'Rentan Keliru',
                'description' => 'Banyak melewatkan ketidakcocokan detail. Perlu dicek ulang oleh rekan kerja lain.',
            ];
        }

        return [
            'label' => 'Sangat Ceroboh / Berisiko',
            'tone' => 'danger',
            'badge' => 'Tinggi Risiko',
            'description' => 'Tingkat kekeliruan tinggi. Tidak disarankan memegang data krusial tanpa pengawasan ketat.',
        ];
    }

    protected function evaluateStability(array $segments): array
    {
        $p1 = $segments['phase_1']['accuracy_percent'];
        $p2 = $segments['phase_2']['accuracy_percent'];
        $p3 = $segments['phase_3']['accuracy_percent'];

        $drop = max(0, max($p1, $p2) - $p3);
        $spread = max($p1, $p2, $p3) - min($p1, $p2, $p3);

        if ($drop >= 15.0 && $p3 < 70) {
            return [
                'key' => 'fatigue',
                'label' => 'Menurun di Akhir (Work Fatigue)',
                'tone' => 'warning',
                'description' => 'Konsentrasi dan akurasi kandidat mengalami penurunan signifikan di fase akhir tes, mengindikasikan rentan lelah saat menghadapi beban kerja monoton berkepanjangan.',
            ];
        }

        if ($p1 < 70 && $p3 >= 85) {
            return [
                'key' => 'warmup',
                'label' => 'Meningkat (Adaptif / Butuh Pemanasan)',
                'tone' => 'primary',
                'description' => 'Kandidat membutuhkan waktu adaptasi di awal, namun konsentrasi dan akurasinya meningkat tajam seiring berjalannya pekerjaan.',
            ];
        }

        if ($spread <= 12.0) {
            return [
                'key' => 'stable',
                'label' => 'Sangat Stabil & Konsisten',
                'tone' => 'success',
                'description' => 'Daya tahan konsentrasi tinggi. Mampu mempertahankan fokus dan ketelitian yang merata dari awal hingga akhir tanpa terpengaruh rasa jenuh.',
            ];
        }

        return [
            'key' => 'fluctuating',
            'label' => 'Fluktuatif (Konsentrasi Naik-Turun)',
            'tone' => 'neutral',
            'description' => 'Fokus kerja tidak konstan, kadang sangat cermat namun sesekali teralihkan sehingga terjadi kekeliruan acak.',
        ];
    }

    protected function determineQuadrant(float $accuracy, float $completion, float $speed): array
    {
        $isAccurate = $accuracy >= 85.0;
        $isFast = $completion >= 85.0 || $speed >= 3.0;

        if ($isAccurate && $isFast) {
            return [
                'code' => 'Q1',
                'title' => 'Optimal: Cepat & Sangat Teliti',
                'subtitle' => 'The Efficient Perfectionist',
                'tone' => 'success',
                'description' => 'Kombinasi kerja terbaik. Mampu menuntaskan beban kerja besar dalam tempo singkat dengan tingkat kepatuhan data dan akurasi mendekati sempurna.',
            ];
        }

        if ($isAccurate && ! $isFast) {
            return [
                'code' => 'Q2',
                'title' => 'Hati-Hati & Akurat',
                'subtitle' => 'The Careful Verifier',
                'tone' => 'primary',
                'description' => 'Sangat mengutamakan ketepatan dan kebenaran data di atas kecepatan. Jarang melakukan kesalahan karena terbiasa memverifikasi ulang hasil kerjanya.',
            ];
        }

        if (! $isAccurate && $isFast) {
            return [
                'code' => 'Q3',
                'title' => 'Cepat tapi Kurang Cermat',
                'subtitle' => 'The Impulsive Rusher',
                'tone' => 'warning',
                'description' => 'Memiliki semangat kerja dan kecepatan tinggi, namun cenderung tergesa-gesa sehingga sering melewatkan detail kecil yang berpotensi fatal.',
            ];
        }

        return [
            'code' => 'Q4',
            'title' => 'Lambat & Kurang Cermat',
            'subtitle' => 'The Struggler / High Risk',
            'tone' => 'danger',
            'description' => 'Menunjukkan kesulitan ganda baik dari segi kecepatan pemrosesan maupun ketepatan analisa detail. Rentan kewalahan pada tugas administrasi padat.',
        ];
    }

    protected function evaluateRiskProfile(float $accuracyRate, int $errorCount, string $stabilityKey): array
    {
        if ($accuracyRate >= 92 && $errorCount <= 3) {
            return [
                'level' => 'Sangat Rendah (Aman)',
                'tone' => 'success',
                'notes' => 'Sangat aman dipercaya mengelola data sensitif, transaksi keuangan bernilai besar, dan dokumen legal penting.',
            ];
        }

        if ($accuracyRate >= 80 && $errorCount <= 8) {
            return [
                'level' => 'Moderat (Dapat Ditoleransi)',
                'tone' => 'primary',
                'notes' => 'Cukup aman untuk operasional harian standar, namun disarankan menerapkan prosedur double-check berkala.',
            ];
        }

        return [
            'level' => 'Tinggi (Berisiko Kekeliruan)',
            'tone' => 'danger',
            'notes' => 'Berisiko menimbulkan salah hitung / salah input data. Wajib dilakukan verifikasi silang (peer check) oleh tim sebelum eksekusi.',
        ];
    }

    protected function generateRecommendations(array $quadrant, float $accuracy, array $stability): array
    {
        $roles = match ($quadrant['code']) {
            'Q1' => ['Senior Finance & Tax Officer', 'Internal Auditor', 'Data Quality Specialist', 'Compliance Officer', 'Supply Chain Analyst'],
            'Q2' => ['Accountant / Verifier', 'Legal Drafter & Proofreader', 'Database Administrator', 'Pharmacy / Lab Assistant', 'Archivist'],
            'Q3' => ['Field Sales Executive', 'Customer Onboarding', 'Fast-paced Dispatcher', 'Social Media Coordinator', 'General Operations'],
            default => ['General Field Support', 'Manual Labor Support', 'Helper / Asisten Umum'],
        };

        $summary = "Kandidat berada pada kuadran {$quadrant['title']} dengan tingkat ketelitian murni {$accuracy}%. Kestabilan kerja tergolong {$stability['label']}. {$quadrant['description']}";

        return [
            'roles' => $roles,
            'summary' => $summary,
        ];
    }
}
