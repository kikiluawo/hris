<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_biodatas', function (Blueprint $table) {
            $table->longText('digital_signature')->nullable()->after('signed_date');
        });
    }

    public function down(): void
    {
        Schema::table('employee_biodatas', function (Blueprint $table) {
            $table->dropColumn('digital_signature');
        });
    }
};
