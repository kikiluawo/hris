<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_application_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('submitted_at')->nullable();
            $table->string('photo_path')->nullable();
            $table->json('personal_data')->nullable();
            $table->json('family_data')->nullable();
            $table->json('education_data')->nullable();
            $table->json('work_history')->nullable();
            $table->json('additional_info')->nullable();
            $table->json('office_use')->nullable();
            $table->string('signature_name')->nullable();
            $table->date('signed_date')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_biodatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 20)->default('draft');
            $table->json('personal_data')->nullable();
            $table->json('bank_accounts')->nullable();
            $table->json('education_formal')->nullable();
            $table->json('education_informal')->nullable();
            $table->json('work_experiences')->nullable();
            $table->json('residences_history')->nullable();
            $table->json('family_details')->nullable();
            $table->json('organization_history')->nullable();
            $table->json('attachments')->nullable();
            $table->string('signed_city', 50)->default('Batam');
            $table->string('signed_name')->nullable();
            $table->date('signed_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_biodatas');
        Schema::dropIfExists('job_application_forms');
    }
};
