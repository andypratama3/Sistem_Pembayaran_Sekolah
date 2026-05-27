<?php

namespace App\Policies;

use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferencePolicy
{
    /**
     * Determine whether the user can view their notification preferences.
     * All authenticated users can view their own
     */
    public function view(User $user, ?NotificationPreference $notificationPreference = null): bool
    {
        if ($notificationPreference) {
            return $notificationPreference->user_id === $user->id || $user->hasRole('admin');
        }

        return true;
    }

    /**
     * Determine whether the user can update their notification preferences.
     * Users can only update their own, admins can update any
     */
    public function update(User $user, NotificationPreference $notificationPreference): bool
    {
        return $notificationPreference->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete their notification preferences.
     * Users can only delete their own, admins can delete any
     */
    public function delete(User $user, NotificationPreference $notificationPreference): bool
    {
        return $notificationPreference->user_id === $user->id || $user->hasRole('admin');
    }
}
