
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
        Schema::create('salary_grades', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('name');          // "Guru Tetap I", "Grade A", dst
            $table->string('code', 20)->unique();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Foreign key ke employees setelah tabel salary_grades dibuat
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('salary_grade_id')
                ->references('id')
                ->on('salary_grades')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_grades');
    }
};
