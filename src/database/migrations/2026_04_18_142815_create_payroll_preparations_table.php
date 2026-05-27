<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_preparations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('academic_year');
            $table->integer('month');
            $table->decimal('base_salary', 15, 2);
            $table->decimal('total_addition', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('final_salary', 15, 2);
            $table->json('metadata'); // For dynamic components (allowances, bonuses, deductions)
            $table->timestamps();

            $table->index(['academic_year', 'month']);
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_preparations');
    }
};
