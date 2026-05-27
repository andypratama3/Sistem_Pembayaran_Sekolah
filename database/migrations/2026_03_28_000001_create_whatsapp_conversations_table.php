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
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone_number', 20)->unique()->index();
            $table->string('profile_name')->nullable();
            $table->uuid('student_id')->nullable()->index();
            $table->uuid('assigned_admin_id')->nullable()->index();
            $table->enum('status', ['active', 'closed', 'archived'])->default('active')->index();
            $table->text('notes')->nullable();
            $table->integer('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            $table->foreign('assigned_admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};
