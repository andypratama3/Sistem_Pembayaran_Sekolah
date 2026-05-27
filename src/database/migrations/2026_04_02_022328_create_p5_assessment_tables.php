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
        // P5 Dimensions - The 6 core Pancasila competencies
        if (! Schema::hasTable('p5_dimensions')) {
            Schema::create('p5_dimensions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name'); // Beriman, Berkebinekaan, Gotong Royong, Mandiri, Bernalar Kritis, Kreatif
                $table->string('code')->unique(); // BRM, BK, GR, MND, BRK, KRE
                $table->text('description')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index('code');
            });
        }

        // P5 Indicators - Observable behaviors per dimension
        if (! Schema::hasTable('p5_indicators')) {
            Schema::create('p5_indicators', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('p5_dimension_id')->constrained('p5_dimensions')->cascadeOnDelete();
                $table->string('name'); // Observable behavior/indicator
                $table->string('code')->unique();
                $table->integer('grade_level_min')->default(1);
                $table->integer('grade_level_max')->default(12);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['p5_dimension_id', 'grade_level_min']);
            });
        }

        // Student P5 Assessment Records
        if (! Schema::hasTable('student_p5_assessments')) {
            Schema::create('student_p5_assessments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
                $table->string('academic_year'); // e.g., "2024/2025"
                $table->string('assessment_period'); // e.g., "ganjil" or "genap"
                $table->foreignUuid('assessor_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('assessed_at')->nullable();
                $table->longText('notes')->nullable();
                $table->softDeletes()->index();
                $table->timestamps();

                $table->index(['student_id', 'academic_year']);
                $table->index(['classroom_id', 'assessment_period']);
                $table->unique(['student_id', 'classroom_id', 'academic_year', 'assessment_period'], 'uniq_p5_assessment_period');
            });
        }

        // Individual dimension scores
        if (! Schema::hasTable('student_p5_scores')) {
            Schema::create('student_p5_scores', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('student_p5_assessment_id')->constrained('student_p5_assessments')->cascadeOnDelete();
                $table->foreignUuid('p5_dimension_id')->constrained('p5_dimensions')->cascadeOnDelete();
                $table->integer('score'); // 1-4 scale
                $table->longText('evidence')->nullable(); // Observations/notes
                $table->timestamps();

                $table->index(['student_p5_assessment_id', 'p5_dimension_id']);
                $table->unique(['student_p5_assessment_id', 'p5_dimension_id'], 'uniq_p5_score_dim');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_p5_scores');
        Schema::dropIfExists('student_p5_assessments');
        Schema::dropIfExists('p5_indicators');
        Schema::dropIfExists('p5_dimensions');
    }
};
