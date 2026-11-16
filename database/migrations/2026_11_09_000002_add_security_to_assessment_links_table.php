<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_links', function (Blueprint $table) {
            $table->string('access_mode', 20)->default('applicants_only')->after('duration_minutes');
            $table->timestamp('expires_at')->nullable()->after('access_mode');
            $table->timestamp('closed_at')->nullable()->after('expires_at');
        });

        DB::table('assessment_links')->whereNull('expires_at')->update([
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function down(): void
    {
        Schema::table('assessment_links', function (Blueprint $table) {
            $table->dropColumn(['access_mode', 'expires_at', 'closed_at']);
        });
    }
};