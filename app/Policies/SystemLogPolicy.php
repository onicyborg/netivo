<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class SystemLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && in_array($user->role, [UserRole::ADMIN, UserRole::SUPERVISOR], true);
    }
}
