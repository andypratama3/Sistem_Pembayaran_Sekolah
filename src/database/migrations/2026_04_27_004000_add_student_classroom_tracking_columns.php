<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_classrooms', function (Blueprint $table) {
            $table->uuid('academic_year_id')->nullable()->after('classroom_id');
            $table->enum('status', ['active', 'transferred', 'graduated', 'retained', 'dropped'])->default('active')->after('classroom_type');
            $table->timestamp('enrolled_at')->nullable()->after('status');
            $table->timestamp('left_at')->nullable()->after('enrolled_at');
            $table->text('notes')->nullable()->after('left_at');
            $table->uuid('enrolled_by')->nullable()->after('notes');
        });

        Schema::table('student_classrooms', function (Blueprint $table) {
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->index(['student_id', 'status']);
            $table->index(['classroom_id', 'status']);
            $table->index(['academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::table('student_classrooms', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn([
                'academic_year_id',
                'status',
                'enrolled_at',
                'left_at',
                'notes',
                'enrolled_by',
            ]);
        });
    }
};
