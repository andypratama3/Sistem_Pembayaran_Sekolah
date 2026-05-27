<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->enum('document_type', [
                'report_card',
                'report_card_p5',
                'letter_active',
                'letter_graduate',
                'letter_recommendation',
                'letter_permission',
                'certificate_graduate',
                'certificate_achievement',
                'certificate_participation',
                'custom',
            ])->default('custom')->after('category_id');

            $table->string('document_number_format')->nullable()->after('document_type');
            $table->boolean('requires_approval')->default(false)->after('document_number_format');
            $table->boolean('supports_bulk')->default(true)->after('requires_approval');
            $table->tinyInteger('default_semester')->nullable()->after('supports_bulk');
            $table->text('user_instructions')->nullable()->after('default_semester');
            $table->string('preview_thumbnail')->nullable()->after('user_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn([
                'document_type',
                'document_number_format',
                'requires_approval',
                'supports_bulk',
                'default_semester',
                'user_instructions',
                'preview_thumbnail',
            ]);
        });
    }
};
