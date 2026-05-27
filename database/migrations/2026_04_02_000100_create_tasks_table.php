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
        Schema::create('tasks', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();

            // Core Task Data
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('parent_id')->nullable();

            // Status Tracking
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'blocked',
                'archived',
            ])->default('pending')->index();

            // Progress & Completion
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->date('due_date')->nullable();

            // Category/Feature Grouping
            $table->string('category');
            $table->string('feature_name')->nullable();

            // Priority & Sequencing
            $table->unsignedTinyInteger('priority')->default(2);
            $table->unsignedInteger('sequence_order')->default(0);

            // Assignments & Tracking
            $table->uuid('assigned_to')->nullable();
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            // File Attachments
            $table->json('attachments')->nullable();

            // Metadata
            $table->integer('subtasks_count')->default(0);
            $table->integer('completed_subtasks_count')->default(0);
            $table->text('notes')->nullable();
            $table->string('link_to_issue')->nullable();

            // Soft Deletes & Timestamps
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->foreign('parent_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['category', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['parent_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('created_at');
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('depends_on_task_id');
            $table->string('dependency_type');
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->foreign('depends_on_task_id')->references('id')->on('tasks')->cascadeOnDelete();

            $table->unique(['task_id', 'depends_on_task_id']);
            $table->index(['task_id', 'dependency_type']);
        });

        Schema::create('task_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('user_id');
            $table->text('comment');
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['task_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
    }
};
