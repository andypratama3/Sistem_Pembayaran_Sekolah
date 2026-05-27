<?php

namespace App\Models;

use App\Traits\Cacheable;
use App\Traits\Filterable;
use App\Traits\HasAuditLog;
use App\Traits\RealtimeSync;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string|null $code
 * @property string|null $classroom_type
 * @property string|null $academic_year_id
 * @property string $slug
 * @property AcademicYear|null $academicYear
 * @property Collection|Student[] $students
 * @property Collection|Teacher[] $teachers
 * @property Collection|Subject[] $subjects
 * @property Teacher|null $homeroomTeacher
 */
class Classroom extends Model
{
    use Cacheable, Filterable, HasAuditLog, \Illuminate\Database\Eloquent\SoftDeletes, RealtimeSync, Searchable;
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'code',
        'academic_year_id',
        'classroom_type',
        'slug',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_classrooms', 'classroom_id', 'student_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'classroom_subjects', 'classroom_id', 'subject_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'classroom_id', 'id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'classroom_id', 'id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_classrooms', 'classroom_id', 'teacher_id');
    }

    public function teacher(): BelongsToMany
    {
        return $this->teachers();
    }

    public function homeroomTeacher(): BelongsToMany
    {
        return $this->teachers();
    }

    public function gradeComponentWeights()
    {
        return $this->hasMany(GradeComponentWeight::class, 'classroom_id');
    }

    public function gradeComponents()
    {
        return $this->hasMany(GradeComponent::class, 'classroom_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
