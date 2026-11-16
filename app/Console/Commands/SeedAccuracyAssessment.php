<?php

namespace App\Console\Commands;

use App\Services\AccuracyBank;
use Illuminate\Console\Command;

class SeedAccuracyAssessment extends Command
{
    protected $signature = 'assessment:seed-accuracy';

    protected $description = 'Generate atau sinkronisasi paket 40 soal tes ketelitian & sikap kerja standar ke database';

    public function handle(): int
    {
        $this->info('Membuat paket tes ketelitian kerja (40 soal)...');

        $assessment = AccuracyBank::createOrUpdateAssessment();

        $this->info("Berhasil! Tes Ketelitian ID: {$assessment->id} ('{$assessment->title}') dengan {$assessment->questions()->count()} soal siap digunakan.");

        return Command::SUCCESS;
    }
}
