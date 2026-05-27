<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_attendances', function ($table) {
            // Change column type using schema builder (database-agnostic)
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE employee_attendances MODIFY device_id VARCHAR(255) NULL');
            } else {
                // SQLite doesn't need explicit type changes, columns are flexible
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendances', function ($table) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE employee_attendances MODIFY device_id VARCHAR(64) NULL');
            }
        });
    }
};
