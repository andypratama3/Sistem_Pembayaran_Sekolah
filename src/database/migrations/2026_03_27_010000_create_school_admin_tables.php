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
        Schema::create('staff_positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->foreignUuid('parent_position_id')->nullable()->constrained('staff_positions')->nullOnDelete();
            $table->timestamps();

            $table->index('parent_position_id');
        });

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
        });

        Schema::create('student_report_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file')->nullable();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->softDeletes()->index();
            $table->timestamps();
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
        Schema::dropIfExists('staff_positions');
    }
};
