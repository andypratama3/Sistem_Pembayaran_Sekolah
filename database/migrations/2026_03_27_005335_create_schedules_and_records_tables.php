<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        Schema::create('grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('narrative')->nullable()->comment('Narasi capaian kompetensi');
            $table->string('predicate', 5)->nullable()->comment('Predikat: A/B/C/D');
            $table->timestamps();

            $table->unique(['student_id', 'classroom_id', 'subject_id', 'semester'], 'grades_unique_constraint');
            $table->index(['classroom_id', 'semester'], 'grades_classroom_semester_index');
            $table->index('classroom_id', 'grades_classroom_id_index');
            $table->index(['student_id', 'subject_id'], 'grades_student_subject_index');
            $table->index('created_at', 'grades_created_at_index');
        });

        Schema::create('grade_component_weights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->string('component_label', 100);
            $table->decimal('percentage', 5, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['classroom_id', 'semester']);
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('academic_year', 50);
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('file')->nullable();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('classroom_type', 100);
            $table->string('slug')->unique();
            $table->string('status', 50)->default('draft');
            $table->timestamps();

            $table->index(['classroom_id', 'academic_year']);
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
            $table->index('schedule_id', 'schedule_details_schedule_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_details');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('grade_component_weights');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('student_attendances');
    }
};
