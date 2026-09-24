<?php

namespace App\Services;

use App\Contracts\Notifier;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Receipt;
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
}
