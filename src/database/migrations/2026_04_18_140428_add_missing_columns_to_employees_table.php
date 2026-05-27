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
            if (! Schema::hasColumn('employees', 'photo')) {
                $table->string('photo')->nullable()->after('status');
            }
            if (! Schema::hasColumn('employees', 'work_shift_id')) {
                $table->foreignUuid('work_shift_id')->nullable()->constrained('work_shifts')->nullOnDelete()->after('staff_position_id');
            }
            if (! Schema::hasColumn('employees', 'base_salary')) {
                $table->decimal('base_salary', 15, 2)->default(0)->after('photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['work_shift_id']);
            $table->dropColumn(['photo', 'work_shift_id', 'base_salary']);
        });
    }
};
