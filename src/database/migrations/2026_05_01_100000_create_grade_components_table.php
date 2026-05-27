<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->string('component_type', 50);
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(
                ['student_id', 'classroom_id', 'subject_id', 'semester', 'component_type'],
                'grade_components_unique'
            );
            $table->index(['classroom_id', 'semester'], 'gc_classroom_semester_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_components');
    }
};
