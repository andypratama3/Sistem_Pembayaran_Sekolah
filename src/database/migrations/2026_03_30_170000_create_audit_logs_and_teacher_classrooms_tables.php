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
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 50);

                // New audit shape
                $table->string('model_type')->nullable();
                $table->string('model_id')->nullable();
                $table->text('description')->nullable();

                // Legacy audit shape kept for compatibility
                $table->string('model')->nullable();
                $table->string('record_id')->nullable();

                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['model_type', 'model_id']);
                $table->index(['model', 'record_id']);
                $table->index('action');
            });
        }

        if (! Schema::hasTable('teacher_classrooms')) {
            Schema::create('teacher_classrooms', function (Blueprint $table) {
                $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->foreignUuid('classroom_id')->constrained('classrooms')->cascadeOnDelete();
                $table->primary(['teacher_id', 'classroom_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_classrooms');
        Schema::dropIfExists('audit_logs');
    }
};
