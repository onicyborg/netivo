<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class AdminPolicy
{
    public function manage(User $user): bool
    {
        return $user->is_active && $user->role === UserRole::ADMIN;
    }

    public function viewBilling(User $user): bool
    {
        return $user->is_active && in_array($user->role, [UserRole::ADMIN, UserRole::SUPERVISOR], true);
    }
}
