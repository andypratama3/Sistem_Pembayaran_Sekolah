<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->enum('template_type', ['whatsapp', 'email'])->default('whatsapp');
            $table->text('subject')->nullable()->comment('For email templates');
            $table->longText('content')->comment('Template content with variables like {{student_name}}');
            $table->json('variables')->nullable()->comment('Available variables with descriptions');
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['template_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_templates');
    }
};
