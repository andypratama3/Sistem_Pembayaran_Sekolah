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
        Schema::create('ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->tinyInteger('rating')->unsigned()->comment('1-5 stars');
            $table->string('ip_address', 45);
            $table->timestamps();

            $table->index('ip_address');
        });

        Schema::create('visitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->index('date');
            $table->index(['ip_address', 'date']);
        });

        Schema::create('error_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('error');
            $table->string('status_code', 10);
            $table->timestamps();

            $table->index('status_code');
        });

        Schema::create('charge_not_found', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_type', 50)->nullable();
            $table->dateTime('transaction_time');
            $table->string('transaction_status', 50);
            $table->string('transaction_id');
            $table->string('status_message');
            $table->string('status_code', 10);
            $table->string('signature_key');
            $table->dateTime('settlement_time')->nullable();
            $table->string('payment_type', 50);
            $table->string('order_id');
            $table->json('metadata')->nullable();
            $table->string('merchant_id', 100);
            $table->string('issuer', 100)->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->string('fraud_status', 20);
            $table->dateTime('expiry_time')->nullable();
            $table->string('currency', 10);
            $table->string('acquirer', 100)->nullable();
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('whatsapp_incoming_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('message_id')->unique();
            $table->string('phone', 20);
            $table->string('type', 50);
            $table->json('content')->nullable();
            $table->string('profile_name')->nullable();
            $table->enum('status', ['received', 'processed', 'replied'])->default('received');
            $table->timestamps();

            $table->index(['phone', 'created_at']);
        });

        Schema::create('whatsapp_message_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('message_id');
            $table->string('status', 45);
            $table->string('recipient', 20)->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->json('errors')->nullable();
            $table->timestamps();

            $table->index('message_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_statuses');
        Schema::dropIfExists('whatsapp_incoming_messages');
        Schema::dropIfExists('charge_not_found');
        Schema::dropIfExists('error_log');
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('ratings');
    }
};
