<?php

namespace App\Models;

use App\Traits\Cacheable;
use App\Traits\Filterable;
use App\Traits\HasAuditLog;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Cacheable, Filterable, HasApiTokens, HasAuditLog,
        HasFactory, HasUuids, Notifiable, Searchable,
        SoftDeletes;
    use HasRoles {
        hasRole as protected traitHasRole;
        hasAnyRole as protected traitHasAnyRole;
        hasAllRoles as protected traitHasAllRoles;
        hasPermissionTo as protected traitHasPermissionTo;
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        if ($this->traitHasRole('superadmin', $guard)) {
            return true;
        }

        return $this->traitHasRole($roles, $guard);
    }

    public function getRoles(?string $name = null)
    {
        if ($name === null) {
            return $this->roles()->get();
        }

        return Role::where('name', $name)
            ->where('guard_name', 'web')
            ->first();
    }

    public function hasAnyRole(...$roles): bool
    {
        if ($this->traitHasRole('superadmin')) {
            return true;
        }

        return $this->traitHasAnyRole(...$roles);
    }

    public function hasAllRoles(...$roles): bool
    {
        if ($this->traitHasRole('superadmin')) {
            return true;
        }

        return $this->traitHasAllRoles(...$roles);
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->traitHasRole('superadmin')) {
            return true;
        }

        return $this->traitHasPermissionTo($permission, $guardName);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name).'-'.Str::random(5);
            }
        });
    }

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'current_team_id',
        'avatar',
        'slug',
        'gauth_id',
        'gauth_type',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    public function whatsappConversations()
    {
        return $this->hasMany(WhatsAppConversation::class, 'assigned_admin_id', 'id');
    }

    public function whatsappMessages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'sender_id', 'id');
    }
}
