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
        Schema::create('whatsapp_message_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique()->index();
            $table->string('category', 50)->index(); // greeting, payment_reminder, attendance_alert, etc.
            $table->text('template_text');
            $table->integer('response_time_seconds')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->uuid('created_by')->index();
            $table->timestamps();

            // Foreign key
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_templates');
    }
};
