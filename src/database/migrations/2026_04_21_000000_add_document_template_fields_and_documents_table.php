<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            if (! Schema::hasColumn('templates', 'html_template')) {
                $table->longtext('html_template')->nullable()->after('canvas_layout');
            }
            if (! Schema::hasColumn('templates', 'pages_json')) {
                $table->json('pages_json')->nullable()->after('html_template');
            }
            if (! Schema::hasColumn('templates', 'variable_map')) {
                $table->json('variable_map')->nullable()->after('pages_json');
            }
            if (! Schema::hasColumn('templates', 'thumbnail_path')) {
                $table->string('thumbnail_path')->nullable()->after('variable_map');
            }
        });

        if (! Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('template_id');
                $table->uuid('student_id');
                $table->uuid('created_by');
                $table->json('data_json')->nullable();
                $table->enum('status', ['draft', 'published'])->default('draft');
                $table->string('file_path')->nullable();
                $table->uuid('bulk_batch_id')->nullable();
                $table->string('verification_code')->nullable()->default(null);
                $table->integer('render_time_ms')->nullable();
                $table->timestamps();

                $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('bulk_batch_id')->references('id')->on('batch_exports')->onDelete('set null');

                $table->index('template_id');
                $table->index('student_id');
                $table->index('bulk_batch_id');
                $table->index('status');
                $table->index('created_at');
            });
        } else {
            Schema::table('documents', function (Blueprint $table) {
                if (! Schema::hasColumn('documents', 'render_time_ms')) {
                    $table->integer('render_time_ms')->nullable()->after('verification_code');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::table('templates', function (Blueprint $table) {
            if (Schema::hasColumn('templates', 'html_template')) {
                $table->dropColumn('html_template');
            }
            if (Schema::hasColumn('templates', 'pages_json')) {
                $table->dropColumn('pages_json');
            }
            if (Schema::hasColumn('templates', 'variable_map')) {
                $table->dropColumn('variable_map');
            }
            if (Schema::hasColumn('templates', 'thumbnail_path')) {
                $table->dropColumn('thumbnail_path');
            }
        });
    }
};
