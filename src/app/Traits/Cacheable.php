<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    protected $cacheTTL = 300; // Default 5 minutes

    public function getCached(string $key, callable $callback)
    {
        $cacheKey = $this->getCacheKey($key);

        return Cache::remember($cacheKey, $this->cacheTTL, $callback);
    }

    public function flushCache(): void
    {
        $cacheKeyPattern = $this->getCacheKey('*');
        try {
            if (Cache::supportsTags()) {
                Cache::tags($cacheKeyPattern)->flush();
            } else {
                // Fallback for cache drivers that don't support tagging
                Cache::forget($cacheKeyPattern);
            }
        } catch (\Exception $e) {
            // Silently fail if cache flush fails
            \Log::debug('Cache flush failed: '.$e->getMessage());
        }
    }

    protected function getCacheKey(string $key): string
    {
        return 'model:'.static::class.":$key";
    }

    protected static function bootCacheable()
    {
        static::created(function ($model) {
            $model->flushCache();
        });

        static::updated(function ($model) {
            $model->flushCache();
        });

        static::deleted(function ($model) {
            $model->flushCache();
        });
    }
}
