<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\User;

class ComprehensiveBank
{
    
    public static function createOrUpdateAssessment(?User $creator = null): Assessment
    {
        $assessment = Assessment::firstOrNew(['title' => 'Tes Komprehensif HRD (Kepribadian, IQ & Ketelitian)']);

        $assessment->fill([
            'description' => 'Paket asesmen terpadu all-in-one yang menggabungkan Tes Kepribadian DISC (24 soal), Tes Bakat Kognitif & IQ (30 soal), dan Tes Ketelitian Kerja (40 soal).',
            'instructions' => "Tes ini terdiri dari 3 bagian penting:\n\nBagian 1: Profil Kepribadian DISC (Soal 1–24)\nPilih 1 pernyataan Paling Sesuai [P] dan 1 Paling Tidak Sesuai [K].\n\nBagian 2: Potensi Kognitif & Bakat Skolastik (Soal 25–54)\nPilih 1 jawaban yang paling tepat dari opsi A sampai E (Verbal, Numerik, Logika, Spasial).\n\nBagian 3: Ketelitian Kerja & Verifikasi Data (Soal 55–94)\nBandingkan Kolom A dan Kolom B secara teliti dan cepat, lalu tentukan apakah SAMA atau BEDA.\n\nDurasi pengerjaan: 50 menit.",
            'duration_minutes' => 50,
            'passing_score' => 70,
            'status' => Assessment::STATUS_PUBLISHED,
            'created_by' => $creator?->id ?? $assessment->created_by ?? User::first()?->id,
        ]);

        $assessment->save();
        $assessment->questions()->delete();

        $position = 1;

        foreach (DiscBank::questions() as $discItem) {
            $assessment->questions()->create([
                'type' => AssessmentQuestion::TYPE_DISC,
                'question' => $discItem['question'],
                'dimension' => $discItem['dimension'] ?? 'S,I,D,C',
                'options' => $discItem['options'],
                'correct_option' => null,
                'points' => 0,
                'position' => $position++,
            ]);
        }

        foreach (AptitudeBank::questions() as $aptItem) {
            $assessment->questions()->create([
                'type' => AssessmentQuestion::TYPE_MULTIPLE_CHOICE,
                'question' => $aptItem['question'],
                'dimension' => $aptItem['dimension'],
                'options' => $aptItem['options'],
                'correct_option' => $aptItem['correct_option'],
                'points' => 1,
                'position' => $position++,
            ]);
        }

        foreach (AccuracyBank::questions() as $accItem) {
            $assessment->questions()->create([
                'type' => AssessmentQuestion::TYPE_MULTIPLE_CHOICE,
                'question' => $accItem['question'],
                'dimension' => $accItem['dimension'],
                'options' => $accItem['options'],
                'correct_option' => $accItem['correct_option'],
                'points' => 1,
                'position' => $position++,
            ]);
        }

        QuestionBankSyncService::syncStandardQuestions($creator);

        return $assessment;
    }
}
