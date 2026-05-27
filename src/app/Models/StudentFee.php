<?php

namespace App\Models;

use App\Traits\RealtimeSync;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids, RealtimeSync;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'payment_title_id',
        'amount',
        'due_date',
        'status',
        'academic_year',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function paymentTitle()
    {
        return $this->belongsTo(PaymentTitle::class, 'payment_title_id');
    }
}
