<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table
        if (config('database.default') === 'sqlite') {
            // Get existing data
            $events = DB::table('academic_events')->get();

            // Drop foreign keys and table
            Schema::dropIfExists('academic_events');

            // Recreate with new visibility options
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
                $table->string('visibility', 20)->default('public')->index();
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

            // Re-insert data
            foreach ($events as $event) {
                DB::table('academic_events')->insert((array) $event);
            }
        } else {
            DB::statement("ALTER TABLE academic_events MODIFY COLUMN visibility VARCHAR(20) DEFAULT 'public'");
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            $events = DB::table('academic_events')->get();

            Schema::dropIfExists('academic_events');

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

            foreach ($events as $event) {
                DB::table('academic_events')->insert((array) $event);
            }
        } else {
            DB::statement("ALTER TABLE academic_events MODIFY COLUMN visibility ENUM('private', 'staff', 'parents', 'public') DEFAULT 'public'");
        }
    }
};
