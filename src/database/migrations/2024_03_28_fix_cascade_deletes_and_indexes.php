<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip cascade delete modifications if tables don't exist
        // They should exist from initial migrations

        // Add useful indexes for performance
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (! Schema::hasIndex('students', 'email_status')) {
                    $table->index(['email', 'status']);
                }
                if (! Schema::hasIndex('students', 'nisn')) {
                    $table->index(['nisn']);
                }
                if (! Schema::hasIndex('students', 'created_at')) {
                    $table->index('created_at');
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasIndex('payments', 'status_created_at')) {
                    $table->index(['status', 'created_at']);
                }
                if (! Schema::hasIndex('payments', 'student_id_status')) {
                    $table->index(['student_id', 'status']);
                }
            });
        }

        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                if (! Schema::hasIndex('grades', 'student_id_subject_id')) {
                    $table->index(['student_id', 'subject_id']);
                }
            });
        }

        if (Schema::hasTable('employee_attendances')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                if (! Schema::hasIndex('employee_attendances', 'employee_created_at')) {
                    $table->index(['employee_id', 'created_at']);
                }
            });
        }

        if (Schema::hasTable('student_attendances')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                if (! Schema::hasIndex('student_attendances', 'student_id_date')) {
                    $table->index(['student_id', 'date']);
                }
                if (! Schema::hasIndex('student_attendances', 'status_created_at')) {
                    $table->index(['status', 'created_at']);
                }
            });
        }

        if (Schema::hasTable('employee_attendances')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                if (! Schema::hasIndex('employee_attendances', 'employee_id_date')) {
                    $table->index(['employee_id', 'date']);
                }
                if (! Schema::hasIndex('employee_attendances', 'status_created_at')) {
                    $table->index(['status', 'created_at']);
                }
            });
        }
    }

    public function down(): void
    {
        // Drop indexes created in up()
        // These drop methods won't fail if indexes don't exist
        try {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex(['email', 'status']);
                $table->dropIndex(['nisn']);
                $table->dropIndex('created_at');
            });
        } catch (Exception $e) {
            // Silently fail if indexes don't exist
        }

        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex(['status', 'created_at']);
                $table->dropIndex(['student_id', 'status']);
            });
        } catch (Exception $e) {
        }

        try {
            Schema::table('grades', function (Blueprint $table) {
                $table->dropIndex(['student_id', 'subject_id']);
            });
        } catch (Exception $e) {
        }

        try {
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->dropIndex(['student_id', 'date']);
                $table->dropIndex(['status', 'created_at']);
            });
        } catch (Exception $e) {
        }

        try {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->dropIndex(['employee_id', 'date']);
                $table->dropIndex(['status', 'created_at']);
            });
        } catch (Exception $e) {
        }
    }
};
