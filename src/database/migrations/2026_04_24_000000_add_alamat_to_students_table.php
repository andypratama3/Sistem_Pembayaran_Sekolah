<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'address')) {
                $table->text('address')->nullable()->after('parent_email');
            }
            if (! Schema::hasColumn('students', 'alamat')) {
                $table->text('alamat')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'address']);
        });
    }
};
