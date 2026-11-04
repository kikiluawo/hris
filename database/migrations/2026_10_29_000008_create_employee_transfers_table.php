<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->restrictOnDelete();
            $table->foreignId('from_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('from_position_id')->constrained('positions')->restrictOnDelete();
            $table->foreignId('to_company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->restrictOnDelete();
            $table->foreignId('to_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('to_position_id')->constrained('positions')->restrictOnDelete();
            $table->date('effective_date')->index();
            $table->boolean('position_changed')->default(false);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_transfers');
    }
};
