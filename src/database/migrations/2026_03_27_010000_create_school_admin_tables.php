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
        // staff_positions moved to HR migration to ensure correct creation order

        Schema::create('education_staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('position');
            $table->string('photo');
            $table->string('slug')->unique();
            $table->foreignUuid('staff_position_id')->constrained('staff_positions')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('report_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file');
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('batch', 10)->nullable();
            $table->year('year');
            $table->enum('period', ['ganjil', 'genap', 'tengah'])->default('ganjil');
            $table->longText('notes')->nullable();
            $table->softDeletes()->index();
            $table->timestamps();

            $table->unique(['student_id', 'classroom_id', 'year', 'period'], 'rc_unique_constraint');
            // Ensure lookup index on student_id exists (previously added by a separate migration)
            $table->index('student_id', 'report_cards_student_id_index');
        });

        Schema::create('student_report_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file')->nullable();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->softDeletes()->index();
            $table->timestamps();
            // Add direct index for student lookups (was previously added by a separate migration)
            $table->index('student_id', 'student_report_cards_student_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_report_cards');
        Schema::dropIfExists('report_cards');
        Schema::dropIfExists('education_staff');
    }
};
