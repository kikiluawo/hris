<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('hrd')->after('is_hrd');
        });

        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->string('request_type', 20)->default('baru')->after('requesting_unit');
            $table->string('replaced_employee_name')->nullable()->after('request_type');
            $table->text('replacement_reason')->nullable()->after('replaced_employee_name');

            $table->string('approval_route', 20)->nullable()->after('status');
            $table->string('ceo_approval_status', 20)->nullable()->after('approval_route');
            $table->foreignId('ceo_approved_by')->nullable()->after('ceo_approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('ceo_approved_at')->nullable()->after('ceo_approved_by');
            $table->text('ceo_notes')->nullable()->after('ceo_approved_at');

            $table->string('cfo_approval_status', 20)->nullable()->after('ceo_notes');
            $table->foreignId('cfo_approved_by')->nullable()->after('cfo_approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('cfo_approved_at')->nullable()->after('cfo_approved_by');
            $table->text('cfo_notes')->nullable()->after('cfo_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cfo_approved_by');
            $table->dropConstrainedForeignId('ceo_approved_by');
            $table->dropColumn([
                'request_type',
                'replaced_employee_name',
                'replacement_reason',
                'approval_route',
                'ceo_approval_status',
                'ceo_approved_at',
                'ceo_notes',
                'cfo_approval_status',
                'cfo_approved_at',
                'cfo_notes',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
