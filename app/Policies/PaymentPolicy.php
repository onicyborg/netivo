<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $this->canReadAll($user) || $this->owns($user, $payment);
    }

    public function proof(User $user, Payment $payment): bool
    {
        return $this->view($user, $payment);
    }

    public function confirm(User $user, Payment $payment): bool
    {
        return $user->is_active && $user->role === UserRole::ADMIN;
    }

    public function reject(User $user, Payment $payment): bool
    {
        return $this->confirm($user, $payment);
    }

    private function canReadAll(User $user): bool
    {
        return $user->is_active && in_array($user->role, [UserRole::ADMIN, UserRole::SUPERVISOR], true);
    }

    private function owns(User $user, Payment $payment): bool
    {
        return $user->is_active && $payment->bill?->customer?->user_id === $user->id;
    }
}
