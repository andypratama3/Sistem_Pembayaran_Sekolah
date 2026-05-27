<?php

namespace Database\Factories;

use App\Models\PaymentTitle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentTitle>
 */
class PaymentTitleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'id' => (string) Str::uuid(),
            'name' => ucwords($name),
            'code' => strtoupper('PT-'.Str::random(5)),
            'slug' => Str::slug($name.'-'.Str::random(3)),
        ];
    }
}
