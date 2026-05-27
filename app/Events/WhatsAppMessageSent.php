<?php

namespace App\Events;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?WhatsAppMessage $message;

    public WhatsAppConversation $conversation;

    public function __construct(?WhatsAppMessage $message, WhatsAppConversation $conversation)
    {
        $this->message = $message;
        $this->conversation = $conversation;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('whatsapp-conversation.'.$this->conversation->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message-sent';
    }

    public function broadcastWith(): array
    {
        if (! $this->message) {
            return [
                'conversation_id' => $this->conversation->id,
                'type' => 'conversation_reopened',
            ];
        }

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $this->message->sender_name,
            'content' => $this->message->content,
            'message_type' => $this->message->message_type,
            'media_url' => $this->message->media_url,
            'status' => $this->message->status,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
