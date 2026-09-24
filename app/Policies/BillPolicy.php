<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function view(User $user, Bill $bill): bool
    {
        return $this->canReadAll($user) || $this->owns($user, $bill);
    }

    public function pay(User $user, Bill $bill): bool
    {
        return $user->is_active && $user->role === UserRole::CUSTOMER && $this->owns($user, $bill);
    }

    private function canReadAll(User $user): bool
    {
        return $user->is_active && in_array($user->role, [UserRole::ADMIN, UserRole::SUPERVISOR], true);
    }

    private function owns(User $user, Bill $bill): bool
    {
        return $user->is_active && $bill->customer?->user_id === $user->id;
    }
}
