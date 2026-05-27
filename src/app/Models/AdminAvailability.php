<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAvailability extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'admin_availabilities';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'available_start',
        'available_end',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'available_start' => 'datetime:H:i:s',
        'available_end' => 'datetime:H:i:s',
    ];

    /**
     * Get the admin user
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Check if admin is available now
     */
    public static function isAdminAvailableNow(User $admin): bool
    {
        $today = now()->dayOfWeek;
        $currentTime = now()->format('H:i:s');

        $availability = self::where('user_id', $admin->id)
            ->where('day_of_week', $today)
            ->where('is_available', true)
            ->first();

        if (! $availability) {
            return false;
        }

        if ($availability->available_start && $availability->available_end) {
            return $currentTime >= $availability->available_start &&
                   $currentTime <= $availability->available_end;
        }

        return true;
    }

    /**
     * Get available admins for current time
     */
    public static function getAvailableAdmins()
    {
        $today = now()->dayOfWeek;
        $currentTime = now()->format('H:i:s');

        return self::where('day_of_week', $today)
            ->where('is_available', true)
            ->where(function ($query) use ($currentTime) {
                $query->whereNull('available_start')
                    ->orWhere(function ($q) use ($currentTime) {
                        $q->where('available_start', '<=', $currentTime)
                            ->where('available_end', '>=', $currentTime);
                    });
            })
            ->with('admin')
            ->get()
            ->pluck('admin');
    }
}
