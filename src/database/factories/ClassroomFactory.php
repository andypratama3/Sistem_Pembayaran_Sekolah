<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        $academicYear = AcademicYear::first() ?? AcademicYear::create([
            'name' => now()->year.'-'.(now()->year + 1),
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);

        return [
            'name' => 'Class '.fake()->unique()->numberBetween(1, 1000),
            'code' => fake()->unique()->regexify('[0-9][0-9][A-Z]'),
            'academic_year_id' => $academicYear->id,
            'classroom_type' => 'regular',
            'slug' => fake()->unique()->slug(2),
        ];
    }
}
