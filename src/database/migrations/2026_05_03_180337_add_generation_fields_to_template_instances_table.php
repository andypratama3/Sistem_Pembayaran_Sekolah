<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_instances', function (Blueprint $table) {
            $table->string('document_number')->nullable()->after('status');
            $table->tinyInteger('semester')->nullable()->after('document_number');
            $table->string('purpose')->nullable()->after('semester');
            $table->date('document_date')->nullable()->after('purpose');
            $table->uuid('approved_by')->nullable()->after('document_date');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('generated_pdf_path')->nullable()->after('approved_at');
            $table->uuid('achievement_id')->nullable()->after('generated_pdf_path');
            $table->text('notes')->nullable()->after('achievement_id');
            $table->boolean('is_printed')->default(false)->after('notes');
            $table->timestamp('printed_at')->nullable()->after('is_printed');

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('achievement_id')->references('id')->on('school_achievements')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('template_instances', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['achievement_id']);
            $table->dropColumn([
                'document_number',
                'semester',
                'purpose',
                'document_date',
                'approved_by',
                'approved_at',
                'generated_pdf_path',
                'achievement_id',
                'notes',
                'is_printed',
                'printed_at',
            ]);
        });
    }
};
