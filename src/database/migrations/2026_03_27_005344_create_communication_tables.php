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

        Schema::create('feedbacks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->longText('content');
            $table->string('photo');
            $table->string('slug')->unique();
            $table->softDeletes()->index();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->longText('content');
            $table->string('photo')->nullable();
            $table->string('slug')->unique();
            $table->foreignUuid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->softDeletes()->index();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('photo');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('facilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('photo');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('facility_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('school_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('photo');
            $table->enum('type', ['general', 'facility', 'activity'])->default('general');
            $table->timestamps();
        });

        Schema::create('url_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('url');
            $table->string('ip_address', 45);
            $table->timestamp('visited_at');
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

        Schema::create('url_visitor', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('visited_at')->useCurrent();

            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_visitor');
        Schema::dropIfExists('whatsapp_sessions');
        Schema::dropIfExists('whatsapp_request_logs');
        Schema::dropIfExists('url_visits');
        Schema::dropIfExists('school_photos');
        Schema::dropIfExists('facility_items');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('extracurriculars');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('news');
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('notifications');
    }
};
