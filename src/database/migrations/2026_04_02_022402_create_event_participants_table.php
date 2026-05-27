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
        Schema::create('event_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_event_id');
            $table->enum('participant_type', ['classroom', 'student', 'teacher', 'all'])->default('all');
            $table->uuid('participant_id')->nullable();
            $table->boolean('attendance_required')->default(false);
            $table->timestamps();

            $table->foreign('academic_event_id')->references('id')->on('academic_events')->cascadeOnDelete();
            $table->index(['academic_event_id', 'participant_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
