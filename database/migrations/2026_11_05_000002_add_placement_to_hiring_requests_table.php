<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('screening_threshold')->default(70)->after('min_experience');
        });
    }

    public function down(): void
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('position_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('screening_threshold');
        });
    }
};
