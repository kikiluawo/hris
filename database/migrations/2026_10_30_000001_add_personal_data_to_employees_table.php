<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('contract_1_start')->nullable()->after('joined_at');
            $table->date('contract_1_end')->nullable()->after('contract_1_start');
            $table->date('contract_2_start')->nullable()->after('contract_1_end');
            $table->date('contract_2_end')->nullable()->after('contract_2_start');
            $table->string('note')->nullable()->after('contract_2_end');
            $table->string('birth_place')->nullable()->after('note');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('gender', 10)->nullable()->after('birth_date');
            $table->string('religion', 30)->nullable()->after('gender');
            $table->string('marital_status', 30)->nullable()->after('religion');
            $table->unsignedTinyInteger('daughters_count')->default(0)->after('marital_status');
            $table->unsignedTinyInteger('sons_count')->default(0)->after('daughters_count');
            $table->text('address')->nullable()->after('sons_count');
            $table->string('ktp_no', 32)->nullable()->after('address');
            $table->string('npwp_no', 32)->nullable()->after('ktp_no');
            $table->string('bpjs_tk_no', 32)->nullable()->after('npwp_no');
            $table->string('bpjs_kes_no', 32)->nullable()->after('bpjs_tk_no');
            $table->string('spouse_name')->nullable()->after('bpjs_kes_no');
            $table->string('father_name')->nullable()->after('spouse_name');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('emergency_phone', 32)->nullable()->after('mother_name');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'contract_1_start',
                'contract_1_end',
                'contract_2_start',
                'contract_2_end',
                'note',
                'birth_place',
                'birth_date',
                'gender',
                'religion',
                'marital_status',
                'daughters_count',
                'sons_count',
                'address',
                'ktp_no',
                'npwp_no',
                'bpjs_tk_no',
                'bpjs_kes_no',
                'spouse_name',
                'father_name',
                'mother_name',
                'emergency_phone',
            ]);
        });
    }
};
