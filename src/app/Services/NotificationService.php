<?php

namespace App\Services;

use App\Jobs\SendNotificationEmailJob;
use App\Jobs\SendSmsNotificationJob;
use App\Models\Notification;

class NotificationService
{
    /**
     * Send notification and optionally dispatch email/SMS jobs.
     */
    public function send(array $payload): Notification
    {
        $notification = Notification::create([
            'user_id' => $payload['user_id'],
            'type' => $payload['type'] ?? 'info',
            'title' => $payload['title'],
            'body' => $payload['message'] ?? $payload['body'],
            'data' => $payload['data'] ?? [],
            'read_at' => null,
        ]);

        // Dispatch Email if requested
        if (! empty($payload['should_email'])) {
            SendNotificationEmailJob::dispatch($notification->user, $notification);
        }

        // Dispatch SMS if requested
        if (! empty($payload['should_sms'])) {
            SendSmsNotificationJob::dispatch($notification->user, $notification);
        }

        return $notification;
    }
}
