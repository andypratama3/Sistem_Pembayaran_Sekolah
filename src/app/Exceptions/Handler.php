<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed when validating.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReportToSentry()) {
                $this->reportToSentry($e);
            } else {
                // Fallback logging when Sentry DSN is not configured
                \Log::error('Exception (Sentry DSN not configured)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            return $this->handleException($e, $request);
        });
    }

    /**
     * Check if Sentry is configured and should report
     */
    private function shouldReportToSentry(): bool
    {
        return config('sentry.dsn') !== null &&
               config('app.env') !== 'testing';
    }

    /**
     * Report exception to Sentry
     */
    private function reportToSentry(Throwable $exception): void
    {
        try {
            Integration::captureUnhandledException($exception);
        } catch (Throwable $e) {
            \Log::error('Failed to report to Sentry', [
                'original_error' => $exception->getMessage(),
                'sentry_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle exception rendering
     */
    protected function handleException(Throwable $exception, $request)
    {
        // Handle JSON requests
        if ($request->expectsJson()) {
            return $this->renderJsonException($exception);
        }

        // Handle authentication exceptions
        if ($exception instanceof AuthenticationException) {
            return redirect()->guest(route('login'))
                ->with('error', 'Please login to continue');
        }

        // Handle authorization exceptions
        if ($exception instanceof AuthorizationException) {
            return response()->view('errors.403', ['message' => $exception->getMessage()], 403);
        }

        // Handle validation exceptions
        if ($exception instanceof ValidationException) {
            return back()
                ->withErrors($exception->errors())
                ->withInput();
        }

        // Handle HTTP exceptions
        if ($exception instanceof HttpException) {
            return response()->view('errors.'.$exception->getStatusCode(), [
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }

        // Default error page
        return parent::render($request, $exception);
    }

    /**
     * Render JSON exception response
     */
    protected function renderJsonException(Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getExceptionMessage($exception);

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $this->getValidationErrors($exception),
            'code' => $statusCode,
            'timestamp' => now()->toIso8601ZuluString(),
        ], $statusCode);
    }

    /**
     * Get HTTP status code from exception
     */
    protected function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof AuthenticationException) {
            return 401;
        }

        if ($exception instanceof AuthorizationException) {
            return 403;
        }

        if ($exception instanceof ValidationException) {
            return 422;
        }

        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    /**
     * Get exception message
     */
    protected function getExceptionMessage(Throwable $exception): string
    {
        if ($exception instanceof AuthenticationException) {
            return 'Unauthenticated';
        }

        if ($exception instanceof AuthorizationException) {
            return 'Unauthorized';
        }

        if ($exception instanceof ValidationException) {
            return 'Validation failed';
        }

        if ($exception instanceof HttpException) {
            return $exception->getMessage() ?: 'HTTP Error';
        }

        return config('app.debug') ? $exception->getMessage() : 'Internal Server Error';
    }

    /**
     * Get validation errors
     */
    protected function getValidationErrors(Throwable $exception): array
    {
        if ($exception instanceof ValidationException) {
            return $exception->errors();
        }

        return [];
    }
}
