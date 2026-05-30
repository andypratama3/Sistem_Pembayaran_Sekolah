<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $day_of_week
 * @property string|null $work_start
 * @property string|null $work_end
 * @property bool $is_active
 */
class SchoolWorkHours extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'day_of_week',
        'work_start',
        'work_end',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getDayNameAttribute(): ?string
    {
        return now()->startOfWeek()->addDays($this->day_of_week)->locale('id')->dayName;
    }

    public static function isWorkingNow(): bool
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $time = $now->format('H:i');

        $hours = static::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $hours) {
            return false;
        }

        return $time >= $hours->work_start && $time <= $hours->work_end;
    }

    public static function getTodayWorkHours(): ?self
    {
        return static::where('day_of_week', Carbon::now()->dayOfWeek)
            ->where('is_active', true)
            ->first();
    }
}
