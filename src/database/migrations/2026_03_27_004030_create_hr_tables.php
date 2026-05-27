<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('sex', ['Laki-Laki', 'Perempuan']);
            $table->string('phone', 20)->nullable();
            $table->string('nip', 45)->nullable();
            $table->string('nik', 20)->nullable()->unique();
            $table->string('slug')->unique();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
