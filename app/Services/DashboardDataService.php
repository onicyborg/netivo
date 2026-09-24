<?php

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReportStatus;
use App\Enums\UpgradeStatus;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\DailyReport;
use App\Models\Payment;
use App\Models\ServiceUpgradeRequest;
use App\Models\User;

class DashboardDataService
{
    /** @return array<string, mixed> */
    public function forAdmin(): array
    {
        return $this->forOperations('admin');
    }

    /** @return array<string, mixed> */
    public function forSupervisor(): array
    {
        return $this->forOperations('supervisor');
    }

    /** @return array<string, mixed> */
    public function forCustomer(User $user): array
    {
        $period = now()->format('Y-m');
        $customer = Customer::query()->with('service')->where('user_id', $user->id)->first();
        if (! $customer) {
            return ['customer' => null, 'currentBill' => null, 'overdueBills' => collect(), 'activeService' => null, 'upgrade' => null, 'payments' => collect(), 'notifications' => collect()];
        }

        return [
            'customer' => $customer,
            'currentBill' => Bill::query()->with('service')->where('customer_id', $customer->id)->where('period', $period)->first(),
            'overdueBills' => Bill::query()->where('customer_id', $customer->id)->where('status', BillStatus::TERLAMBAT->value)->orderBy('due_date')->get(),
            'activeService' => $customer->service,
            'upgrade' => ServiceUpgradeRequest::query()->with('toService')->where('customer_id', $customer->id)->latest()->first(),
            'payments' => Payment::query()->with(['bill.service', 'paymentMethod'])->whereHas('bill', fn ($query) => $query->where('customer_id', $customer->id))->latest()->limit(5)->get(),
            'notifications' => $user->notifications()->latest()->limit(5)->get(),
        ];
    }

    /** @return array<string, mixed> */
    private function forOperations(string $role): array
    {
        $period = now()->format('Y-m');
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $statusCounts = Bill::query()->where('period', $period)->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $revenueToday = Payment::query()->where('status', PaymentStatus::CONFIRMED->value)->whereDate('verified_at', $today)->sum('amount');
        $revenueMonth = Payment::query()->where('status', PaymentStatus::CONFIRMED->value)->whereBetween('verified_at', [$monthStart, $monthEnd])->sum('amount');

        $data = [
            'period' => $period,
            'billTotal' => Bill::query()->where('period', $period)->count(),
            'billStatuses' => [
                'lunas' => (int) ($statusCounts[BillStatus::LUNAS->value] ?? 0),
                'belum_bayar' => (int) ($statusCounts[BillStatus::BELUM_BAYAR->value] ?? 0),
                'terlambat' => (int) ($statusCounts[BillStatus::TERLAMBAT->value] ?? 0),
                'menunggu_verifikasi' => (int) ($statusCounts[BillStatus::MENUNGGU_VERIFIKASI->value] ?? 0),
            ],
            'revenueToday' => $revenueToday,
            'revenueMonth' => $revenueMonth,
            'transactions' => Payment::query()->with(['bill.customer.user', 'bill.service', 'paymentMethod'])->latest()->limit(10)->get(),
        ];

        if ($role === 'admin') {
            $data['pendingPayments'] = Payment::query()->where('status', PaymentStatus::PENDING->value)->count();
            $data['pendingUpgrades'] = ServiceUpgradeRequest::query()->where('status', UpgradeStatus::PENDING->value)->count();
        } else {
            $data['pendingReports'] = DailyReport::query()->where('status', ReportStatus::DIKIRIM->value)->latest('report_date')->limit(5)->get();
        }

        return $data;
    }
}
