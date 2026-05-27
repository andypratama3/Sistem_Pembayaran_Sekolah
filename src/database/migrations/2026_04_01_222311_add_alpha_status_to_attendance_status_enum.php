<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Database-agnostic approach
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employee_attendances MODIFY check_in_status ENUM('tepat_waktu', 'terlambat', 'alpha', 'pending') NULL");
            DB::statement("ALTER TABLE employee_attendances MODIFY check_out_status ENUM('tepat_waktu', 'alpha', 'auto_checkout') NULL");
        } else {
            // SQLite: Column changes are simple - SQLite doesn't enforce enum constraints
            // The values are already stored, we just need to acknowledge the new enum values
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employee_attendances MODIFY check_in_status ENUM('tepat_waktu', 'terlambat') NULL");
            DB::statement("ALTER TABLE employee_attendances MODIFY check_out_status ENUM('tepat_waktu') NULL");
        }
    }
};
