<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_report_cards', function (Blueprint $table) {
            $table->timestamp('distributed_at')->nullable()->after('finalized_at');
            $table->enum('distribution_status', ['not_distributed', 'pending', 'sent', 'failed'])->default('not_distributed')->after('finalized_at');
        });
    }

    public function down(): void
    {
        Schema::table('student_report_cards', function (Blueprint $table) {
            $table->dropColumn(['distributed_at', 'distribution_status']);
        });
    }
};
