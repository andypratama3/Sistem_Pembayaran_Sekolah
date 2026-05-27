<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to admissions table
        Schema::table('admissions', function (Blueprint $table) {
            // Only add if not already present
            if (! Schema::hasColumn('admissions', 'user_id')) {
                $table->foreignUuid('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('admissions', 'academic_year_id')) {
                $table->foreignUuid('academic_year_id')->nullable()->after('user_id')->constrained('academic_years')->nullOnDelete();
            }
            if (! Schema::hasColumn('admissions', 'classroom_id')) {
                $table->foreignUuid('classroom_id')->nullable()->after('academic_year_id')->constrained('classrooms')->nullOnDelete();
            }
            if (! Schema::hasColumn('admissions', 'decided_by')) {
                $table->foreignUuid('decided_by')->nullable()->after('status')->constrained('users', 'id')->nullOnDelete();
            }
            if (! Schema::hasColumn('admissions', 'decision_reason')) {
                $table->text('decision_reason')->nullable()->after('decided_by');
            }
            if (! Schema::hasColumn('admissions', 'notes')) {
                $table->text('notes')->nullable()->after('decision_reason');
            }
            if (! Schema::hasColumn('admissions', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('admissions', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            }
            if (! Schema::hasColumn('admissions', 'decided_at')) {
                $table->timestamp('decided_at')->nullable()->after('reviewed_at');
            }
            if (! Schema::hasColumn('admissions', 'enrolled_at')) {
                $table->timestamp('enrolled_at')->nullable()->after('decided_at');
            }
        });

        // Create admission decisions table for audit trail
        Schema::create('admission_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->foreignUuid('decided_by')->constrained('users', 'id')->restrictOnDelete();
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'enrolled', 'cancelled']);
            $table->text('reason')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('admission_id');
            $table->index('decided_by');
        });

        // Create admission documents table for tracking submitted files
        Schema::create('admission_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->string('status', 50)->default('submitted');
            $table->text('verification_notes')->nullable();
            $table->timestamps();

            $table->index('admission_id');
            $table->index(['admission_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_documents');
        Schema::dropIfExists('admission_decisions');

        Schema::table('admissions', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('admissions', 'user_id')) {
                $table->dropForeignKeyIfExists(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('admissions', 'academic_year_id')) {
                $table->dropForeignKeyIfExists(['academic_year_id']);
                $table->dropColumn('academic_year_id');
            }
            if (Schema::hasColumn('admissions', 'classroom_id')) {
                $table->dropForeignKeyIfExists(['classroom_id']);
                $table->dropColumn('classroom_id');
            }
            if (Schema::hasColumn('admissions', 'decided_by')) {
                $table->dropForeignKeyIfExists(['decided_by']);
                $table->dropColumn('decided_by');
            }
            if (Schema::hasColumn('admissions', 'decision_reason')) {
                $table->dropColumn('decision_reason');
            }
            if (Schema::hasColumn('admissions', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('admissions', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
            if (Schema::hasColumn('admissions', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('admissions', 'decided_at')) {
                $table->dropColumn('decided_at');
            }
            if (Schema::hasColumn('admissions', 'enrolled_at')) {
                $table->dropColumn('enrolled_at');
            }
        });
    }
};
