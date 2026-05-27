<?php

use App\Bootstrap\HandleExceptions;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\LogSensitiveRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->api(append: [
            LogSensitiveRequests::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'check_user_status' => CheckUserStatus::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        if (! app()->environment('testing')) {
            Integration::handles($exceptions);
        }

    })->create();

// Bind custom HandleExceptions BEFORE bootstrappers run
// This must happen here because bootstrappers run before service providers register
$app->beforeBootstrapping(BaseHandleExceptions::class, function ($app) {
    $app->bind(BaseHandleExceptions::class, function () {
        return new HandleExceptions;
    });
});

return $app;
