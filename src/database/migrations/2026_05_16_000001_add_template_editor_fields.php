<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add fields to document_templates for complete editor support
        Schema::table('document_templates', function (Blueprint $table) {
            // Additional generate modes
            $table->enum('generate_mode', ['perorang', 'daftar', 'statis'])->default('perorang')->change();

            // Auto number configuration
            $table->boolean('auto_nomor_surat')->default(false)->after('generate_mode');
            $table->string('nomor_surat_format', 255)->nullable()->after('auto_nomor_surat');

            // Visibility
            $table->boolean('is_public')->default(false)->after('nomor_surat_format');
            $table->boolean('is_locked')->default(false)->after('is_public');

            // Additional content fields
            $table->string('thumbnail_path', 500)->nullable()->after('is_locked');
            $table->json('pages_json')->nullable()->after('canvas_json');
            $table->json('variable_map')->nullable()->after('pages_json');
            $table->json('table_config')->nullable()->after('variable_map');

            // Timestamps
            $table->uuid('created_by')->nullable()->after('classroom_id');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // Create document_template_categories if not exists
        if (! Schema::hasTable('document_template_categories')) {
            Schema::create('document_template_categories', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Create table_store for dynamic tables
        if (! Schema::hasTable('document_template_tables')) {
            Schema::create('document_template_tables', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('template_id');
                $table->string('table_id'); // tbl_1, tbl_2, etc.
                $table->string('sheet_name'); // Excel sheet name
                $table->enum('table_mode', ['perorang', 'daftar', 'statis'])->default('perorang');
                $table->string('linking_key', 100)->nullable(); // For perorang mode
                $table->json('col_names')->nullable();
                $table->json('col_widths')->nullable();
                $table->json('row_heights')->nullable();
                $table->string('header_color', 20)->default('#1a3c5e');
                $table->string('stripe_color', 20)->default('#eaf2ff');
                $table->string('border_color', 20)->default('#adb5bd');
                $table->timestamps();

                $table->foreign('template_id')->references('id')->on('document_templates')->cascadeOnDelete();
                $table->unique(['template_id', 'table_id'], 'dtt_template_table_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_template_tables');

        Schema::dropIfExists('document_template_categories');

        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'auto_nomor_surat',
                'nomor_surat_format',
                'is_public',
                'is_locked',
                'thumbnail_path',
                'pages_json',
                'variable_map',
                'created_by',
            ]);
            $table->enum('generate_mode', ['perorang', 'daftar'])->change();
        });
    }
};
