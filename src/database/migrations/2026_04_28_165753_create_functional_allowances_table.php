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
        Schema::create('functional_allowances', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('name');          // "Wali Kelas", "Kepala Lab", "Pembina OSIS"
            $table->string('code', 30)->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('functional_allowances');
    }
};
