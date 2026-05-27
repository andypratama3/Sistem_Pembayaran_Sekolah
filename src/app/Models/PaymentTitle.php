<?php

namespace App\Models;

use App\Traits\RealtimeSync;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 */
class PaymentTitle extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids, RealtimeSync;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'slug',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_title_id', 'id');
    }
}
