<?php

namespace App\Services\Billing;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Services\AuditLogger;

class OverdueMarker
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function mark(): int
    {
        $bills = Bill::query()
            ->where('status', BillStatus::BELUM_BAYAR->value)
            ->whereDate('due_date', '<', today())
            ->get();
        foreach ($bills as $bill) {
            $bill->update(['status' => BillStatus::TERLAMBAT]);
            $this->audit->log('bills', $bill->id, 'updated', ['status' => BillStatus::BELUM_BAYAR->value], ['status' => BillStatus::TERLAMBAT->value]);
        }

        return $bills->count();
    }
}
