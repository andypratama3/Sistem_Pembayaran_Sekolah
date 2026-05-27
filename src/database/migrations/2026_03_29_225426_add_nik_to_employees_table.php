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
            $table->string('nik', 20)->nullable()->unique()->after('nip');
            $table->foreignUuid('staff_position_id')->nullable()->constrained('staff_positions')->nullOnDelete()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['staff_position_id']);
            $table->dropColumn(['nik', 'staff_position_id']);
        });
    }
};
