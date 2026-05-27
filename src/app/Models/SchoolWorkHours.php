<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property string|null $day_name
 * @property string|null $work_start
 * @property string|null $work_end
 */
class SchoolWorkHours extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'day_name',
        'work_start',
        'work_end',
    ];

    public static function isWorkingNow(): bool
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $time = $now->format('H:i');

        $hours = static::where('day_of_week', $dayOfWeek)->first();

        if (! $hours) {
            return false;
        }

        return $time >= $hours->start_time && $time <= $hours->end_time;
    }

    public static function getTodayWorkHours(): ?self
    {
        return static::where('day_of_week', Carbon::now()->dayOfWeek)->first();
    }
}
