<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template Categories (Lookup table)
        Schema::create('template_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique(); // grade, attendance, report, certificate, custom
            $table->string('label');
            $table->string('icon_key')->nullable();
            $table->timestamps();
        });

        // Main Templates
        Schema::create('templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->uuid('created_by');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('canvas_layout')->nullable(); // Fabric.js JSON for visual layout
            $table->boolean('is_published')->default(false);
            $table->boolean('is_global')->default(false); // Global templates available to all teachers
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('template_categories')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('category_id');
            $table->index('created_by');
        });

        // Template Field Definitions
        Schema::create('template_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->string('field_key'); // final_score, student_name, attendance_rate
            $table->string('label');
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'checkbox', 'formula']);
            $table->json('options')->nullable(); // For select type: {"Present":"Hadir","Absent":"Alpa"}
            $table->text('formula')->nullable(); // For formula type: (midterm * 0.4) + (final_exam * 0.6)
            $table->string('placeholder')->nullable();
            $table->boolean('required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->unique(['template_id', 'field_key']);
            $table->index('template_id');
            $table->index('sort_order');
        });

        // Archive existing template data for migration
        // This table helps migrate from ReportTemplate/DistributionTemplate
        Schema::create('template_migration_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_table'); // ReportTemplate, DistributionTemplate
            $table->uuid('source_id');
            $table->uuid('new_template_id')->nullable();
            $table->string('status'); // pending, migrated, skipped
            $table->json('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_migration_log');
        Schema::dropIfExists('template_fields');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('template_categories');
    }
};
