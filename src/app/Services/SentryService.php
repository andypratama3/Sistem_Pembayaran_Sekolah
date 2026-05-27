<?php

namespace App\Services;

use Sentry\Breadcrumb;
use Sentry\Laravel\Facade as Sentry;
use Sentry\Severity;
use Sentry\State\Scope;
use Throwable;

class SentryService
{
    /**
     * Capture an exception in Sentry
     */
    public static function captureException(Throwable $exception, array $context = []): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            Sentry::captureException($exception);

            if (! empty($context)) {
                Sentry::configureScope(function (Scope $scope) use ($context) {
                    foreach ($context as $key => $value) {
                        $scope->setContext($key, $value);
                    }
                });
            }
        } catch (Throwable $e) {
            \Log::error('Failed to capture exception in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Capture a message in Sentry
     */
    public static function captureMessage(string $message, string $level = 'info', array $context = []): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            Sentry::captureMessage($message, $level);

            if (! empty($context)) {
                Sentry::configureScope(function (Scope $scope) use ($context) {
                    foreach ($context as $key => $value) {
                        $scope->setContext($key, $value);
                    }
                });
            }
        } catch (Throwable $e) {
            \Log::error('Failed to capture message in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Add breadcrumb to transaction
     */
    public static function addBreadcrumb(string $message, string $category = 'app', string $level = 'info', array $data = []): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            Sentry::addBreadcrumb(new Breadcrumb(
                level: Severity::fromString($level),
                category: $category,
                message: $message,
                data: $data
            ));
        } catch (Throwable $e) {
            \Log::error('Failed to add breadcrumb in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Set user context
     */
    public static function setUserContext(int|string $userId, string $email = '', string $username = '', array $extraData = []): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            Sentry::configureScope(function (Scope $scope) use ($userId, $email, $username, $extraData) {
                $scope->setUser([
                    'id' => $userId,
                    'email' => $email,
                    'username' => $username,
                    ...$extraData,
                ]);
            });
        } catch (Throwable $e) {
            \Log::error('Failed to set user context in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear user context
     */
    public static function clearUserContext(): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            Sentry::configureScope(function (Scope $scope) {
                $scope->setUser(null);
            });
        } catch (Throwable $e) {
            \Log::error('Failed to clear user context in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Add custom tags to the current scope
     */
    public static function addTags(array $tags): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            Sentry::configureScope(function (Scope $scope) use ($tags) {
                foreach ($tags as $key => $value) {
                    $scope->setTag($key, (string) $value);
                }
            });
        } catch (Throwable $e) {
            \Log::error('Failed to add tags in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Add custom context to the current scope
     */
    public static function addContext(string $contextName, array $data): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            Sentry::configureScope(function (Scope $scope) use ($contextName, $data) {
                $scope->setContext($contextName, $data);
            });
        } catch (Throwable $e) {
            \Log::error('Failed to add context in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Set extra data to the current scope
     */
    public static function addExtra(string $key, $value): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            Sentry::configureScope(function (Scope $scope) use ($key, $value) {
                $scope->setExtra($key, $value);
            });
        } catch (Throwable $e) {
            \Log::error('Failed to add extra data in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log a database query performance issue
     */
    public static function logDatabaseQuery(string $query, float $duration, bool $isSlowQuery = false): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        if (! $isSlowQuery || $duration < 1000) {
            return;
        }

        try {
            self::addBreadcrumb(
                message: "Slow database query ({$duration}ms)",
                category: 'database',
                level: 'warning',
                data: [
                    'duration_ms' => $duration,
                    'query' => substr($query, 0, 200), // Truncate for safety
                ]
            );
        } catch (Throwable $e) {
            \Log::error('Failed to log database query in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log an API call
     */
    public static function logApiCall(string $method, string $endpoint, int $statusCode, float $duration, array $additionalData = []): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            $level = $statusCode >= 500 ? 'error' : 'info';

            self::addBreadcrumb(
                message: "{$method} {$endpoint} - {$statusCode}",
                category: 'http',
                level: $level,
                data: [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status_code' => $statusCode,
                    'duration_ms' => $duration,
                    ...$additionalData,
                ]
            );
        } catch (Throwable $e) {
            \Log::error('Failed to log API call in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log a queue job
     */
    public static function logQueueJob(string $jobName, string $status, float $duration, array $additionalData = []): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            $level = $status === 'failed' ? 'error' : 'info';

            self::addBreadcrumb(
                message: "Queue job: {$jobName} - {$status}",
                category: 'queue',
                level: $level,
                data: [
                    'job' => $jobName,
                    'status' => $status,
                    'duration_ms' => $duration,
                    ...$additionalData,
                ]
            );
        } catch (Throwable $e) {
            \Log::error('Failed to log queue job in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log an authentication event
     */
    public static function logAuthEvent(string $eventType, int|string|null $userId = null, array $additionalData = []): void
    {
        if (! \get_system_config('sentry_dsn', config('sentry.dsn')) || config('app.env') === 'testing') {
            return;
        }

        try {
            self::addBreadcrumb(
                message: "Auth event: {$eventType}",
                category: 'auth',
                level: 'info',
                data: [
                    'event_type' => $eventType,
                    'user_id' => $userId,
                    ...$additionalData,
                ]
            );
        } catch (Throwable $e) {
            \Log::error('Failed to log auth event in Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Test Sentry connection
     */
    public static function testConnection(): array
    {
        $result = [
            'status' => 'error',
            'message' => 'Sentry is not configured',
            'dsn' => \get_system_config('sentry_dsn', config('sentry.dsn')) ? 'Configured' : 'Not configured',
        ];

        if (! \get_system_config('sentry_dsn', config('sentry.dsn'))) {
            return $result;
        }

        try {
            self::captureMessage('Test message from ProductSchool', 'info', ['test' => true]);
            $result['status'] = 'success';
            $result['message'] = 'Test message sent to Sentry successfully';
        } catch (Throwable $e) {
            $result['message'] = 'Failed to send test message: '.$e->getMessage();
        }

        return $result;
    }
}
