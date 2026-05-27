<?php

namespace Database\Factories;

use App\Models\WhatsAppMessageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhatsAppMessageTemplateFactory extends Factory
{
    protected $model = WhatsAppMessageTemplate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'category' => fake()->randomElement(['greeting', 'payment', 'info']),
            'template_text' => fake()->paragraph(),
            'response_time_seconds' => 60,
            'is_active' => true,
            'created_by' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
