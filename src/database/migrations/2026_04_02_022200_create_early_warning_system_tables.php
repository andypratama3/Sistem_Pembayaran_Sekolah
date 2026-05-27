<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_risk_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('academic_year_id')->nullable();
            $table->string('academic_year')->comment('Year string like 2024/2025');
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->decimal('risk_score', 5, 2)->default(0);
            $table->decimal('grade_trend', 5, 2)->nullable()->comment('Grade movement percentage');
            $table->decimal('attendance_rate', 5, 2)->nullable();
            $table->decimal('behavior_score', 5, 2)->nullable();
            $table->decimal('engagement_factor', 5, 2)->nullable();
            $table->json('risk_factors')->nullable()->comment('Array of identified risk factors');
            $table->json('recommended_actions')->nullable()->comment('Array of recommended interventions');
            $table->timestamp('assessment_date')->useCurrent();
            $table->timestamp('next_review_date')->nullable();
            $table->uuid('assessed_by_user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('assessed_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('academic_year');
            $table->index('risk_level');
            $table->index('assessment_date');
        });

        Schema::create('student_risk_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_risk_assessment_id');
            $table->enum('alert_type', ['grade_drop', 'attendance', 'behavior', 'engagement'])->default('grade_drop');
            $table->enum('severity', ['warning', 'critical'])->default('warning');
            $table->text('message');
            $table->text('action_taken')->nullable();
            $table->timestamp('notified_to_teacher_at')->nullable();
            $table->timestamp('notified_to_parent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->uuid('acknowledged_by_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_risk_assessment_id')->references('id')->on('student_risk_assessments')->onDelete('cascade');
            $table->foreign('acknowledged_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('alert_type');
            $table->index('severity');
            $table->index('acknowledged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_risk_alerts');
        Schema::dropIfExists('student_risk_assessments');
    }
};
