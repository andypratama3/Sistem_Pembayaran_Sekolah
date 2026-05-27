<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->index('status');
            $table->index('author_id');
            $table->index(['status', 'created_at']);
            $table->string('slug')->nullable()->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('school_achievements', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('level');
            $table->index('organizer');
            $table->string('slug')->nullable()->change();
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('heroes', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('cooperations', function (Blueprint $table) {
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['author_id']);
            $table->dropIndex(['status', 'created_at']);
            $table->string('slug')->nullable(false)->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('school_achievements', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['level']);
            $table->dropIndex(['organizer']);
            $table->string('slug')->nullable(false)->change();
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('heroes', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('cooperations', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
        });
    }
};
