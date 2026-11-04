<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('phone')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained()->restrictOnDelete();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropColumn(['department', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('department')->nullable()->after('phone');
            $table->string('position')->nullable()->after('department');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('position_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
