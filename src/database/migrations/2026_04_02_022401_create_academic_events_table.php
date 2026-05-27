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
        Schema::create('academic_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_calendar_id');
            $table->string('title', 255);
            $table->enum('event_type', ['holiday', 'exam', 'event', 'deadline', 'celebration'])->default('event')->index();
            $table->date('start_date')->index();
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->string('location', 255)->nullable();
            $table->uuid('organizer_id')->nullable();
            $table->enum('visibility', ['private', 'staff', 'parents', 'public'])->default('public')->index();
            $table->string('color', 7)->default('#3B82F6');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('academic_calendar_id')->references('id')->on('academic_calendars')->cascadeOnDelete();
            $table->foreign('organizer_id')->references('id')->on('users')->nullOnDelete();
            $table->index('academic_calendar_id');
            $table->index('organizer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
