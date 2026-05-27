<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use App\Traits\ModelAuditable;
use App\Traits\RealtimeSync;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasAuditLog, HasFactory, HasUuids, ModelAuditable, RealtimeSync;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
