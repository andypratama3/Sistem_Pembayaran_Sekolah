<?php

namespace App\Providers;

use App\Services\Ai\AiTemplateProvider;
use App\Services\Ai\StubAiTemplateProvider;
use Illuminate\Support\ServiceProvider;

/**
 * AiServiceProvider — Resolves the active AI backend.
 *
 * Driver is selected via `services.ai.driver` (env: `AI_DRIVER`).
 * Currently registered drivers:
 *   - "stub" (default, offline, no API key required)
 *
 * Other drivers (claude, openai) can be added by:
 *   1. Implementing AiTemplateProvider in a new class.
 *   2. Adding a case in `resolveDriver()` below.
 *   3. Setting AI_DRIVER=claude (or similar) in .env.
 */
class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiTemplateProvider::class, function ($app) {
            $driver = config('services.ai.driver', 'stub');

            return $this->resolveDriver($driver);
        });
    }

    private function resolveDriver(string $driver): AiTemplateProvider
    {
        return match ($driver) {
            'stub' => new StubAiTemplateProvider,
            default => new StubAiTemplateProvider, // safe default
        };
    }
}
