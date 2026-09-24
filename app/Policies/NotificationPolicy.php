<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): bool
    {
        return $user->is_active && $notification->user_id === $user->id;
    }

    public function mark(User $user, Notification $notification): bool
    {
        return $this->view($user, $notification);
    }
}
