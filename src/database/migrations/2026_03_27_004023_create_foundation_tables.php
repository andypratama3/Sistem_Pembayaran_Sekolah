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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_slug', 100)->unique();
            $table->string('school_name', 255);
            $table->enum('plan', ['free', 'basic', 'pro', 'enterprise'])->default('free');
            $table->enum('status', ['active', 'suspended', 'cancelled', 'trial'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->integer('max_students')->default(200);
            $table->integer('max_users')->default(10);
            $table->timestamps();
        });

        Schema::create('school_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->nullable()->constrained('subscriptions')->cascadeOnDelete();
            $table->string('key', 100)->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_settings');
        Schema::dropIfExists('subscriptions');
    }
};
