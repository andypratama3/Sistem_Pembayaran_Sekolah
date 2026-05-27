<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure staff_positions exists before employees references it
        Schema::create('staff_positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->foreignUuid('parent_position_id')->nullable()->constrained('staff_positions')->nullOnDelete();
            $table->timestamps();

            $table->index('parent_position_id');
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('sex', ['Laki-Laki', 'Perempuan']);
            $table->string('phone', 20)->nullable();
            $table->string('nip', 45)->nullable();
            $table->string('nik', 20)->nullable()->unique();
            $table->string('slug')->unique();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('staff_position_id')->nullable()->constrained('staff_positions')->nullOnDelete();
            $table->foreignUuid('work_shift_id')->nullable()->constrained('work_shifts')->nullOnDelete();
            $table->string('photo')->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->date('join_date')->nullable();
            $table->enum('education_level', ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'])->nullable();
            $table->unsignedTinyInteger('dependent_count')->default(0);
            $table->char('salary_grade_id', 36)->nullable();
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index('name', 'name_index');
            $table->index('status');
        });

        Schema::create('work_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('shift_name');
            $table->enum('employee_type', ['guru', 'tenaga-pendidikan', 'shadow-teacher']);
            $table->time('check_in_time');
            $table->time('check_in_deadline');
            $table->time('check_out_time');
            $table->time('check_out_deadline');
            $table->enum('day', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'])->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('attendance_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('location_name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius')->default(100);
            $table->string('address')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->index();
            $table->timestamps();
        });

        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUuid('attendance_location_id')->nullable()->constrained('attendance_locations')->nullOnDelete();
            $table->foreignUuid('work_shift_id')->nullable()->constrained('work_shifts')->nullOnDelete();
            $table->date('date');
            $table->enum('attendance_status', ['hadir', 'cuti', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->time('check_in_time')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_in_distance', 8, 2)->nullable();
            $table->enum('check_in_status', ['tepat_waktu', 'terlambat'])->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->decimal('check_out_distance', 8, 2)->nullable();
            $table->enum('check_out_status', ['tepat_waktu', 'pulang_cepat', 'auto-generated'])->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_id', 64)->nullable();
            $table->string('ip_address_out', 45)->nullable();
            $table->string('user_agent_out', 500)->nullable();
            $table->string('photo_in')->nullable();
            $table->string('photo_out')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index('date');
            $table->index(['attendance_location_id', 'date']);
        });

        Schema::create('attendance_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('device_fingerprint', 64)->unique();
            $table->string('device_name')->nullable();
            $table->string('device_id', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'is_active']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', ['cuti', 'izin', 'sakit']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->string('attachment', 255)->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_devices');
        Schema::dropIfExists('employee_attendances');
        Schema::dropIfExists('attendance_locations');
        Schema::dropIfExists('work_shifts');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('staff_positions');
    }
};
