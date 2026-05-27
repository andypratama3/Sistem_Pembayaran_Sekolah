<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Services\CacheService;
use Illuminate\Support\Facades\Auth;

/**
 * ModelAuditable — Auto-log create/update/delete operations on any model.
 *
 * Usage: Add `use ModelAuditable;` to any Eloquent model.
 */
trait ModelAuditable
{
    public static function bootModelAuditable(): void
    {
        static::created(function ($model) {
            self::writeAuditLog('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            $original = collect($dirty)->mapWithKeys(fn ($v, $k) => [$k => $model->getOriginal($k)])->toArray();

            if (! empty($dirty)) {
                self::writeAuditLog('updated', $model, $original, $dirty);
            }
        });

        static::deleted(function ($model) {
            self::writeAuditLog('deleted', $model, $model->getAttributes(), null);
        });
    }

    protected static function writeAuditLog(string $action, $model, ?array $oldValues, ?array $newValues): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'description' => class_basename($model)." {$action}",
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            // Auto-invalidate dashboard caches when data changes
            CacheService::flushDashboard();
            if (in_array($action, ['created', 'deleted'])) {
                CacheService::flushCounts();
            }
        } catch (\Throwable $e) {
            // Never let audit logging break the main operation
            logger()->warning('Audit log write failed: '.$e->getMessage());
        }
    }
}
