<?php

namespace App\Traits;

use App\Events\DataUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Broadcast;

/**
 * RealtimeSync Trait
 *
 * Add realtime data synchronization to any model.
 * Automatically broadcasts changes without page refresh.
 *
 * Usage:
 * class Student extends Model {
 *     use RealtimeSync;
 * }
 */
trait RealtimeSync
{
    /**
     * Boot the trait
     */
    public static function bootRealtimeSync()
    {
        // Broadcast on create
        static::created(function (Model $model) {
            broadcast(new DataUpdated($model));
        });

        // Broadcast on update
        static::updated(function (Model $model) {
            broadcast(new DataUpdated($model));
        });

        // Broadcast on delete
        static::deleted(function (Model $model) {
            broadcast(new DataUpdated($model));
        });
    }

    /**
     * Get the channel name for this model
     */
    public function getBroadcastChannelName()
    {
        return 'data-updated';
    }

    /**
     * Get the model name for broadcasting
     */
    public function getModelName()
    {
        return class_basename(static::class);
    }
}
