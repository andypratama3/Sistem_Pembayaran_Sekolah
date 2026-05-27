<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template Instance (filled document)
        Schema::create('template_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->uuid('student_id');
            $table->uuid('period_id')->nullable(); // Academic period (semester, etc)
            $table->uuid('subject_id')->nullable(); // For grade reports
            $table->uuid('filled_by'); // Teacher or admin who filled it
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('academic_years')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
            $table->foreign('filled_by')->references('id')->on('users')->onDelete('cascade');

            $table->index('template_id');
            $table->index('student_id');
            $table->index('period_id');
            $table->index('status');
            $table->index(['template_id', 'student_id', 'period_id']);
        });

        // Field Values (actual data for instance)
        Schema::create('field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id');
            $table->uuid('field_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('instance_id')->references('id')->on('template_instances')->onDelete('cascade');
            $table->foreign('field_id')->references('id')->on('template_fields')->onDelete('cascade');
            $table->unique(['instance_id', 'field_id']);
            $table->index('instance_id');
        });

        // Exports (audit trail + file storage)
        Schema::create('exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id');
            $table->uuid('exported_by');
            $table->string('file_path'); // S3 URI or local path
            $table->enum('format', ['pdf', 'xlsx']);
            $table->string('original_filename')->nullable();
            $table->integer('file_size')->nullable(); // Bytes
            $table->json('export_metadata')->nullable(); // page_count, sheet_count, etc
            $table->timestamps();

            $table->foreign('instance_id')->references('id')->on('template_instances')->onDelete('cascade');
            $table->foreign('exported_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('instance_id');
            $table->index('exported_by');
            $table->index('created_at');
        });

        // Batch Export Log (for class-level exports)
        Schema::create('batch_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->uuid('period_id')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->uuid('exported_by');
            $table->string('batch_name'); // "Grade Report - Grade 10A - Semester 1 2025"
            $table->integer('total_instances');
            $table->integer('successful_exports')->default(0);
            $table->integer('failed_exports')->default(0);
            $table->string('status'); // pending, processing, completed, failed
            $table->json('error_log')->nullable();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('academic_years')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
            $table->foreign('exported_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('status');
            $table->index('created_at');
        });

        // Batch Export Items (individual files in batch)
        Schema::create('batch_export_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('batch_id');
            $table->uuid('export_id')->nullable(); // Links to exports table if successful
            $table->uuid('instance_id');
            $table->string('status'); // pending, completed, failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('batch_exports')->onDelete('cascade');
            $table->foreign('export_id')->references('id')->on('exports')->onDelete('set null');
            $table->foreign('instance_id')->references('id')->on('template_instances')->onDelete('cascade');
            $table->index('batch_id');
            $table->index('instance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_export_items');
        Schema::dropIfExists('batch_exports');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('field_values');
        Schema::dropIfExists('template_instances');
    }
};
