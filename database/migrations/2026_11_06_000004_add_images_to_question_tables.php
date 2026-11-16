<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->string('image_path', 80)->nullable()->after('question');
            $table->json('option_images')->nullable()->after('options');
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->string('image_path', 80)->nullable()->after('question');
            $table->json('option_images')->nullable()->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'option_images']);
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'option_images']);
        });
    }
};
