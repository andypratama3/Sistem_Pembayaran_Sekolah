<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WhatsAppMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhatsAppMessageFactory extends Factory
{
    protected $model = WhatsAppMessage::class;

    public function definition(): array
    {
        return [
            'conversation_id' => WhatsAppConversationFactory::new(),
            'sender_id' => null,
            'sender_type' => 'parent',
            'message_type' => 'text',
            'content' => fake()->sentence(),
            'status' => 'sent',
            'whatsapp_message_id' => 'wamid.'.fake()->uuid(),
            'retry_count' => 0,
            'is_deleted' => false,
            'reactions' => null,
        ];
    }

    public function fromAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_type' => 'admin',
            'sender_id' => User::factory(),
        ]);
    }
}
