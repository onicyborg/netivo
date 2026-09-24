<?php

namespace App\Services;

use App\Contracts\Notifier;
use App\Enums\UpgradeStatus;
use App\Exceptions\UpgradeRequestException;
use App\Models\ServiceUpgradeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class ServiceUpgradeWorkflow
{
    public function __construct(private readonly Notifier $notifier, private readonly AuditLogger $audit) {}

    public function approve(ServiceUpgradeRequest $request, User $reviewer): ServiceUpgradeRequest
    {
        return DB::transaction(function () use ($request, $reviewer): ServiceUpgradeRequest {
            $locked = ServiceUpgradeRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== UpgradeStatus::PENDING) {
                throw new UpgradeRequestException('Pengajuan ini sudah diproses.');
            }

            $locked->update(['status' => UpgradeStatus::APPROVED, 'effective_period' => Carbon::now(config('app.timezone'))->startOfMonth()->addMonth()->format('Y-m'), 'reviewed_by' => $reviewer->id, 'reviewed_at' => now()]);
            $this->notifier->upgradeApproved($locked->fresh(['customer.user', 'toService']));
            $this->audit->log('service_upgrade_requests', $locked->id, 'approve', ['status' => UpgradeStatus::PENDING->value], ['status' => UpgradeStatus::APPROVED->value, 'effective_period' => $locked->effective_period], $reviewer);

            return $locked->fresh();
        });
    }

    public function reject(ServiceUpgradeRequest $request, User $reviewer, string $reason): ServiceUpgradeRequest
    {
        return DB::transaction(function () use ($request, $reviewer, $reason): ServiceUpgradeRequest {
            $locked = ServiceUpgradeRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== UpgradeStatus::PENDING) {
                throw new UpgradeRequestException('Pengajuan ini sudah diproses.');
            }

            $locked->update(['status' => UpgradeStatus::REJECTED, 'note' => $reason, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now()]);
            $this->notifier->upgradeRejected($locked->fresh(['customer.user']));
            $this->audit->log('service_upgrade_requests', $locked->id, 'reject', ['status' => UpgradeStatus::PENDING->value], ['status' => UpgradeStatus::REJECTED->value, 'note' => $reason], $reviewer);

            return $locked->fresh();
        });
    }
}
