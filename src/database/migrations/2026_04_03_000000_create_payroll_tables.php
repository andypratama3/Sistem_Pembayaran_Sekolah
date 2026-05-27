<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Salary components (elements)
        Schema::create('salary_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('component_type', ['base', 'allowance', 'deduction']);
            $table->decimal('amount', 15, 2)->nullable();
            $table->enum('calculation_type', ['fixed', 'percentage'])->default('fixed');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('component_type');
            $table->index('is_active');
        });

        // Employee salary configurations per academic year
        Schema::create('employee_salary_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('academic_year');
            $table->json('salary_components');
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['employee_id', 'academic_year']);
            $table->index('employee_id');
            $table->index('academic_year');
            $table->index('is_active');
        });

        // Payroll run records (monthly execution)
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('academic_year');
            $table->integer('month');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->foreignUuid('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('academic_year');
            $table->index('month');
            $table->index('status');
            $table->unique(['academic_year', 'month']);
        });

        // Individual payroll records per employee
        Schema::create('employee_payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('gross_salary', 15, 2);
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->enum('payment_method', ['bank_transfer', 'cash', 'check'])->default('bank_transfer');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('payroll_run_id');
            $table->index('employee_id');
            $table->unique(['payroll_run_id', 'employee_id']);
        });

        // Payroll details - line items per payroll
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_payroll_id')->constrained('employee_payrolls')->cascadeOnDelete();
            $table->foreignUuid('salary_component_id')->constrained('salary_components')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('employee_payroll_id');
            $table->index('salary_component_id');
        });

        // Payroll audit trail
        Schema::create('payroll_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
            $table->foreignUuid('employee_payroll_id')->nullable()->constrained('employee_payrolls')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->json('changes')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index('payroll_run_id');
            $table->index('employee_payroll_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_audit_logs');
        Schema::dropIfExists('payroll_details');
        Schema::dropIfExists('employee_payrolls');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('employee_salary_configurations');
        Schema::dropIfExists('salary_components');
    }
};
