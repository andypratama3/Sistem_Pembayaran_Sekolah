<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_instances', function (Blueprint $table) {
            $table->string('verification_code', 20)->nullable()->unique()->after('generated_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('template_instances', function (Blueprint $table) {
            $table->dropColumn('verification_code');
        });
    }
};
