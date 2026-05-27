<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Student Promotions
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->constrained()->cascadeOnDelete();
            $table->uuid('from_classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->uuid('to_classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->uuid('academic_year_id')->constrained()->nullable();
            $table->uuid('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('promoted_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
            $table->index(['academic_year_id', 'status']);
        });

        // Student Transfers
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['masuk', 'keluar']);
            $table->string('previous_school_name')->nullable();
            $table->text('previous_school_address')->nullable();
            $table->string('previous_school_npsn')->nullable();
            $table->uuid('from_classroom_id')->nullable()->constrained('classrooms');
            $table->date('transfer_date');
            $table->json('documents')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'type']);
            $table->index(['status']);
        });

        // Student Retentions
        Schema::create('student_retentions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->constrained()->cascadeOnDelete();
            $table->uuid('classroom_id')->constrained()->cascadeOnDelete();
            $table->uuid('academic_year_id')->constrained();
            $table->enum('reason', ['akademik', 'non_akademik', 'lainnya']);
            $table->text('notes')->nullable();
            $table->timestamp('retained_at');
            $table->uuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
            $table->index(['academic_year_id']);
        });

        // Student Graduations
        Schema::create('student_graduations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->constrained()->cascadeOnDelete();
            $table->uuid('from_classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->date('graduation_date');
            $table->string('diploma_number', 50)->unique()->nullable();
            $table->string('certificate_path')->nullable();
            $table->decimal('final_gpa', 5, 2)->nullable();
            $table->integer('rank')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('graduated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_graduations');
        Schema::dropIfExists('student_retentions');
        Schema::dropIfExists('student_transfers');
        Schema::dropIfExists('student_promotions');
    }
};
