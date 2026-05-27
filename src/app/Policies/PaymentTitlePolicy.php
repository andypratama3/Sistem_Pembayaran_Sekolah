<?php

namespace App\Policies;

use App\Models\PaymentTitle;
use App\Models\User;

class PaymentTitlePolicy
{
    /**
     * Determine whether the user can view any payment titles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'finance', 'hr']);
    }

    /**
     * Determine whether the user can view the payment title.
     */
    public function view(User $user, ?PaymentTitle $paymentTitle = null): bool
    {
        return $user->hasRole(['admin', 'finance', 'hr']);
    }

    /**
     * Determine whether the user can create payment titles.
     * Only admin can create
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the payment title.
     * Only admin can update
     */
    public function update(User $user, PaymentTitle $paymentTitle): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the payment title.
     * Only admin can delete
     */
    public function delete(User $user, PaymentTitle $paymentTitle): bool
    {
        return $user->hasRole('admin');
    }
}
