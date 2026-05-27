<?php

namespace Database\Factories;

use App\Models\WhatsAppConversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhatsAppConversationFactory extends Factory
{
    protected $model = WhatsAppConversation::class;

    public function definition(): array
    {
        return [
            'phone_number' => '628'.fake()->numerify('##########'),
            'profile_name' => fake()->name(),
            'status' => 'active',
            'assigned_admin_id' => null,
            'last_message_at' => now(),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }
}
