<?php

namespace App\Models;

use App\Traits\RealtimeSync;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property bool $is_active
 */
class AcademicYear extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids, RealtimeSync;

    protected $table = 'academic_years';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'academic_year_id', 'id');
    }

    public function calendars()
    {
        return $this->hasMany(AcademicCalendar::class, 'academic_year_id', 'id');
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'academic_year_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
