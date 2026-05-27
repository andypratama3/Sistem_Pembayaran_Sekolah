<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the user can view any students.
     * Teachers can view their own students, admins can view all
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'teacher', 'finance', 'hr']);
    }

    /**
     * Determine whether the user can view the student.
     * Teachers can view students in their classrooms, admins can view all
     */
    public function view(User $user, ?Student $student = null): bool
    {
        // Admin and HR can view any student
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Teachers can view students in their classrooms
        if ($user->hasRole('teacher') && $student) {
            return $student->classrooms()->whereHas('teachers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists();
        }

        // Finance can view for payment purposes
        if ($user->hasRole('finance') && $student) {
            return true;
        }

        // Students can view their own profile
        if ($user->hasRole('student') && $student && $student->user_id === $user->id) {
            return true;
        }

        // Parents can view their child's profile
        if ($user->hasRole('parent') && $student && $student->parent_email === $user->email) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create students.
     * Only admin and HR can create
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can update the student.
     * Admin, HR, and teachers (partial) can update
     */
    public function update(User $user, ?Student $student = null): bool
    {
        // Admin and HR can update any student
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Teachers can only update students in their classrooms (grades, attendance)
        if ($user->hasRole('teacher') && $student) {
            return $student->classrooms()->whereHas('teachers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists();
        }

        // If no student instance (e.g., checking if user can update "any" student)
        // Admin/HR already returned true above
        return false;
    }

    /**
     * Determine whether the user can delete the student.
     * Only admins and HR can delete
     */
    public function delete(User $user, ?Student $student = null): bool
    {
        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can restore the student.
     * Only admin can restore
     */
    public function restore(User $user, ?Student $student = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the student.
     * Only admin can force delete
     */
    public function forceDelete(User $user, ?Student $student = null): bool
    {
        return $user->hasRole('admin');
    }
}
