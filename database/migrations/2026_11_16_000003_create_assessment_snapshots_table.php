<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained('assessment_attempts')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('trigger', 50)->default('periodic');
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['assessment_attempt_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_snapshots');
    }
};
