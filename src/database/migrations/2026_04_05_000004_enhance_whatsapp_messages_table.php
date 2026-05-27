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
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            // Reply Threading
            $table->uuid('reply_to_message_id')->nullable()->after('conversation_id');
            $table->foreign('reply_to_message_id')
                ->references('id')
                ->on('whatsapp_messages')
                ->onDelete('set null');

            // Message Editing & Soft Delete
            $table->timestamp('edited_at')->nullable()->after('updated_at');
            $table->boolean('is_deleted')->default(false)->after('edited_at');

            // Message Reactions (emoji)
            $table->json('reactions')->nullable()->comment('{"emoji": ["user_id1", "user_id2"]}')->after('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropForeign(['reply_to_message_id']);
            $table->dropColumn('reply_to_message_id');
            $table->dropColumn('edited_at');
            $table->dropColumn('is_deleted');
            $table->dropColumn('reactions');
        });
    }
};
