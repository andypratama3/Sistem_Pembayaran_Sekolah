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
        Schema::create('system_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('grup')->index(); // Group: attendance, payroll, system, etc.
            $table->string('key')->unique(); // Config key
            $table->string('label'); // Human-readable label
            $table->text('nilai')->nullable(); // Config value
            $table->string('tipe')->default('text'); // Type: text, number, boolean, date, json
            $table->text('deskripsi')->nullable(); // Description
            $table->boolean('is_editable')->default(true); // Can user edit this?
            $table->integer('urutan')->default(0); // Sort order
            $table->timestamps();

            $table->index(['grup', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configs');
    }
};
