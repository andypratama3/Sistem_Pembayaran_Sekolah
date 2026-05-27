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
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_preparation_id')->constrained('payroll_preparations')->cascadeOnDelete();
            $table->string('type'); // 'allowance', 'deduction', 'bonus'
            $table->string('name'); // e.g., "Tunjangan Transport", "Potongan BPJS"
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive, removed
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('payroll_preparation_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
