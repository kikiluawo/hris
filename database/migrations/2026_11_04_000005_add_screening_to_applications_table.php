<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('screening_score')->nullable()->after('status');
            $table->json('screening_data')->nullable()->after('screening_score');
            $table->timestamp('screened_at')->nullable()->after('screening_data');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['screening_score', 'screening_data', 'screened_at']);
        });
    }
};
