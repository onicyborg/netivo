<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ServiceUpgradeRequest;
use App\Models\User;

class ServiceUpgradeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->admin($user);
    }

    public function view(User $user, ServiceUpgradeRequest $request): bool
    {
        return $this->admin($user) || ($user->is_active && $user->role === UserRole::CUSTOMER && $request->customer?->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->role === UserRole::CUSTOMER;
    }

    public function review(User $user, ServiceUpgradeRequest $request): bool
    {
        return $this->admin($user) && $request->status === \App\Enums\UpgradeStatus::PENDING;
    }

    private function admin(User $user): bool
    {
        return $user->is_active && $user->role === UserRole::ADMIN;
    }
}
