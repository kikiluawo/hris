<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->unique();
            $table->string('brand', 50)->nullable();
            $table->string('type', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_code_unique');
            $table->dropColumn(['code', 'brand', 'type']);
        });
    }
};
