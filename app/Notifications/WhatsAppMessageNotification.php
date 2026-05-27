<?php

namespace App\Notifications;

use App\Models\WhatsAppConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WhatsAppMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly WhatsAppConversation $conversation,
        private readonly string $profileName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Pesan WhatsApp baru dari {$this->profileName}",
            'message' => "Percakapan dari {$this->profileName} memerlukan respon Anda.",
            'conversation_id' => $this->conversation->id,
            'phone_number' => $this->conversation->phone_number,
            'action_url' => route('dashboard.whatsapp-chat.show', $this->conversation->id),
            'type' => 'whatsapp_message',
        ];
    }
}
