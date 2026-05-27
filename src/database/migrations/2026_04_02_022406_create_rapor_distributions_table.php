<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapor_distributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_report_card_id');
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->uuid('created_by');

            $table->enum('distribution_method', ['whatsapp', 'email', 'print'])->default('whatsapp');
            $table->enum('status', ['pending', 'processing', 'sent', 'delivered', 'read', 'failed'])->default('pending');

            $table->string('recipient_phone')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('message_id')->nullable()->comment('WhatsApp/Email message ID from provider');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->text('error_message')->nullable();

            $table->json('metadata')->nullable()->comment('Store additional data like template variables');

            $table->timestamps();

            $table->foreign('student_report_card_id')->references('id')->on('student_report_cards')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['status', 'distribution_method']);
            $table->index(['student_report_card_id', 'distribution_method'], 'rapor_dist_student_report_method_idx');
            $table->index(['created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapor_distributions');
    }
};
