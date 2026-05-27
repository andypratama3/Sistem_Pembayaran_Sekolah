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
        Schema::table('report_templates', function (Blueprint $table) {
            $table->string('curriculum_type', 50)->nullable()->after('language')->index();
            $table->json('config')->nullable()->after('placeholders')->comment('JSON configuration: show_rank, show_attendance_summary, grade_format, etc.');
            $table->string('blade_template', 255)->nullable()->after('config')->comment('Path to blade template file');
            $table->string('logo_path', 255)->nullable()->after('blade_template')->comment('Path to school logo');
            $table->string('header_color', 20)->nullable()->after('logo_path')->comment('Header color in hex format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_templates', function (Blueprint $table) {
            $table->dropColumn(['curriculum_type', 'config', 'blade_template', 'logo_path', 'header_color']);
        });
    }
};
