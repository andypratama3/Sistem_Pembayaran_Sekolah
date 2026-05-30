<?php

namespace App\Models;

use App\Traits\RealtimeSync;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Charge extends Model
{
    use HasFactory, HasUuids, RealtimeSync, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'order_id',
        'order_id_alt',
        'student_id',
        'payment_id',
        'gross_amount',
        'payment_type',
        'bank',
        'va_number',
        'transaction_id',
        'transaction_time',
        'fraud_status',
        'payment_title_id',
        'transaction_status',
        'action_name',
        'method',
        'action_url',
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

    public function paymentTitle()
    {
        return $this->belongsTo(PaymentTitle::class, 'payment_title_id', 'id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }
}
