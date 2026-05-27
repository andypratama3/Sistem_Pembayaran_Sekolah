<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view any audit logs.
     * Admin and superadmin can view
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can view the audit log.
     * Admin and superadmin can view
     */
    public function view(User $user, ?AuditLog $auditLog = null): bool
    {
        return $user->hasRole('admin') || $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can delete the audit log.
     * Only admin can delete (usually reserved for cleanup)
     */
    public function delete(User $user, ?AuditLog $auditLog = null): bool
    {
        return $user->hasRole('admin') || $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore the audit log.
     */
    public function restore(User $user, ?AuditLog $auditLog = null): bool
    {
        return $user->hasRole('admin') || $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the audit log.
     */
    public function forceDelete(User $user, ?AuditLog $auditLog = null): bool
    {
        return $user->hasRole('admin') || $user->hasRole('superadmin');
    }
}
