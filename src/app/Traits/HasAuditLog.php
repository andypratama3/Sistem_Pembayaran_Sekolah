<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait HasAuditLog
{
    protected static function bootHasAuditLog()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });
    }

    protected function logAudit(string $action): void
    {
        try {
            $userId = Auth::id();
            $oldValues = $this->getOriginal();
            $newValues = $this->getChanges();

            unset($oldValues['password'], $newValues['password'], $oldValues['remember_token'], $newValues['remember_token']);

            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'model_type' => static::class,
                'model_id' => (string) $this->getKey(),
                'old_values' => $action === 'created' ? null : $oldValues,
                'new_values' => $action === 'deleted' ? null : $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'description' => $this->getAuditDescription($action),
            ]);
        } catch (\Exception $e) {
            // Prevent audit logging from breaking the main operation
            \Log::error('Audit log failed: '.$e->getMessage());
        }
    }

    protected function getAuditDescription(string $action): string
    {
        $modelName = class_basename(static::class);

        return "{$modelName} was {$action}";
    }

    public function getAuditHistory()
    {
        return $this->hasMany(AuditLog::class, 'model_id', $this->getKeyName())
            ->where('model_type', static::class);
    }
}
