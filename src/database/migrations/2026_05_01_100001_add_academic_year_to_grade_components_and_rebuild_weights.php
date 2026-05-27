<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_components', function (Blueprint $table) {
            $table->foreignUuid('academic_year_id')->nullable()->after('subject_id')->constrained('academic_years')->nullOnDelete();
        });

        Schema::dropIfExists('grade_weights');
    }

    public function down(): void
    {
        Schema::table('grade_components', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
