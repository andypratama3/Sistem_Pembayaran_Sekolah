<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores extracurricular participation and grades per student per academic year.
 * Used by VariableResolver to populate {{ekskul_name_*}} and {{ekskul_grade_*}}
 * variables in template documents (rapor, sertifikat, etc).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_extracurriculars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('semester', 10)->nullable();
            $table->string('grade', 20)->nullable()->comment('Predikat: A/B/C/D atau Sangat Baik/Baik/Cukup');
            $table->text('description')->nullable()->comment('Narasi/keterangan kegiatan eskul');
            $table->timestamps();

            $table->unique(['student_id', 'extracurricular_id', 'academic_year_id', 'semester'], 'student_eskul_unique');
            $table->index('student_id');
            $table->index('extracurricular_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_extracurriculars');
    }
};
