<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentClassroom extends Model
{
    protected $fillable = [
        'student_id',
        'classroom_id',
        'academic_year_id',
        'classroom_type',
        'status',
        'enrolled_at',
        'left_at',
        'notes',
        'enrolled_by',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public $timestamps = true;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_TRANSFERRED = 'transferred';

    public const STATUS_GRADUATED = 'graduated';

    public const STATUS_RETAINED = 'retained';

    public const STATUS_DROPPED = 'dropped';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function enrolledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isTransferred(): bool
    {
        return $this->status === self::STATUS_TRANSFERRED;
    }

    public function isGraduated(): bool
    {
        return $this->status === self::STATUS_GRADUATED;
    }

    public function isRetained(): bool
    {
        return $this->status === self::STATUS_RETAINED;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByClassroom($query, string $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopeByAcademicYear($query, string $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }
}
