<?php

namespace App\Events;

use App\Models\WhatsAppConversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppConversationAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WhatsAppConversation $conversation;

    public string $assignedToName;

    public function __construct(WhatsAppConversation $conversation, string $assignedToName)
    {
        $this->conversation = $conversation;
        $this->assignedToName = $assignedToName;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('whatsapp-conversations'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation-assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'assigned_to' => $this->assignedToName,
            'updated_at' => $this->conversation->updated_at->toIso8601String(),
        ];
    }
}
