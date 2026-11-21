<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_openings', function (Blueprint $table) {
            $table->string('job_type', 30)->default('Full Time')->after('title');
        });

        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->string('job_type', 30)->default('Full Time')->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('job_openings', function (Blueprint $table) {
            $table->dropColumn('job_type');
        });

        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->dropColumn('job_type');
        });
    }
};
