<?php

namespace App\Console\Commands;

use App\Services\AptitudeBank;
use Illuminate\Console\Command;

class SeedAptitudeAssessment extends Command
{
    protected $signature = 'assessment:seed-aptitude';

    protected $description = 'Generate atau sinkronisasi paket 30 soal tes bakat kognitif & estimasi IQ standar ke database';

    public function handle(): int
    {
        $this->info('Membuat paket tes bakat kognitif & IQ (30 soal)...');

        $assessment = AptitudeBank::createOrUpdateAssessment();

        $this->info("Berhasil! Tes Bakat & IQ ID: {$assessment->id} ('{$assessment->title}') dengan {$assessment->questions()->count()} soal siap digunakan.");

        return Command::SUCCESS;
    }
}
