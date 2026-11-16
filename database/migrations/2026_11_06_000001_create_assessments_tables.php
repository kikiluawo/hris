<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->unsignedTinyInteger('passing_score')->default(70);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('multiple_choice');
            $table->text('question');
            $table->json('options')->nullable();
            $table->unsignedTinyInteger('correct_option')->nullable();
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['assessment_id', 'position']);
        });

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

        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_invitation_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('in_progress');
            $table->dateTime('started_at');
            $table->dateTime('ends_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedTinyInteger('violation_count')->default(0);
            $table->string('end_reason', 30)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('selected_option')->nullable();
            $table->text('answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['assessment_attempt_id', 'assessment_question_id'], 'assessment_answer_unique');
        });

        Schema::create('assessment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->json('meta')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
            $table->index(['assessment_attempt_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_events');
        Schema::dropIfExists('assessment_answers');
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('assessment_invitations');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }
};
