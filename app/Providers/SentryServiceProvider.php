<?php

namespace App\Providers;

use App\Services\SentryService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\ServiceProvider;
use Sentry\State\Scope;

class SentryServiceProvider extends ServiceProvider
{
    /**
     * Register Sentry services
     */
    public function register(): void
    {
        // Skip Sentry initialization in testing to prevent error handler side effects
        if ($this->app->environment('testing')) {
            return;
        }

        // Register SentryService as a singleton
        $this->app->singleton(SentryService::class, function () {
            return new SentryService;
        });
    }

    /**
     * Bootstrap Sentry integration
     */
    public function boot(): void
    {
        if ($this->app->environment('testing')) {
            return;
        }
        // Initialize Sentry if configured
        if (config('sentry.dsn')) {
            $this->initializeSentry();
            $this->registerEventListeners();
        }
    }

    /**
     * Initialize Sentry and set default context
     */
    private function initializeSentry(): void
    {
        try {
            // Set application version
            if (config('sentry.release')) {
                \Sentry\withScope(function (Scope $scope): void {
                    $scope->setTag('version', config('sentry.release'));
                });
            }

            // Set application environment
            \Sentry\withScope(function (Scope $scope): void {
                $scope->setTag('environment', config('sentry.environment') ?? config('app.env'));
            });

            // Add application info
            SentryService::addContext('application', [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'version' => config('sentry.release'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to initialize Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Register event listeners for Sentry integration
     */
    private function registerEventListeners(): void
    {
        try {
            // Log authentication events
            $this->app['events']->listen(Login::class, function (Login $event) {
                SentryService::setUserContext(
                    $event->user->id,
                    $event->user->email ?? '',
                    $event->user->name ?? '',
                );
                SentryService::logAuthEvent('user_login', $event->user->id, [
                    'user_email' => $event->user->email,
                    'user_name' => $event->user->name,
                ]);
            });

            $this->app['events']->listen(Logout::class, function (Logout $event) {
                SentryService::logAuthEvent('user_logout', $event->user->id);
                SentryService::clearUserContext();
            });
        } catch (\Throwable $e) {
            \Log::error('Failed to register Sentry event listeners', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
