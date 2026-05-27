<?php

namespace App\Jobs;

use App\Mail\PaymentNotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmailJob implements ShouldQueue
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
            if (! $this->user->email) {
                Log::warning('No email found for user', [
                    'user_id' => $this->user->id,
                ]);
                $this->notification->update(['sent_at' => now()]);

                return;
            }

            Mail::to($this->user->email)->send(new PaymentNotificationMail($this->notification));

            $this->notification->update([
                'sent_at' => now(),
                'channel' => 'email',
            ]);

            Log::info('Email notification sent', [
                'user_id' => $this->user->id,
                'notification_id' => $this->notification->id,
                'email' => $this->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('SendNotificationEmailJob error', [
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

    public function failed(\Throwable $exception): void
    {
        Log::error('SendNotificationEmailJob failed permanently', [
            'user_id' => $this->user->id,
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);

        $this->notification->update(['failed_at' => now()]);
    }
}
