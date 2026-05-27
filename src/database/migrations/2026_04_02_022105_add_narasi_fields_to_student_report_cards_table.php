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
        Schema::table('student_report_cards', function (Blueprint $table) {
            $table->longText('narasi_text')->nullable()->after('finalized_by');
            $table->timestamp('narasi_generated_at')->nullable()->after('narasi_text');
            $table->string('narasi_model')->nullable()->after('narasi_generated_at')->default('claude-3-5-sonnet');
            $table->integer('narasi_prompt_version')->default(1)->after('narasi_model');
            $table->boolean('is_narasi_approved')->default(false)->after('narasi_prompt_version');
            $table->timestamp('narasi_approved_at')->nullable()->after('is_narasi_approved');
            $table->foreignUuid('narasi_approved_by')->nullable()->after('narasi_approved_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_report_cards', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['narasi_approved_by']);
            $table->dropColumn([
                'narasi_text',
                'narasi_generated_at',
                'narasi_model',
                'narasi_prompt_version',
                'is_narasi_approved',
                'narasi_approved_at',
                'narasi_approved_by',
            ]);
        });
    }
};
