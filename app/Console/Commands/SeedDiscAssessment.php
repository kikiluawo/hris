<?php

namespace App\Console\Commands;

use App\Services\DiscBank;
use Illuminate\Console\Command;

class SeedDiscAssessment extends Command
{
    protected $signature = 'assessment:seed-disc';

    protected $description = 'Generate atau sinkronisasi 24 soal tes kepribadian DISC standar ke database';

    public function handle(): int
    {
        $this->info('Membuat paket tes kepribadian DISC (24 soal)...');

        $assessment = DiscBank::createOrUpdateAssessment();

        $this->info("Berhasil! Tes DISC ID: {$assessment->id} ('{$assessment->title}') dengan {$assessment->questions()->count()} soal siap digunakan.");

        return Command::SUCCESS;
    }
}
