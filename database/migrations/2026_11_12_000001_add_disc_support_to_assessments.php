<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_answers', function (Blueprint $table) {
            $table->unsignedTinyInteger('most_option')->nullable()->after('selected_option');
            $table->unsignedTinyInteger('least_option')->nullable()->after('most_option');
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->json('disc_report')->nullable()->after('dimension_scores');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            $table->dropColumn('disc_report');
        });

        Schema::table('assessment_answers', function (Blueprint $table) {
            $table->dropColumn(['most_option', 'least_option']);
        });
    }
};
