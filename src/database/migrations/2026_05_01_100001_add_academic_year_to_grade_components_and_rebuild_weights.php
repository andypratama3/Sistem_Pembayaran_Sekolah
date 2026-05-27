<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_components', function (Blueprint $table) {
            $table->foreignUuid('academic_year_id')->nullable()->after('subject_id')->constrained('academic_years')->nullOnDelete();
        });

        Schema::dropIfExists('grade_weights');

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
    }

    public function down(): void
    {
        Schema::table('grade_components', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });

        Schema::dropIfExists('grade_component_weights');

        Schema::create('grade_weights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('weight', 5, 2);
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
