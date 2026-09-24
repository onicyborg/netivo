<?php

namespace App\Services\Billing;

use App\Enums\BillStatus;
use App\Models\Bill;

class OverdueMarker
{
    public function mark(): int
    {
        return Bill::query()
            ->where('status', BillStatus::BELUM_BAYAR->value)
            ->whereDate('due_date', '<', today())
            ->update(['status' => BillStatus::TERLAMBAT]);
    }
}
