<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $notification;

    public $tries = 3;

    public $timeout = 120;

    public $backoff = [60, 300];

    public function __construct(User $user, Notification $notification)
    {
        $this->user = $user;
        $this->notification = $notification;
    }

    public function handle(): void
    {
        try {
            $phone = $this->getPhoneNumber();

            if (! $phone) {
                Log::warning('No phone number found for SMS notification', [
                    'user_id' => $this->user->id,
                ]);
                $this->notification->update(['sent_at' => now()]);

                return;
            }

            // SMS sending implementation would go here
            // e.g., using a third-party SMS gateway
            Log::info('SMS notification dispatched', [
                'user_id' => $this->user->id,
                'notification_id' => $this->notification->id,
                'phone' => $phone,
                'message' => $this->notification->title ?? 'No title',
            ]);

            $this->notification->update([
                'sent_at' => now(),
                'channel' => 'sms',
            ]);
        } catch (\Exception $e) {
            Log::error('SendSmsNotificationJob error', [
                'user_id' => $this->user->id ?? null,
                'notification_id' => $this->notification->id ?? null,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() < $this->tries) {
                $delay = $this->backoff[$this->attempts() - 1] ?? 300;
                $this->release($delay);
            } else {
                $this->fail($e);
            }
        }
    }

    protected function getPhoneNumber(): ?string
    {
        if ($this->user->employee && $this->user->employee->phone) {
            return $this->user->employee->phone;
        }

        return $this->user->phone ?? null;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendSmsNotificationJob failed permanently', [
            'user_id' => $this->user->id,
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);

        $this->notification->update(['failed_at' => now()]);
    }
}
