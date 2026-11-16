<?php

namespace App\Console\Commands;

use App\Services\QuestionBankSyncService;
use Illuminate\Console\Command;

class SyncQuestionBank extends Command
{
    protected $signature = 'assessment:sync-bank';

    protected $description = 'Sinkronkan 94 butir soal standar (DISC, IQ & Ketelitian) ke bank soal (question_banks)';

    public function handle(): int
    {
        $this->info('Memulai sinkronisasi 94 butir soal standar ke bank soal...');

        $result = QuestionBankSyncService::syncStandardQuestions();

        $this->info("Selesai! {$result['added']} soal baru ditambahkan, {$result['skipped']} soal sudah ada sebelumnya (dilewati).");

        return Command::SUCCESS;
    }
}
