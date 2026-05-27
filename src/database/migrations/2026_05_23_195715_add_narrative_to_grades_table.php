<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds narrative/capaian kompetensi columns to grades table.
 *
 * These columns store the teacher's written assessment (narasi) for each
 * student per subject, as required by Indonesian Kurikulum Merdeka rapor format.
 *
 * - narrative_excellent: Narasi untuk capaian "sangat menguasai"
 * - narrative_good: Narasi untuk capaian "baik"
 * - predicate: Predikat huruf (A/B/C/D) derived from score
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->text('narrative')->nullable()->after('score')
                ->comment('Narasi capaian kompetensi (gabungan deskripsi guru)');
            $table->string('predicate', 5)->nullable()->after('narrative')
                ->comment('Predikat: A/B/C/D');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['narrative', 'predicate']);
        });
    }
};
