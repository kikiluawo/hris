<?php

namespace App\Console\Commands;

use App\Services\ComprehensiveBank;
use Illuminate\Console\Command;

class SeedComprehensiveAssessment extends Command
{
    protected $signature = 'assessment:seed-comprehensive';

    protected $description = 'Generate atau sinkronisasi paket tes komprehensif (DISC + IQ + Ketelitian) 94 soal ke database';

    public function handle(): int
    {
        $this->info('Membuat paket tes komprehensif terpadu (94 soal)...');

        $assessment = ComprehensiveBank::createOrUpdateAssessment();

        $this->info("Berhasil! Tes Komprehensif ID: {$assessment->id} ('{$assessment->title}') dengan {$assessment->questions()->count()} soal siap digunakan.");

        return Command::SUCCESS;
    }
}
