<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hiring_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requester_name');
            $table->string('requester_position');
            $table->string('requester_phone', 30);
            $table->string('requester_email')->nullable();
            $table->string('requesting_unit')->nullable();
            $table->string('title');
            $table->unsignedSmallInteger('headcount')->default(1);
            $table->text('description');
            $table->json('required_skills')->nullable();
            $table->string('min_education', 20)->nullable();
            $table->unsignedTinyInteger('min_experience')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('baru')->index();
            $table->text('hr_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('job_opening_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hiring_requests');
    }
};
