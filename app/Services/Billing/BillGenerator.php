<?php

namespace App\Services\Billing;

use App\Contracts\Notifier;
use App\Enums\BillStatus;
use App\Enums\CustomerStatus;
use App\Enums\UpgradeStatus;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\ServiceUpgradeRequest;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BillGenerator
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly Notifier $notifier,
    ) {}

    /** @return array{created:int, skipped:int, failed:int} */
    public function generateForPeriod(string $period): array
    {
        $periodDate = $this->periodDate($period);
        $this->applyApprovedUpgrades($period);
        $summary = ['created' => 0, 'skipped' => 0, 'failed' => 0];
        $dueDate = $periodDate->copy()->day((int) $this->settings->get('bill_due_day', 10));

        Customer::query()
            ->where('status', CustomerStatus::AKTIF->value)
            ->with('service')
            ->chunkById(100, function ($customers) use ($period, $dueDate, &$summary): void {
                foreach ($customers as $customer) {
                    try {
                        $created = $this->createBillForCustomer($customer, $period, $dueDate);
                        $summary[$created ? 'created' : 'skipped']++;
                    } catch (\Throwable $exception) {
                        $summary['failed']++;
                    }
                }
            });

        return $summary;
    }

    private function createBillForCustomer(Customer $customer, string $period, Carbon $dueDate): bool
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return DB::transaction(function () use ($customer, $period, $dueDate): bool {
                    $lockedCustomer = Customer::query()->with('service')->lockForUpdate()->findOrFail($customer->id);

                    if ($lockedCustomer->status !== CustomerStatus::AKTIF || ! $lockedCustomer->service) {
                        return false;
                    }

                    $bill = Bill::firstOrCreate(
                        ['customer_id' => $lockedCustomer->id, 'period' => $period],
                        [
                            'bill_number' => $this->nextBillNumber($period),
                            'service_id' => $lockedCustomer->service_id,
                            'amount' => $lockedCustomer->service->price,
                            'due_date' => $dueDate->toDateString(),
                            'status' => BillStatus::BELUM_BAYAR,
                        ],
                    );

                    if (! $bill->wasRecentlyCreated) {
                        return false;
                    }

                    $this->notifier->billCreated($lockedCustomer, $bill);

                    return true;
                });
            } catch (QueryException $exception) {
                if ($attempt === 3) {
                    throw $exception;
                }
            }
        }

        return false;
    }

    private function applyApprovedUpgrades(string $period): void
    {
        ServiceUpgradeRequest::query()
            ->where('status', UpgradeStatus::APPROVED->value)
            ->whereNotNull('effective_period')
            ->where('effective_period', '<=', $period)
            ->orderBy('id')
            ->chunkById(100, function ($requests): void {
                foreach ($requests as $request) {
                    DB::transaction(function () use ($request): void {
                        $upgrade = ServiceUpgradeRequest::query()->lockForUpdate()->find($request->id);

                        if (! $upgrade || $upgrade->status !== UpgradeStatus::APPROVED) {
                            return;
                        }

                        $customer = Customer::query()->lockForUpdate()->find($upgrade->customer_id);

                        if (! $customer) {
                            return;
                        }

                        $customer->update(['service_id' => $upgrade->to_service_id]);
                        $upgrade->update(['status' => UpgradeStatus::APPLIED]);
                        $this->notifier->upgradeApplied($upgrade->fresh(['customer.user', 'toService']));
                    });
                }
            });
    }

    private function nextBillNumber(string $period): string
    {
        $prefix = 'INV-'.str_replace('-', '', $period).'-';

        do {
            $number = $prefix.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Bill::where('bill_number', $number)->exists());

        return $number;
    }

    private function periodDate(string $period): Carbon
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            throw new InvalidArgumentException('Periode harus berformat YYYY-MM.');
        }

        [$year, $month] = array_map('intval', explode('-', $period));

        return Carbon::create($year, $month, 1, 0, 0, 0, config('app.timezone'));
    }
}
