<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns to whatsapp_conversations for admin routing
        Schema::table('whatsapp_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_conversations', 'admin_assigned_at')) {
                $table->timestamp('admin_assigned_at')->nullable()->after('assigned_admin_id');
            }
            if (! Schema::hasColumn('whatsapp_conversations', 'work_hours_connected')) {
                $table->boolean('work_hours_connected')->default(false)->after('admin_assigned_at');
            }
            if (! Schema::hasColumn('whatsapp_conversations', 'is_priority')) {
                $table->boolean('is_priority')->default(false)->after('work_hours_connected');
            }
            if (! Schema::hasColumn('whatsapp_conversations', 'outside_hours_message')) {
                $table->text('outside_hours_message')->nullable()->after('is_priority');
            }
        });

        // School work hours configuration
        Schema::create('school_work_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('day_of_week')->comment('0=Sunday, 1=Monday, ... 6=Saturday');
            $table->time('work_start')->comment('Start time HH:MM:SS');
            $table->time('work_end')->comment('End time HH:MM:SS');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('day_of_week');
        });

        // Admin availability tracking
        Schema::create('admin_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('day_of_week')->comment('0=Sunday, 1=Monday, ... 6=Saturday');
            $table->time('available_start')->nullable();
            $table->time('available_end')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'day_of_week']);
        });

        // WhatsApp admin activity tracking
        Schema::create('whatsapp_admin_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('whatsapp_conversations')->cascadeOnDelete();
            $table->foreignUuid('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action')->comment('assigned, replied, escalated, transferred, auto_reply');
            $table->text('details')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->index('conversation_id');
            $table->index('admin_id');
            $table->index('assigned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_admin_activities');
        Schema::dropIfExists('admin_availabilities');
        Schema::dropIfExists('school_work_hours');

        Schema::table('whatsapp_conversations', function (Blueprint $table) {
            $table->dropColumn([
                'admin_assigned_at',
                'work_hours_connected',
                'is_priority',
                'outside_hours_message',
            ]);
        });
    }
};
