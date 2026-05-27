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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('classroom_type', 100);
            $table->date('date');
            $table->enum('status', ['hadir', 'izin', 'pulang', 'sakit', 'alpa', 'present', 'absent', 'late', 'excused'])->default('hadir');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'classroom_id', 'date'], 'sa_student_classroom_date_unique');
            $table->index(['classroom_id', 'date'], 'sa_classroom_date_index');
            $table->index(['student_id', 'date'], 'sa_student_date_index');
        });

        Schema::create('grade_weights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('weight', 5, 2);
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'classroom_id', 'subject_id', 'semester'], 'grades_unique_constraint');
            $table->index(['classroom_id', 'semester'], 'grades_classroom_semester_index');
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('academic_year', 50);
            $table->string('file')->nullable();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('classroom_type', 100);
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('schedule_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->string('day', 20);
            $table->time('time_start');
            $table->time('time_end');
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('color', 10)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['schedule_id', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_details');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('grade_weights');
        Schema::dropIfExists('student_attendances');
    }
};
