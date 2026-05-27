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
        Schema::create('provinces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('province_id', 10)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('regencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('regency_id', 10)->unique();
            $table->string('province_id', 10)->index();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('district_id', 10)->unique();
            $table->string('regency_id', 10)->index();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('district_id', 10)->index();
            $table->string('village_id', 10)->unique();
            $table->string('name');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }
};
