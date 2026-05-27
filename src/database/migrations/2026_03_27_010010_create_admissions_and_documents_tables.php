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
        Schema::create('provinces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('province_id', 10)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('regencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('regency_id', 10)->unique();
            $table->string('province_id', 10)->index();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('district_id', 10)->unique();
            $table->string('regency_id', 10)->index();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('district_id', 10)->index();
            $table->string('village_id', 10)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('birth_place');
            $table->date('birth_date');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->string('religion', 50)->default('islam');
            $table->string('ethnicity', 100);
            $table->text('address');
            $table->string('previous_school_name')->nullable();
            $table->string('diploma_number')->nullable();
            $table->text('previous_school_address')->nullable();
            $table->enum('guardian_type', ['orang_tua', 'wali'])->default('orang_tua');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_education')->nullable();
            $table->string('mother_education')->nullable();
            $table->text('father_address')->nullable();
            $table->text('mother_address')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_address')->nullable();
            $table->string('diploma_file')->nullable();
            $table->string('birth_certificate');
            $table->string('family_card');
            $table->string('passport_photo');
            $table->string('phone', 20);
            $table->integer('queue_number');
            $table->string('payment_status');
            $table->string('order_id');
            $table->string('status')->nullable()->index();
            $table->tinyInteger('agreed_to_terms')->default(0);
            $table->timestamps();

            $table->index('payment_status');
        });

        Schema::create('document_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('document_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('document_categories')->cascadeOnDelete();
            $table->foreignUuid('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('name');
            $table->longText('html_template');
            $table->json('canvas_json');
            $table->enum('generate_mode', ['perorang', 'daftar'])->default('perorang');
            $table->timestamps();

            $table->index('category_id');
            $table->index('classroom_id');
        });

        Schema::create('document_template_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_template_id')->constrained('document_templates')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['document_template_id', 'subject_id'], 'dts_template_subject_unique');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained('document_templates')->cascadeOnDelete();
            $table->foreignUuid('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('data_json');
            $table->string('status', 50)->default('draft');
            $table->string('file_path')->nullable();
            $table->string('bulk_batch_id', 100)->nullable();
            $table->string('verification_code')->unique();
            $table->softDeletes()->index();
            $table->timestamps();

            $table->index('template_id');
            $table->index('student_id');
            $table->index('bulk_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_template_subjects');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_categories');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }
};
