<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     * Only admin can view all users
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('superadmin') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the user.
     * Users can view their own, admins can view all
     */
    public function view(User $user, ?User $model = null): bool
    {
        if ($model) {
            return $user->id === $model->id || $user->hasRole('superadmin') || $user->hasRole('admin');
        }

        return true;
    }

    /**
     * Determine whether the user can create users.
     * Only admin can create
     */
    public function create(User $user): bool
    {
        return $user->hasRole('superadmin') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the user.
     * Users can update their own profile, admins can update any
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasRole('superadmin') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the user.
     * Only admin can delete
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('superadmin') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('superadmin') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('superadmin') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can clear application cache.
     * Only admin can clear cache
     */
    public function clearCache(User $user): bool
    {
        return $user->hasRole('superadmin') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view cache statistics.
     * Only admin can view cache stats
     */
    public function viewCacheStats(User $user): bool
    {
        return $user->hasRole('superadmin') || $user->hasRole('admin');
    }
}
