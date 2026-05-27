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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->index();
            $table->uuid('sender_id')->nullable()->index(); // NULL if from WhatsApp parent, reference to users if from admin
            $table->enum('sender_type', ['parent', 'admin'])->default('parent')->index();
            $table->enum('message_type', ['text', 'image', 'document', 'media', 'template'])->default('text');
            $table->longText('content');
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable(); // image, video, document, etc.
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent')->index();
            $table->string('whatsapp_message_id')->nullable()->unique(); // for tracking on WhatsApp side
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('conversation_id')->references('id')->on('whatsapp_conversations')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
