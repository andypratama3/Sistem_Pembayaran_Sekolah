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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title')->nullable();
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });

        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('photo');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('whatsapp_request_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone', 20);
            $table->string('nisn', 10)->nullable();
            $table->char('student_id', 36)->nullable();
            $table->string('status', 30);
            $table->string('ip_address', 45)->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamps();

            $table->index(['phone', 'requested_at'], 'whatsapp_rl_phone_time_index');
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });

        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone', 20)->unique();
            $table->string('state', 30)->default('new');
            $table->string('nisn', 10)->nullable();
            $table->char('student_id', 36)->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
        Schema::dropIfExists('whatsapp_request_logs');
        Schema::dropIfExists('extracurriculars');
        Schema::dropIfExists('notifications');
    }
};
