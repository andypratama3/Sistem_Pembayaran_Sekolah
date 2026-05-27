<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    /**
     * Determine whether the user can view any classrooms.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'teacher', 'student']);
    }

    /**
     * Determine whether the user can view the classroom.
     */
    public function view(User $user, ?Classroom $classroom = null): bool
    {
        // Admin can view any classroom
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can view classrooms they teach
        if ($user->hasRole('teacher') && $classroom) {
            return $classroom->teachers()->where('user_id', $user->id)->exists();
        }

        // Students can view their own classrooms
        if ($user->hasRole('student') && $classroom) {
            return $classroom->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create classrooms.
     * Only admin can create
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the classroom.
     * Only admin can update
     */
    public function update(User $user, ?Classroom $classroom = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the classroom.
     * Only admin can delete
     */
    public function delete(User $user, ?Classroom $classroom = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the classroom.
     */
    public function restore(User $user, ?Classroom $classroom = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the classroom.
     */
    public function forceDelete(User $user, ?Classroom $classroom = null): bool
    {
        return $user->hasRole('admin');
    }
}
