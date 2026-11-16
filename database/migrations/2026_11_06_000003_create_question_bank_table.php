<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('scale');
            $table->text('question');
            $table->string('dimension', 50)->nullable();
            $table->json('options')->nullable();
            $table->unsignedTinyInteger('correct_option')->nullable();
            $table->unsignedSmallInteger('points')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('type');
            $table->index('dimension');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
