<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogSensitiveRequests
{
    /**
     * Sensitive endpoints that require logging.
     */
    protected array $sensitivePatterns = [
        'payments',
        'users',
        'roles',
        'permissions',
        'payroll',
        'grades',
        'attendance',
    ];

    /**
     * Fields to redact from logs for security.
     */
    protected array $redactedFields = [
        'password',
        'token',
        'api_key',
        'secret',
        'credit_card',
        'bank_account',
        'ssn',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log sensitive API requests
        if ($this->isSensitiveOperation($request)) {
            $this->logSensitiveRequest($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request is a sensitive operation.
     */
    private function isSensitiveOperation(Request $request): bool
    {
        // Check if this is an API request (starts with /api/)
        if (! str_starts_with($request->path(), 'api/')) {
            return false;
        }

        // Check if this is a POST, PUT, DELETE, or PATCH request
        if (! in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return false;
        }

        // Check against sensitive patterns
        $path = $request->path();
        foreach ($this->sensitivePatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the sensitive request with redacted sensitive data.
     */
    private function logSensitiveRequest(Request $request, Response $response): void
    {
        $payload = $this->redactSensitiveData($request->all());

        Log::channel('sensitive_api')->info('Sensitive API Request', [
            'timestamp' => now()->toIso8601String(),
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $payload,
            'response_status' => $response->status(),
            'response_size' => strlen($response->getContent()),
        ]);

        // Alert if operation failed
        if ($response->status() >= 400) {
            Log::channel('sensitive_api')->warning('Sensitive API Operation Failed', [
                'user_id' => $request->user()?->id,
                'method' => $request->getMethod(),
                'path' => $request->path(),
                'status' => $response->status(),
            ]);
        }
    }

    /**
     * Redact sensitive fields from request payload.
     */
    private function redactSensitiveData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redactSensitiveData($value);
            } elseif ($this->isSensitiveField($key)) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Check if a field contains sensitive information.
     */
    private function isSensitiveField(string $field): bool
    {
        $lowerField = strtolower($field);

        foreach ($this->redactedFields as $sensitiveField) {
            if (str_contains($lowerField, strtolower($sensitiveField))) {
                return true;
            }
        }

        return false;
    }
}
