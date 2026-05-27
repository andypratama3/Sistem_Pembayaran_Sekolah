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
        Schema::create('payroll_salary_rates', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('key', 60)->unique();   // 'seniority_per_year', 'overtime_per_hour', 'family_per_dependent'
            $table->string('label');
            $table->decimal('amount', 15, 2);
            $table->string('unit', 30);            // 'per_year', 'per_hour', 'per_person'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_salary_rates');
    }
};
