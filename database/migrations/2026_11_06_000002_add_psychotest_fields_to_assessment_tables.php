<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->string('dimension', 50)->nullable()->after('question');
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->json('dimension_scores')->nullable()->after('score');
            $table->text('review_notes')->nullable()->after('dimension_scores');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropColumn(['dimension_scores', 'review_notes']);
        });

        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropColumn('dimension');
        });
    }
};
