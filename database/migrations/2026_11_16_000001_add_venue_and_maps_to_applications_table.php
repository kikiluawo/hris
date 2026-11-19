<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('interview_address', 500)->nullable()->after('interview_location');
            $table->string('interview_maps_url', 500)->nullable()->after('interview_address');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['interview_address', 'interview_maps_url']);
        });
    }
};
