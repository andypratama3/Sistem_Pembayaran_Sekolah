<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table
        if (config('database.default') === 'sqlite') {
            // Get existing data
            $students = DB::table('students')->get();

            // Drop the table and recreate
            Schema::dropIfExists('students');

            Schema::create('students', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->after('id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('email')->after('name')->nullable()->unique();
                $table->enum('gender', ['Laki-laki', 'Perempuan']);
                $table->string('birth_place');
                $table->date('birth_date');
                $table->string('nisn', 20)->unique();
                $table->string('religion', 50)->nullable();
                $table->integer('spp')->nullable();
                $table->integer('dpp')->nullable();
                $table->integer('uniform_fee')->default(0);
                $table->string('va_number')->nullable();
                $table->string('previous_school_name')->nullable();
                $table->string('previous_school_address')->nullable();
                $table->string('entry_year', 10)->nullable();
                $table->date('entry_date')->nullable();
                $table->string('scholarship')->nullable();
                $table->string('photo', 100)->nullable();
                $table->enum('guardian_type', ['orang_tua', 'wali'])->default('orang_tua');
                $table->string('father_name')->nullable();
                $table->string('mother_name')->nullable();
                $table->string('father_education')->nullable();
                $table->string('mother_education')->nullable();
                $table->string('father_occupation')->nullable();
                $table->string('mother_occupation')->nullable();
                $table->string('guardian_name')->nullable();
                $table->string('guardian_occupation')->nullable();
                $table->string('guardian_address')->nullable();
                $table->string('rt', 10)->nullable();
                $table->string('rw', 10)->nullable();
                $table->string('province_id', 10)->nullable();
                $table->string('regency_id', 10)->nullable();
                $table->string('district_id', 10)->nullable();
                $table->string('village_id', 10)->nullable();
                $table->text('street')->nullable();
                $table->string('residence_type', 50)->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('parent_email')->nullable();
                $table->text('address')->nullable();
                $table->text('alamat')->nullable();
                $table->string('slug')->unique();
                $table->string('dpp_status', 50)->nullable();
                $table->string('status')->default('active');
                $table->tinyInteger('phone_verified')->default(0);
                $table->timestamp('phone_verified_at')->nullable();
                $table->softDeletes()->index();
                $table->timestamps();

                $table->string('import_id')->nullable();
                $table->index('import_id');
                $table->index('name');
                $table->index('entry_year');
            });

            // Re-insert data
            foreach ($students as $student) {
                DB::table('students')->insert((array) $student);
            }
        } else {
            // For MySQL/PostgreSQL
            Schema::table('students', function (Blueprint $table) {
                $table->string('religion', 50)->nullable()->change();
                $table->string('province_id', 10)->nullable()->change();
                $table->string('regency_id', 10)->nullable()->change();
                $table->string('district_id', 10)->nullable()->change();
                $table->string('village_id', 10)->nullable()->change();
                $table->string('phone', 20)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            $students = DB::table('students')->get();

            Schema::dropIfExists('students');

            Schema::create('students', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->after('id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('email')->after('name')->nullable()->unique();
                $table->enum('gender', ['Laki-laki', 'Perempuan']);
                $table->string('birth_place');
                $table->date('birth_date');
                $table->string('nisn', 20)->unique();
                $table->string('religion', 50);
                $table->integer('spp')->nullable();
                $table->integer('dpp')->nullable();
                $table->integer('uniform_fee')->default(0);
                $table->string('va_number')->nullable();
                $table->string('previous_school_name')->nullable();
                $table->string('previous_school_address')->nullable();
                $table->string('entry_year', 10)->nullable();
                $table->date('entry_date')->nullable();
                $table->string('scholarship')->nullable();
                $table->string('photo', 100)->nullable();
                $table->enum('guardian_type', ['orang_tua', 'wali'])->default('orang_tua');
                $table->string('father_name')->nullable();
                $table->string('mother_name')->nullable();
                $table->string('father_education')->nullable();
                $table->string('mother_education')->nullable();
                $table->string('father_occupation')->nullable();
                $table->string('mother_occupation')->nullable();
                $table->string('guardian_name')->nullable();
                $table->string('guardian_occupation')->nullable();
                $table->string('guardian_address')->nullable();
                $table->string('rt', 10)->nullable();
                $table->string('rw', 10)->nullable();
                $table->string('province_id', 10);
                $table->string('regency_id', 10);
                $table->string('district_id', 10);
                $table->string('village_id', 10);
                $table->text('street')->nullable();
                $table->string('residence_type', 50)->nullable();
                $table->string('phone', 20);
                $table->string('parent_phone')->nullable();
                $table->string('parent_email')->nullable();
                $table->text('address')->nullable();
                $table->text('alamat')->nullable();
                $table->string('slug')->unique();
                $table->string('dpp_status', 50)->nullable();
                $table->string('status')->default('active');
                $table->tinyInteger('phone_verified')->default(0);
                $table->timestamp('phone_verified_at')->nullable();
                $table->softDeletes()->index();
                $table->timestamps();

                $table->string('import_id')->nullable();
                $table->index('import_id');
                $table->index('name');
                $table->index('entry_year');
            });

            foreach ($students as $student) {
                DB::table('students')->insert((array) $student);
            }
        } else {
            Schema::table('students', function (Blueprint $table) {
                $table->string('religion', 50)->nullable(false)->change();
                $table->string('province_id', 10)->nullable(false)->change();
                $table->string('regency_id', 10)->nullable(false)->change();
                $table->string('district_id', 10)->nullable(false)->change();
                $table->string('village_id', 10)->nullable(false)->change();
                $table->string('phone', 20)->nullable(false)->change();
            });
        }
    }
};
