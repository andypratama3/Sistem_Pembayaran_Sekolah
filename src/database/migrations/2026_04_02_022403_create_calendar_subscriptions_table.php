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
        Schema::create('calendar_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('academic_calendar_id');
            $table->enum('calendar_type', ['ical', 'webhook'])->default('ical');
            $table->string('webhook_url')->nullable();
            $table->string('ical_token', 255)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('academic_calendar_id')->references('id')->on('academic_calendars')->cascadeOnDelete();
            $table->unique(['user_id', 'academic_calendar_id']);
            $table->index(['academic_calendar_id', 'calendar_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_subscriptions');
    }
};
