<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     * Finance, HR, and admin can view all payments
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'finance', 'hr']);
    }

    /**
     * Determine whether the user can view the payment.
     * Students can view their own, admins and finance can view all
     */
    public function view(User $user, ?Payment $payment = null): bool
    {
        // Admin and Finance can view any payment
        if ($user->hasRole(['admin', 'finance'])) {
            return true;
        }

        // HR can view payments
        if ($user->hasRole('hr')) {
            return true;
        }

        // Students can view their own payments
        if ($user->hasRole('student') && $payment && $payment->student_id === auth()->id()) {
            return true;
        }

        // Parents can view their child's payments
        if ($user->hasRole('parent') && $payment) {
            return $payment->student->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create payments.
     * Only admin and finance can create
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can update the payment.
     * Only admin and finance can update
     */
    public function update(User $user, ?Payment $payment = null): bool
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can delete the payment.
     * Only admin can delete
     */
    public function delete(User $user, ?Payment $payment = null): bool
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can mark payment as paid.
     * Finance and admin can mark as paid
     */
    public function markPaid(User $user, ?Payment $payment = null): bool
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can restore the payment.
     */
    public function restore(User $user, ?Payment $payment = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the payment.
     */
    public function forceDelete(User $user, ?Payment $payment = null): bool
    {
        return $user->hasRole('admin');
    }
}
