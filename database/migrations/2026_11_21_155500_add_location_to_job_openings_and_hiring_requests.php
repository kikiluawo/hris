<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_openings', function (Blueprint $table) {
            $table->string('location', 100)->nullable()->default('Batam')->after('position_id');
        });

        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->string('location', 100)->nullable()->default('Batam')->after('position_id');
        });
    }

    public function down(): void
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->dropColumn('location');
        });

        Schema::table('job_openings', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
