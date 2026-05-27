<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Payment;
use App\Models\PaymentTitle;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'student_id' => Student::factory(),
            'classroom_id' => Classroom::factory(),
            'classroom_type' => 'Regular',
            'payment_title_id' => PaymentTitle::factory(),
            'order_id' => 'ORDER-'.strtoupper(Str::random(8)),
            'email' => fake()->safeEmail(),
            'gross_amount' => fake()->randomFloat(2, 50000, 5000000),
            'payment_type' => fake()->randomElement(['bank_transfer', 'gopay', 'ovo', 'credit_card']),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }
}
