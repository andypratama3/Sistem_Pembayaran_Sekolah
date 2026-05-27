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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('article_categories', function (Blueprint $table) {
            $table->foreignUuid('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['article_id', 'category_id']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->index('article_id');
        });

        Schema::create('comment_likes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('photo');
            $table->integer('sort_order')->default(0);
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('school_achievements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->longText('description');
            $table->boolean('is_active')->default(true);
            $table->string('photo');
            $table->string('slug')->unique();
            $table->string('level', 100)->nullable();
            $table->date('date')->nullable();
            $table->string('organizer')->nullable();
            $table->string('rank', 50)->nullable();
            $table->integer('views')->default(0);
            $table->timestamps();

            $table->index('date');
        });

        Schema::create('galleries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('cover');
            $table->json('photos')->nullable();
            $table->text('link')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('heroes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('photo')->nullable();
            $table->longText('description')->nullable();
            $table->string('youtube')->nullable();
            $table->string('link')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('cooperations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('photo');
            $table->integer('sort_order')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('achievement_category_labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('achievement_categories', function (Blueprint $table) {
            $table->foreignUuid('category_label_id')->constrained('achievement_category_labels')->cascadeOnDelete();
            $table->foreignUuid('achievement_id')->constrained('school_achievements')->cascadeOnDelete();
            $table->primary(['category_label_id', 'achievement_id']);
        });

        Schema::create('gallery_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('gallery_category_pivot', function (Blueprint $table) {
            $table->foreignUuid('gallery_category_id')->constrained('gallery_categories')->cascadeOnDelete();
            $table->foreignUuid('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->primary(['gallery_category_id', 'gallery_id']);

            $table->index('gallery_id', 'gcp_gallery_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_category_pivot');
        Schema::dropIfExists('gallery_categories');
        Schema::dropIfExists('achievement_categories');
        Schema::dropIfExists('achievement_category_labels');
        Schema::dropIfExists('cooperations');
        Schema::dropIfExists('heroes');
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('school_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('comment_likes');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('article_categories');
        Schema::dropIfExists('categories');
    }
};
