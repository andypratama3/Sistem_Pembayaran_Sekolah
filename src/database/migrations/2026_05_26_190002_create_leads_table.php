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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('school_name');
            $table->string('email');
            $table->string('whatsapp');
            $table->text('message')->nullable();
            $table->string('status')->default('new'); // new, contacted, demo_scheduled, pilot, converted, junk
            $table->string('lead_source')->default('landing_page');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
