<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_openings', function (Blueprint $table) {
            $table->unsignedTinyInteger('screening_threshold')->default(70)->after('min_experience');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('screening_notified_at')->nullable()->after('screened_at');
            $table->timestamp('interview_at')->nullable()->after('screening_notified_at');
            $table->string('interview_mode', 20)->nullable()->after('interview_at');
            $table->string('interview_location')->nullable()->after('interview_mode');
            $table->text('interview_notes')->nullable()->after('interview_location');
            $table->timestamp('interview_sent_at')->nullable()->after('interview_notes');
            $table->timestamp('decided_at')->nullable()->after('interview_sent_at');
            $table->foreignId('decided_by')->nullable()->after('decided_at')->constrained('users')->nullOnDelete();
            $table->index('status');
        });

        $this->deduplicateCandidates();
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique(['job_opening_id', 'candidate_id']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('decided_by');
            $table->dropColumn([
                'screening_notified_at',
                'interview_at',
                'interview_mode',
                'interview_location',
                'interview_notes',
                'interview_sent_at',
                'decided_at',
            ]);
        });

        Schema::table('job_openings', function (Blueprint $table) {
            $table->dropColumn('screening_threshold');
        });
    }

    /**
     * Satu kandidat = satu email. Gabungkan data lama yang terlanjur dobel
     * supaya unique index bisa dipasang tanpa kehilangan riwayat lamaran.
     */
    private function deduplicateCandidates(): void
    {
        $duplicateEmails = DB::table('candidates')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('email');

        foreach ($duplicateEmails as $email) {
            $candidateIds = DB::table('candidates')
                ->where('email', $email)
                ->orderBy('id')
                ->pluck('id');

            $keep = (int) $candidateIds->shift();

            foreach ($candidateIds as $candidateId) {
                $applications = DB::table('applications')
                    ->where('candidate_id', $candidateId)
                    ->get();

                foreach ($applications as $application) {
                    $alreadyExists = DB::table('applications')
                        ->where('candidate_id', $keep)
                        ->where('job_opening_id', $application->job_opening_id)
                        ->exists();

                    if ($alreadyExists) {
                        DB::table('applications')->where('id', $application->id)->delete();
                    } else {
                        DB::table('applications')
                            ->where('id', $application->id)
                            ->update(['candidate_id' => $keep]);
                    }
                }

                DB::table('candidates')->where('id', $candidateId)->delete();
            }
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->unique('email');
        });

        $this->deduplicateApplications();

        Schema::table('applications', function (Blueprint $table) {
            $table->unique(['job_opening_id', 'candidate_id']);
        });
    }

    /**
     * Satu kandidat hanya boleh punya satu lamaran per lowongan.
     */
    private function deduplicateApplications(): void
    {
        $duplicates = DB::table('applications')
            ->select('job_opening_id', 'candidate_id')
            ->groupBy('job_opening_id', 'candidate_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('applications')
                ->where('job_opening_id', $duplicate->job_opening_id)
                ->where('candidate_id', $duplicate->candidate_id)
                ->orderBy('id')
                ->pluck('id');

            $ids->shift();

            if ($ids->isNotEmpty()) {
                DB::table('applications')->whereIn('id', $ids)->delete();
            }
        }
    }
};
