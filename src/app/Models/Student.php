<?php

namespace App\Models;

use App\Traits\RealtimeSync;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin Builder
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $nisn
 * @property string|null $nis
 * @property string|null $gender
 * @property string|null $birth_place
 * @property string|null $religion
 * @property string|null $address
 * @property string|null $guardian_name
 * @property string|null $phone
 * @property string|null $user_id
 * @property Carbon|null $birth_date
 * @property string $status
 * @property string|null $father_name
 * @property string|null $mother_name
 * @property User|null $user
 * @property Classroom|null $classroom
 * @property-read Collection<int, Classroom> $classrooms
 * @property-read Collection<int, StudentAttendance> $attendances
 * @property-read Collection<int, Grade> $grades
 * @property-read Collection<int, StudentExtracurricular> $studentExtracurriculars
 * @property string|null $photo
 */
class Student extends Model
{
    use HasFactory, HasUuids, RealtimeSync, SoftDeletes;

    public $incrementing = false;

    public $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'gender',
        'birth_place',
        'birth_date',
        'nisn',
        'religion',
        'spp',
        'dpp',
        'uniform_fee',
        'va_number',
        'previous_school_name',
        'previous_school_address',
        'entry_year',
        'entry_date',
        'scholarship',
        'photo',
        'guardian_type',
        'father_name',
        'mother_name',
        'father_education',
        'mother_education',
        'father_occupation',
        'mother_occupation',
        'guardian_name',
        'guardian_occupation',
        'guardian_address',
        'rt',
        'rw',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'street',
        'residence_type',
        'phone',
        'parent_phone',
        'parent_email',
        'address',
        'slug',
        'dpp_status',
        'status',
        'phone_verified',
        'phone_verified_at',
        'import_id',
        'guardian_education',
        'village_name',
        'district_name',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'entry_date' => 'date',
        'spp' => 'integer',
        'dpp' => 'integer',
        'uniform_fee' => 'integer',
        'phone_verified' => 'boolean',
        'phone_verified_at' => 'datetime',
    ];

    public const STATUS_BARU = 'baru';

    public const STATUS_TERDAFTAR = 'terdaftar';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_PINDAHAN = 'pindahan';

    public const STATUS_GRADUATED = 'graduated';

    public const STATUS_ALUMNI = 'alumni';

    public const STATUS_TRANSFER_OUT = 'transfer_out';

    public const STATUS_DROPPED = 'dropped';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'student_classrooms')
            ->withPivot([
                'academic_year_id',
                'classroom_type',
                'status',
                'enrolled_at',
                'left_at',
                'notes',
                'enrolled_by',
            ]);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    /**
     * Get parents as a collection-like query for compatibility.
     * Returns father, mother, and guardian names as parent objects.
     */
    public function getParents(): \Illuminate\Support\Collection
    {
        $parents = collect();

        if ($this->father_name) {
            $parents->push((object) [
                'name' => $this->father_name,
                'relation' => 'father',
            ]);
        }

        if ($this->mother_name) {
            $parents->push((object) [
                'name' => $this->mother_name,
                'relation' => 'mother',
            ]);
        }

        if ($this->guardian_name) {
            $parents->push((object) [
                'name' => $this->guardian_name,
                'relation' => 'guardian',
            ]);
        }

        return $parents;
    }

    public function currentClassroom(): ?Classroom
    {
        return $this->classrooms()
            ->wherePivot('status', StudentClassroom::STATUS_ACTIVE)
            ->with('academicYear')
            ->first();
    }

    public function getClassroomAttribute(): ?Classroom
    {
        return $this->currentClassroom();
    }

    public function currentEnrollment(): ?StudentClassroom
    {
        return $this->classrooms()
            ->wherePivot('status', StudentClassroom::STATUS_ACTIVE)
            ->first()?->pivot;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function isGraduated(): bool
    {
        return in_array($this->status, [self::STATUS_GRADUATED, self::STATUS_ALUMNI]);
    }

    public function getAge(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getUnpaidBalance(): int
    {
        return $this->payments()
            ->where('status', '!=', 'paid')
            ->sum('amount');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNewStudents($query)
    {
        return $query->where('status', self::STATUS_BARU);
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', self::STATUS_TERDAFTAR);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_DITERIMA);
    }

    public function scopeGraduated($query)
    {
        return $query->whereIn('status', [self::STATUS_GRADUATED, self::STATUS_ALUMNI]);
    }
}
