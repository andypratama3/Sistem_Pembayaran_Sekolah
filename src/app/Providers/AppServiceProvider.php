<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            try {
                $appName = \get_system_config('app_name');
                if ($appName) {
                    config(['app.name' => $appName]);
                }

                $mailFromAddress = \get_system_config('mail_from_address');
                $mailFromName = \get_system_config('mail_from_name');
                if ($mailFromAddress) {
                    config(['mail.from.address' => $mailFromAddress]);
                }
                if ($mailFromName) {
                    config(['mail.from.name' => $mailFromName]);
                }
            } catch (\Exception $e) {
            }
        }

        $this->configureRateLimiters();

        View::composer('*', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();

            $unreadTotal = $user->notifications()
                ->whereNull('read_at')
                ->count();

            $initialNotifications = $user->notifications()
                ->latest()
                ->limit(10)
                ->get();

            $getNotificationUrl = function ($notification) {
                $data = $notification->data ?? [];
                $type = $notification->type ?? '';

                if (! empty($data['url'])) {
                    return $data['url'];
                }

                return match (true) {
                    $type === 'payment' && ! empty($data['payment_id']) => route('dashboard.payments.show', $data['payment_id']),
                    default => '#',
                };
            };

            $view->with([
                'unreadTotal' => $unreadTotal,
                'initialNotifications' => $initialNotifications,
                'getNotificationUrl' => $getNotificationUrl,
            ]);
        });
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('whatsapp-webhook', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('whatsapp-api', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-general', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('midtrans-webhook', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('api-sensitive', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many sensitive requests. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        RateLimiter::for('api-read', function (Request $request) {
            return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->email ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again in '.ceil($headers['Retry-After'] / 60).' minutes.',
                    ], 429, $headers);
                });
        });
    }
}
