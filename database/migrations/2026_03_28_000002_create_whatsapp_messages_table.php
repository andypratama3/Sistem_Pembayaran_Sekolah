<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->index();
            $table->uuid('reply_to_message_id')->nullable()->index();
            $table->uuid('sender_id')->nullable()->index();
            $table->enum('sender_type', ['parent', 'admin'])->default('parent')->index();
            $table->enum('message_type', ['text', 'image', 'document', 'media', 'template'])->default('text');
            $table->longText('content');
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent')->index();
            $table->string('whatsapp_message_id')->nullable()->unique();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->json('reactions')->nullable()->comment('{"emoji": ["user_id1", "user_id2"]}');
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('whatsapp_conversations')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reply_to_message_id')->references('id')->on('whatsapp_messages')->nullOnDelete();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
