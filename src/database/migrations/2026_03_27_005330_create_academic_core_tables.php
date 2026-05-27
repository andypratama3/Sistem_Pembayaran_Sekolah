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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('classroom_type', 100);
            $table->string('slug')->unique();
            $table->softDeletes()->index();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('graduation')->nullable();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('photo')->nullable();
            $table->string('slug')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->primary(['teacher_id', 'subject_id']);
        });

        Schema::create('classroom_subjects', function (Blueprint $table) {
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->primary(['classroom_id', 'subject_id']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->string('birth_place');
            $table->date('birth_date');
            $table->string('nisn', 20)->unique();
            $table->string('religion', 50);
            $table->integer('spp')->nullable();
            $table->integer('dpp')->nullable();
            $table->integer('uniform_fee')->default(0);
            $table->string('va_number')->nullable();
            $table->string('previous_school_name')->nullable();
            $table->string('previous_school_address')->nullable();
            $table->string('entry_year', 10)->nullable();
            $table->date('entry_date')->nullable();
            $table->string('scholarship')->nullable();
            $table->string('photo', 100)->nullable();
            $table->enum('guardian_type', ['orang_tua', 'wali'])->default('orang_tua');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_education')->nullable();
            $table->string('mother_education')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_address')->nullable();
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('province_id', 10);
            $table->string('regency_id', 10);
            $table->string('district_id', 10);
            $table->string('village_id', 10);
            $table->text('street')->nullable();
            $table->string('residence_type', 50)->nullable();
            $table->string('phone', 20);
            $table->string('slug')->unique();
            $table->string('dpp_status', 50)->nullable();
            $table->string('status')->default('active');
            $table->tinyInteger('phone_verified')->default(0);
            $table->timestamp('phone_verified_at')->nullable();
            $table->softDeletes()->index();
            $table->timestamps();

            $table->string('import_id')->nullable();
            $table->index('import_id');
            $table->index('name');
            $table->index('entry_year');
        });

        Schema::create('student_classrooms', function (Blueprint $table) {
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('classroom_type', 100)->nullable();
            $table->primary(['student_id', 'classroom_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_classrooms');
        Schema::dropIfExists('students');
        Schema::dropIfExists('classroom_subjects');
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classrooms');
    }
};
