<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;

/**
 * Custom exception handler bootstrap that skips error handler registration
 * during testing to prevent PHPUnit 11 risky test warnings.
 *
 * Laravel's default HandleExceptions registers global error/exception handlers
 * which conflict with PHPUnit's own error handler management. During test
 * teardown, Laravel's flushState() removes all handlers, causing PHPUnit to
 * detect handler modifications and mark tests as risky.
 *
 * In testing, we skip handler registration since PHPUnit manages its own
 * error handling.
 */
class HandleExceptions extends BaseHandleExceptions
{
    /**
     * Bootstrap the given application.
     */
    public function bootstrap(Application $app): void
    {
        static::$reservedMemory = str_repeat('x', 32768);
        static::$app = $app;

        if (getenv('APP_ENV') === 'testing' || $app->environment('testing')) {
            return;
        }

        parent::bootstrap($app);
    }
}
