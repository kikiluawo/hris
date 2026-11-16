<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('label', 100)->nullable();
            $table->string('token', 64)->unique();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropForeign(['assessment_invitation_id']);
            $table->dropUnique(['assessment_invitation_id']);
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->foreignId('assessment_link_id')->nullable()->after('id')->constrained('assessment_links')->cascadeOnDelete();
            $table->string('token', 64)->nullable()->after('assessment_link_id');
            $table->string('candidate_name', 150)->nullable()->after('token');
            $table->string('candidate_email', 190)->nullable()->after('candidate_name');
            $table->foreignId('application_id')->nullable()->after('candidate_email')->constrained('applications')->nullOnDelete();
        });

        $now = now();

        foreach (DB::table('assessment_invitations')->orderBy('id')->get() as $invitation) {
            $candidate = DB::table('applications')
                ->join('candidates', 'candidates.id', '=', 'applications.candidate_id')
                ->where('applications.id', $invitation->application_id)
                ->select('candidates.name', 'candidates.email')
                ->first();

            $linkId = DB::table('assessment_links')->insertGetId([
                'assessment_id' => $invitation->assessment_id,
                'label' => 'Sesi pindahan',
                'token' => Str::random(48),
                'duration_minutes' => null,
                'created_by' => null,
                'created_at' => $invitation->created_at ?? $now,
                'updated_at' => $now,
            ]);

            DB::table('assessment_attempts')
                ->where('assessment_invitation_id', $invitation->id)
                ->update([
                    'assessment_link_id' => $linkId,
                    'token' => Str::random(48),
                    'candidate_name' => $candidate->name ?? 'Kandidat',
                    'candidate_email' => strtolower((string) ($candidate->email ?? '')) ?: 'tanpa-email-'.$invitation->id.'@migrasi.local',
                    'application_id' => $invitation->application_id,
                ]);
        }

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropColumn('assessment_invitation_id');
            $table->unique('token');
            $table->unique(['assessment_link_id', 'candidate_email'], 'assessment_attempt_candidate_unique');
        });

        Schema::dropIfExists('assessment_invitations');
    }

    public function down(): void
    {
        Schema::create('assessment_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['assessment_id', 'application_id']);
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropUnique('assessment_attempt_candidate_unique');
            $table->dropUnique(['token']);
            $table->unsignedBigInteger('assessment_invitation_id')->nullable();
        });

        foreach (DB::table('assessment_attempts')->orderBy('id')->get() as $attempt) {
            if ($attempt->application_id === null || $attempt->assessment_link_id === null) {
                continue;
            }

            $link = DB::table('assessment_links')->where('id', $attempt->assessment_link_id)->first();

            if ($link === null) {
                continue;
            }

            $invitationId = DB::table('assessment_invitations')->insertGetId([
                'assessment_id' => $link->assessment_id,
                'application_id' => $attempt->application_id,
                'token' => $attempt->token,
                'sent_at' => $attempt->started_at,
                'expires_at' => null,
                'created_at' => $attempt->created_at,
                'updated_at' => $attempt->updated_at,
            ]);

            DB::table('assessment_attempts')->where('id', $attempt->id)->update([
                'assessment_invitation_id' => $invitationId,
            ]);
        }

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropForeign(['assessment_link_id']);
            $table->dropForeign(['application_id']);
            $table->dropColumn(['assessment_link_id', 'token', 'candidate_name', 'candidate_email', 'application_id']);
            $table->unique('assessment_invitation_id');
            $table->foreign('assessment_invitation_id')->references('id')->on('assessment_invitations')->cascadeOnDelete();
        });

        Schema::dropIfExists('assessment_links');
    }
};