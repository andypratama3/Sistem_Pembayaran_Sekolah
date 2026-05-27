<?php

namespace App\Models;

use App\Traits\RealtimeSync;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargeArchive extends Model
{
    use HasFactory, HasUuids, RealtimeSync, SoftDeletes;

    protected $table = 'charges_archive';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'order_id',
        'student_id',
        'gross_amount',
        'payment_type',
        'bank',
        'va_number',
        'transaction_id',
        'transaction_time',
        'fraud_status',
        'transaction_status',
        'snap_token',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'transaction_time' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
