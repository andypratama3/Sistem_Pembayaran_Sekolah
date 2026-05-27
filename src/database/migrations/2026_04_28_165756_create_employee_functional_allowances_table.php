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
        Schema::create('employee_functional_allowances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('employee_id', 36);
            $table->char('functional_allowance_id', 36);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('functional_allowance_id')
                ->references('id')->on('functional_allowances')->cascadeOnDelete();

            $table->index('employee_id');
            $table->index('functional_allowance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_functional_allowances');
    }
};
