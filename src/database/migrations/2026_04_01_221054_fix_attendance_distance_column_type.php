<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, reset out-of-range values to NULL
        DB::table('employee_attendances')
            ->where('check_in_distance', '>', 999999.99)
            ->orWhere('check_in_distance', '<', -999999.99)
            ->update(['check_in_distance' => null]);

        DB::table('employee_attendances')
            ->where('check_out_distance', '>', 999999.99)
            ->orWhere('check_out_distance', '<', -999999.99)
            ->update(['check_out_distance' => null]);

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->decimal('check_in_distance', 10, 2)->nullable()->change();
            $table->decimal('check_out_distance', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->decimal('check_in_distance', 8, 2)->nullable()->change();
            $table->decimal('check_out_distance', 8, 2)->nullable()->change();
        });
    }
};
