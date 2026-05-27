<?php

namespace App\Models;

use App\Traits\Cacheable;
use App\Traits\Filterable;
use App\Traits\HasAuditLog;
use App\Traits\ModelAuditable;
use App\Traits\RealtimeSync;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Student $student
 * @property Classroom $classroom
 * @property PaymentTitle $paymentTitle
 * @property string $id
 * @property string|null $payment_title_id
 * @property string|null $order_id
 * @property float|null $gross_amount
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property int|null $student_id
 *
 * @mixin Builder
 */
class Payment extends Model
{
    use Cacheable, Filterable, HasAuditLog, HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids, ModelAuditable, RealtimeSync, Searchable, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'student_id',
        'classroom_id',
        'classroom_type',
        'email',
        'gross_amount',
        'start_date',
        'end_date',
        'payment_type',
        'session_id',
        'payment_url',
        'transaction_id',
        'bulk_id',
        'account_id',
        'payment_title_id',
        'va_number',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'paid_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function paymentTitle(): BelongsTo
    {
        return $this->belongsTo(PaymentTitle::class, 'payment_title_id');
    }

    public function charges()
    {
        return $this->hasMany(Charge::class, 'order_id', 'order_id');
    }
}
