<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'id' => (string) Str::uuid(),
            'name' => fake()->unique()->numerify('####-####').'-'.uniqid(),
            'start_date' => $startDate,
            'end_date' => fake()->dateTimeBetween($startDate, '+1 year'),
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }
}
