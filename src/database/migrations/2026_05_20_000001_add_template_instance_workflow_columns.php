<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds workflow audit columns to template_instances:
 *   - submitted_by / submitted_at  — who/when submitted for approval
 *   - rejected_by  / rejected_at   — who/when rejected
 *   - rejection_reason             — required when rejecting (UI enforced)
 *
 * The existing approved_by / approved_at columns are kept; this migration only
 * adds the missing pieces for a complete audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_instances', function (Blueprint $table) {
            if (! Schema::hasColumn('template_instances', 'submitted_by')) {
                $table->uuid('submitted_by')->nullable()->after('approved_at');
                $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('template_instances', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            }
            if (! Schema::hasColumn('template_instances', 'rejected_by')) {
                $table->uuid('rejected_by')->nullable()->after('submitted_at');
                $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('template_instances', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (! Schema::hasColumn('template_instances', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('template_instances', function (Blueprint $table) {
            foreach (['submitted_by', 'rejected_by'] as $col) {
                if (Schema::hasColumn('template_instances', $col)) {
                    try {
                        $table->dropForeign([$col]);
                    } catch (Throwable $e) {
                        // ignore — FK may not exist
                    }
                }
            }

            $columns = array_filter(
                ['submitted_by', 'submitted_at', 'rejected_by', 'rejected_at', 'rejection_reason'],
                fn ($c) => Schema::hasColumn('template_instances', $c),
            );

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
