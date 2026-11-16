<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['assessment_id', 'application_id']);
        });

        DB::table('assessment_answers')->delete();
        DB::table('assessment_events')->delete();
        DB::table('assessment_attempts')->delete();

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropForeign(['assessment_link_id']);
            $table->dropUnique('assessment_attempt_candidate_unique');
            $table->dropForeign(['application_id']);
            $table->dropColumn(['assessment_link_id', 'candidate_name', 'candidate_email', 'application_id']);
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->foreignId('assessment_invitation_id')->after('id')->constrained()->cascadeOnDelete();
            $table->unique('assessment_invitation_id');
        });

        Schema::dropIfExists('assessment_links');
    }

    public function down(): void
    {
        Schema::create('assessment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('label', 100)->nullable();
            $table->string('token', 64)->unique();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('access_mode', 20)->default('applicants_only');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('assessment_answers')->delete();
        DB::table('assessment_events')->delete();
        DB::table('assessment_attempts')->delete();

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropForeign(['assessment_invitation_id']);
            $table->dropUnique(['assessment_invitation_id']);
            $table->dropColumn('assessment_invitation_id');
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->foreignId('assessment_link_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('candidate_name', 150)->nullable();
            $table->string('candidate_email', 190)->nullable();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->unique(['assessment_link_id', 'candidate_email'], 'assessment_attempt_candidate_unique');
        });

        Schema::dropIfExists('assessment_invitations');
    }
};