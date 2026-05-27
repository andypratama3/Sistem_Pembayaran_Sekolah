<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any permissions.
     * Only admin can view
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the permission.
     * Only admin can view
     */
    public function view(User $user, ?Permission $permission = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create permissions.
     * Only admin can create
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the permission.
     * Only admin can update
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the permission.
     * Only admin can delete
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the permission.
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the permission.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin');
    }
}
