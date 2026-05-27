<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsAppConversation;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsAppConversationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WhatsAppConversation $conversation): bool
    {
        // Admin can view all conversations
        if ($user->hasRole('admin')) {
            return true;
        }

        // Staff can view assigned conversations or their own
        if ($user->hasRole('staff')) {
            return $conversation->assigned_admin_id === $user->id || $conversation->assigned_admin_id === null;
        }

        // Teachers can view conversations of their students
        if ($user->hasRole('teacher') && $conversation->student) {
            // Find teacher associated with the student through classrooms
            return $conversation->student->classrooms()->whereHas('teachers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can reply to the conversation
     */
    public function reply(User $user, WhatsAppConversation $conversation): bool
    {
        return $this->view($user, $conversation) && in_array($user->getRoleNames()[0], ['admin', 'staff']);
    }

    /**
     * Determine whether the user can assign the conversation
     */
    public function assign(User $user, WhatsAppConversation $conversation): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can close the conversation
     */
    public function close(User $user, WhatsAppConversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
