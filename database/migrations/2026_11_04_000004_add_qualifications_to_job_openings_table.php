<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_openings', function (Blueprint $table) {
            $table->json('required_skills')->nullable()->after('description');
            $table->string('min_education', 20)->nullable()->after('required_skills');
            $table->unsignedTinyInteger('min_experience')->nullable()->after('min_education');
        });
    }

    public function down(): void
    {
        Schema::table('job_openings', function (Blueprint $table) {
            $table->dropColumn(['required_skills', 'min_education', 'min_experience']);
        });
    }
};
