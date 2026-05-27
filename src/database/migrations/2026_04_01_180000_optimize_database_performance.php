<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing performance indexes
     */
    public function up(): void
    {
        // Students table optimization
        if (Schema::hasTable('students')) {
            $this->addIndexIfNotExists('students', 'created_at');
            $this->addIndexIfNotExists('students', 'status');
            $this->addCompositeIndexIfNotExists('students', ['classroom_id', 'status']);
        }

        // Teachers table optimization
        if (Schema::hasTable('teachers')) {
            $this->addIndexIfNotExists('teachers', 'status');
            $this->addIndexIfNotExists('teachers', 'academic_year');
        }

        // Employees table optimization
        if (Schema::hasTable('employees')) {
            $this->addIndexIfNotExists('employees', 'status');
            $this->addIndexIfNotExists('employees', 'employee_type');
        }

        // Grades table optimization
        if (Schema::hasTable('grades')) {
            $this->addCompositeIndexIfNotExists('grades', ['student_id', 'subject_id', 'academic_year']);
        }

        // Student Attendance optimization
        if (Schema::hasTable('student_attendance')) {
            $this->addCompositeIndexIfNotExists('student_attendance', ['student_id', 'date']);
            $this->addCompositeIndexIfNotExists('student_attendance', ['classroom_id', 'date']);
        }

        // Audit logs optimization
        if (Schema::hasTable('audit_logs')) {
            $this->addCompositeIndexIfNotExists('audit_logs', ['auditable_type', 'auditable_id']);
            $this->addCompositeIndexIfNotExists('audit_logs', ['user_id', 'created_at']);
        }

        // WhatsApp messages optimization
        if (Schema::hasTable('whatsapp_messages')) {
            $this->addCompositeIndexIfNotExists('whatsapp_messages', ['conversation_id', 'created_at']);
        }

        // Schedules optimization
        if (Schema::hasTable('schedules')) {
            $this->addCompositeIndexIfNotExists('schedules', ['classroom_id', 'academic_year']);
        }

        // Leave requests optimization
        if (Schema::hasTable('leave_requests')) {
            $this->addCompositeIndexIfNotExists('leave_requests', ['requestable_type', 'requestable_id', 'status']);
        }

        // Documents optimization
        if (Schema::hasTable('documents')) {
            $this->addIndexIfNotExists('documents', 'documentable_type');
        }

        // Notification preferences optimization
        if (Schema::hasTable('notification_preferences')) {
            $this->addCompositeIndexIfNotExists('notification_preferences', ['user_id', 'notification_type']);
        }

        // Charges optimization
        if (Schema::hasTable('charges')) {
            $this->addCompositeIndexIfNotExists('charges', ['payment_title_id', 'transaction_status']);
        }

        // Users table
        if (Schema::hasTable('users')) {
            $this->addIndexIfNotExists('users', 'email');
        }

        // Soft deletes optimization
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'deleted_at')) {
            $this->addIndexIfNotExists('payments', 'deleted_at');
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'deleted_at')) {
            $this->addIndexIfNotExists('employees', 'deleted_at');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Indexes are safe to keep as they only improve performance
        // Removing them is intentionally avoided to prevent conflicts with rollbacks
    }

    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, string $column): void
    {
        try {
            if (! $this->indexExists($table, $column)) {
                DB::statement("ALTER TABLE {$table} ADD INDEX idx_{$table}_{$column} ({$column})");
            }
        } catch (Exception $e) {
            // Index might already exist, skip silently
        }
    }

    /**
     * Add composite index if it doesn't exist
     */
    private function addCompositeIndexIfNotExists(string $table, array $columns): void
    {
        try {
            $columnStr = implode(', ', $columns);
            $indexName = 'idx_'.$table.'_'.implode('_', $columns);

            if (! $this->indexExists($table, implode('_', $columns))) {
                DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} ({$columnStr})");
            }
        } catch (Exception $e) {
            // Index might already exist, skip silently
        }
    }

    /**
     * Check if index exists on table
     */
    private function indexExists(string $table, string $column): bool
    {
        try {
            $result = DB::select(
                'SELECT COUNT(*) as count FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? 
                AND COLUMN_NAME LIKE ?',
                [$table, "%{$column}%"]
            );

            return $result[0]->count > 0;
        } catch (Exception $e) {
            return false;
        }
    }
};
