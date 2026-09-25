<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DailyReport;
use App\Models\User;

class DailyReportPolicy
{
    public function view(User $user, ?DailyReport $report = null): bool
    {
        return $user->is_active && in_array($user->role, [UserRole::ADMIN, UserRole::SUPERVISOR], true);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->role === UserRole::ADMIN;
    }

    public function update(User $user, DailyReport $report): bool
    {
        return $this->create($user) && $report->status !== \App\Enums\ReportStatus::DIARSIPKAN;
    }

    public function review(User $user, DailyReport $report): bool
    {
        return $user->is_active && $user->role === UserRole::SUPERVISOR && $report->status !== \App\Enums\ReportStatus::DIARSIPKAN;
    }
}
