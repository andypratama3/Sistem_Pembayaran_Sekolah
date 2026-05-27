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
        // Add missing index on employees.name for search
        Schema::table('employees', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM employees WHERE Key_name = 'name_index'")) {
                    $table->index('name', 'name_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing index on classrooms.classroom_type for filtering
        Schema::table('classrooms', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM classrooms WHERE Key_name = 'classroom_type_index'")) {
                    $table->index('classroom_type', 'classroom_type_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing index on teachers.employee_id for relationship
        Schema::table('teachers', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM teachers WHERE Key_name = 'teacher_employee_id_index'")) {
                    $table->index('employee_id', 'teacher_employee_id_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing index on students.entry_year (exists in schema)
        Schema::table('students', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM students WHERE Key_name = 'students_entry_year_index'")) {
                    $table->index('entry_year', 'students_entry_year_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing index on students.created_at
        Schema::table('students', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM students WHERE Key_name = 'students_created_at_index'")) {
                    $table->index('created_at', 'students_created_at_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing indexes on grades
        Schema::table('grades', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM grades WHERE Key_name = 'grades_classroom_id_index'")) {
                    $table->index('classroom_id', 'grades_classroom_id_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        Schema::table('grades', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM grades WHERE Key_name = 'grades_created_at_index'")) {
                    $table->index('created_at', 'grades_created_at_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing index on payments.created_at
        Schema::table('payments', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM payments WHERE Key_name = 'payments_created_at_index'")) {
                    $table->index('created_at', 'payments_created_at_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing index on charges.created_at
        Schema::table('charges', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM charges WHERE Key_name = 'charges_created_at_index'")) {
                    $table->index('created_at', 'charges_created_at_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing indexes on report_cards
        Schema::table('report_cards', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM report_cards WHERE Key_name = 'report_cards_student_id_index'")) {
                    $table->index('student_id', 'report_cards_student_id_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing index on student_report_cards
        Schema::table('student_report_cards', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM student_report_cards WHERE Key_name = 'student_report_cards_student_id_index'")) {
                    $table->index('student_id', 'student_report_cards_student_id_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add missing index on schedule_details.schedule_id
        Schema::table('schedule_details', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM schedule_details WHERE Key_name = 'schedule_details_schedule_id_index'")) {
                    $table->index('schedule_id', 'schedule_details_schedule_id_index');
                }
            } catch (Exception $e) {
                // Column doesn't exist
            }
        });

        // Add compound indexes for performance - but only check single columns that exist
        Schema::table('grades', function (Blueprint $table) {
            try {
                if (! DB::select("SHOW INDEX FROM grades WHERE Key_name = 'grades_student_subject_index'")) {
                    $table->index(['student_id', 'subject_id'], 'grades_student_subject_index');
                }
            } catch (Exception $e) {
                // Compound index creation failed
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'employees' => ['name_index'],
            'classrooms' => ['classroom_type_index'],
            'teachers' => ['teacher_employee_id_index'],
            'students' => ['students_entry_year_index', 'students_created_at_index'],
            'grades' => ['grades_classroom_id_index', 'grades_created_at_index', 'grades_student_subject_index'],
            'payments' => ['payments_created_at_index'],
            'charges' => ['charges_created_at_index'],
            'report_cards' => ['report_cards_student_id_index'],
            'student_report_cards' => ['student_report_cards_student_id_index'],
            'schedule_details' => ['schedule_details_schedule_id_index'],
        ];

        foreach ($indexes as $table => $indexList) {
            Schema::table($table, function (Blueprint $table) use ($indexList) {
                foreach ($indexList as $index) {
                    try {
                        $table->dropIndex($index);
                    } catch (Exception $e) {
                        // Index doesn't exist
                    }
                }
            });
        }
    }
};
