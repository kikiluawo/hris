<?php

namespace Database\Seeders;

use App\Models\JobOpening;
use Illuminate\Database\Seeder;

class UpdateJobOpeningsTypeSeeder extends Seeder
{
    public function run(): void
    {
        $openings = JobOpening::all();

        foreach ($openings as $opening) {
            $text = mb_strtolower($opening->title . ' ' . $opening->description);

            if (preg_match('/\b(intern|internship|magang|pkl|prakerin)\b/ui', $text)) {
                $type = JobOpening::TYPE_INTERNSHIP;
            } elseif (preg_match('/\b(part[\s\-_]?time|paruh[\s\-_]?waktu|freelance|harian[\s\-_]?lepas)\b/ui', $text)) {
                $type = JobOpening::TYPE_PART_TIME;
            } else {
                $type = JobOpening::TYPE_FULL_TIME;
            }

            $opening->update([
                'job_type' => $type,
                'location' => $opening->location ?: 'Batam',
            ]);
        }
    }
}
