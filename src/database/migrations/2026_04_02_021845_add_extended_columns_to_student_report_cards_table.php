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
            $table->foreignUuid('report_card_template_id')->nullable()->after('teacher_id')->constrained('report_templates')->nullOnDelete();
            $table->enum('status', ['draft', 'completed', 'finalized'])->default('draft')->after('report_card_template_id')->index();
            $table->timestamp('finalized_at')->nullable()->after('status');
            $table->foreignUuid('finalized_by')->nullable()->after('finalized_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_report_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('finalized_by');
            $table->dropColumn(['report_card_template_id', 'status', 'finalized_at']);
        });
    }
};
