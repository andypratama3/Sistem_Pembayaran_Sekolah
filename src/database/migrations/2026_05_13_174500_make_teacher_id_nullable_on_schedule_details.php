<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we're using SQLite (for testing)
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't support modifying columns easily, so we skip for tests
            // In production (MySQL), this will run properly
            return;
        }

        // MySQL/MariaDB specific
        $fkExists = DB::selectOne("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'schedule_details' 
            AND CONSTRAINT_NAME = 'schedule_details_teacher_id_foreign'
        ");

        if ($fkExists) {
            DB::statement('ALTER TABLE schedule_details DROP FOREIGN KEY schedule_details_teacher_id_foreign');
        }

        DB::statement('ALTER TABLE schedule_details MODIFY teacher_id CHAR(36) NULL');

        DB::statement('
            ALTER TABLE schedule_details 
            ADD CONSTRAINT schedule_details_teacher_id_foreign 
            FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
        ');

        DB::table('schedule_details')->where('teacher_id', '')->update(['teacher_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if we're using SQLite (for testing)
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't support modifying columns easily, so we skip for tests
            return;
        }

        // MySQL/MariaDB specific
        $fkExists = DB::selectOne("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'schedule_details' 
            AND CONSTRAINT_NAME = 'schedule_details_teacher_id_foreign'
        ");

        if ($fkExists) {
            DB::statement('ALTER TABLE schedule_details DROP FOREIGN KEY schedule_details_teacher_id_foreign');
        }

        DB::statement('ALTER TABLE schedule_details MODIFY teacher_id CHAR(36) NOT NULL');

        DB::statement('
            ALTER TABLE schedule_details 
            ADD CONSTRAINT schedule_details_teacher_id_foreign 
            FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
        ');
    }
};
