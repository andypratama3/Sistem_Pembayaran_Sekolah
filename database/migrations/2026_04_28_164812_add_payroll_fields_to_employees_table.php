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
        Schema::table('employees', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada sebelum menambah
            if (! Schema::hasColumn('employees', 'join_date')) {
                $table->date('join_date')->nullable()->after('work_shift_id');
            }
            if (! Schema::hasColumn('employees', 'education_level')) {
                $table->enum('education_level', [
                    'SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3',
                ])->nullable()->after('join_date');
            }
            if (! Schema::hasColumn('employees', 'dependent_count')) {
                $table->unsignedTinyInteger('dependent_count')->default(0)->after('education_level');
            }
            if (! Schema::hasColumn('employees', 'salary_grade_id')) {
                $table->char('salary_grade_id', 36)->nullable()->after('dependent_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['join_date', 'education_level', 'dependent_count', 'salary_grade_id']);
        });
    }
};
