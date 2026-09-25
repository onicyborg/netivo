<?php

namespace App\Services;

use App\Contracts\Notifier;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\DailyReport;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\ServiceUpgradeRequest;
use App\Models\User;

class DatabaseNotifier implements Notifier
{
    public function notifyUser(User $user, string $type, string $title, string $message, ?string $url = null): Notification
    {
        return $user->notifications()->create(compact('type', 'title', 'message', 'url'));
    }

    public function notifyRole(UserRole|string $role, string $type, string $title, string $message, ?string $url = null): int
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;
        $count = 0;
        User::query()->where('role', $roleValue)->where('is_active', true)->chunkById(100, function ($users) use ($type, $title, $message, $url, &$count): void {
            foreach ($users as $user) {
                $this->notifyUser($user, $type, $title, $message, $url);
                $count++;
            }
        });

        return $count;
    }

    public function billCreated(Customer $customer, Bill $bill): void
    {
        $customer->loadMissing('user');
        $this->notifyUser($customer->user, 'bill_created', 'Tagihan baru', 'Tagihan '.$bill->bill_number.' periode '.$bill->period.' telah diterbitkan.', route('customer.bills.show', $bill));
    }

    public function paymentSubmitted(Payment $payment): void
    {
        $payment->loadMissing('bill.customer');
        $this->notifyRole(UserRole::ADMIN, 'payment_submitted', 'Bukti pembayaran baru', 'Bukti pembayaran untuk tagihan '.$payment->bill->bill_number.' menunggu verifikasi.', route('admin.payments.show', $payment));
    }

    public function paymentConfirmed(Payment $payment, Receipt $receipt): void
    {
        $payment->loadMissing('bill.customer.user');
        $this->notifyUser($payment->bill->customer->user, 'payment_confirmed', 'Pembayaran dikonfirmasi', 'Pembayaran untuk tagihan '.$payment->bill->bill_number.' telah dikonfirmasi.', route('customer.receipts.show', $receipt));
    }

    public function paymentRejected(Payment $payment): void
    {
        $payment->loadMissing('bill.customer.user');
        $this->notifyUser($payment->bill->customer->user, 'payment_rejected', 'Pembayaran ditolak', 'Pembayaran untuk tagihan '.$payment->bill->bill_number.' ditolak: '.$payment->rejection_reason, route('customer.bills.show', $payment->bill));
    }

    public function reportSent(DailyReport $report): void
    {
        $this->notifyRole(UserRole::SUPERVISOR, 'report_sent', 'Laporan harian dikirim', 'Laporan harian tanggal '.$report->report_date->format('d M Y').' menunggu review.', route('supervisor.reports.show', $report));
    }

    public function reportRevisionRequested(DailyReport $report): void
    {
        $this->notifyRole(UserRole::ADMIN, 'report_revision', 'Laporan perlu revisi', 'Laporan harian tanggal '.$report->report_date->format('d M Y').' diminta untuk diperbaiki.', route('admin.reports.show', $report));
    }

    public function reportArchived(DailyReport $report): void
    {
        $this->notifyRole(UserRole::ADMIN, 'report_archived', 'Laporan disetujui dan diarsipkan', 'Laporan harian tanggal '.$report->report_date->format('d M Y').' telah disetujui supervisor.', route('admin.reports.show', $report));
    }

    public function upgradeRequested(ServiceUpgradeRequest $request): void
    {
        $request->loadMissing('customer.user', 'toService');
        $this->notifyRole(UserRole::ADMIN, 'upgrade_requested', 'Pengajuan perubahan layanan baru', 'Customer '.$request->customer->customer_number.' mengajukan perubahan layanan ke '.$request->toService->name.'.', route('admin.upgrades.show', $request));
    }

    public function upgradeApproved(ServiceUpgradeRequest $request): void
    {
        $request->loadMissing('customer.user', 'toService');
        $this->notifyUser($request->customer->user, 'upgrade_approved', 'Perubahan layanan disetujui', 'Perubahan layanan ke '.$request->toService->name.' dijadwalkan mulai periode '.$request->effective_period.'.', route('customer.services.index'));
    }

    public function upgradeRejected(ServiceUpgradeRequest $request): void
    {
        $request->loadMissing('customer.user');
        $this->notifyUser($request->customer->user, 'upgrade_rejected', 'Perubahan layanan ditolak', 'Pengajuan perubahan layanan Anda ditolak: '.$request->note, route('customer.services.index'));
    }

    public function upgradeApplied(ServiceUpgradeRequest $request): void
    {
        $request->loadMissing('customer.user', 'toService');
        $this->notifyUser($request->customer->user, 'upgrade_applied', 'Perubahan layanan diterapkan', 'Layanan Anda sekarang adalah '.$request->toService->name.' untuk periode '.$request->effective_period.'.', route('customer.services.index'));
    }
}
