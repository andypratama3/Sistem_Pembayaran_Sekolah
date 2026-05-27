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
        Schema::create('payment_titles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id')->index();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('classroom_type', 100);
            $table->string('email')->nullable();
            $table->decimal('gross_amount', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('session_id')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('bulk_id')->nullable();
            $table->string('account_id')->nullable();
            $table->foreignUuid('payment_title_id')->constrained('payment_titles')->cascadeOnDelete();
            $table->string('status', 50)->default('pending');
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        Schema::create('charges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('order_id')->unique();
            $table->string('order_id_alt')->nullable();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('gross_amount', 15, 2);
            $table->string('payment_type', 50)->default('bank_transfer');
            $table->string('bank', 50)->nullable();
            $table->string('va_number', 100)->nullable();
            $table->string('transaction_id');
            $table->dateTime('transaction_time')->nullable();
            $table->string('fraud_status', 20)->default('accept');
            $table->foreignUuid('payment_title_id')->constrained('payment_titles')->cascadeOnDelete();
            $table->string('transaction_status', 50);
            $table->string('action_name')->nullable();
            $table->string('method', 50)->nullable();
            $table->text('action_url')->nullable();
            $table->string('snap_token')->nullable();
            $table->softDeletes()->index();
            $table->timestamps();

            $table->index(['student_id', 'transaction_status']);
            $table->index('transaction_id');
        });

        Schema::create('charges_archive', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('order_id')->nullable();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('gross_amount', 15, 2);
            $table->string('payment_type', 50)->default('bank_transfer');
            $table->string('bank', 50)->nullable();
            $table->string('va_number', 100)->nullable();
            $table->string('transaction_id');
            $table->dateTime('transaction_time')->nullable();
            $table->string('fraud_status', 20)->default('accept');
            $table->string('transaction_status', 50);
            $table->string('snap_token')->nullable();
            $table->softDeletes()->index();
            $table->timestamps();
        });

        Schema::create('student_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('payment_title_id')->constrained('payment_titles')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->enum('status', ['belum_bayar', 'sebagian', 'lunas', 'dibebaskan'])->default('belum_bayar');
            $table->string('academic_year', 20);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'academic_year', 'status'], 'student_fees_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fees');
        Schema::dropIfExists('charges_archive');
        Schema::dropIfExists('charges');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_titles');
    }
};
