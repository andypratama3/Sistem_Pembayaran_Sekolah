<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('employee_attendances', 'attendance_status')) {
                $table->renameColumn('attendance_status', 'status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('employee_attendances', 'status')) {
                $table->renameColumn('status', 'attendance_status');
            }
        });
    }
};
