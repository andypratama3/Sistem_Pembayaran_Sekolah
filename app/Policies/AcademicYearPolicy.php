<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    /**
     * Determine whether the user can view any academic years.
     * All authenticated users can view
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the academic year.
     * All authenticated users can view
     */
    public function view(User $user, ?AcademicYear $academicYear = null): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create academic years.
     * Only admin can create
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the academic year.
     * Only admin can update
     */
    public function update(User $user, ?AcademicYear $academicYear = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the academic year.
     * Only admin can delete
     */
    public function delete(User $user, ?AcademicYear $academicYear = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the academic year.
     */
    public function restore(User $user, ?AcademicYear $academicYear = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the academic year.
     */
    public function forceDelete(User $user, ?AcademicYear $academicYear = null): bool
    {
        return $user->hasRole('admin');
    }
}
