<?php

namespace App\Providers;

use App\Events\PaymentCompleted;
use App\Events\WhatsAppMessageSent;
use App\Listeners\PaymentNotificationListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        PaymentCompleted::class => [
            PaymentNotificationListener::class,
        ],

        WhatsAppMessageSent::class => [],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
