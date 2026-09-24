<?php

namespace App\Services;

use App\Contracts\Notifier;
use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentVerificationException;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PaymentVerificationService
{
    public function __construct(private readonly Notifier $notifier, private readonly AuditLogger $audit) {}

    public function confirm(Payment $payment, User $verifier): Receipt
    {
        return DB::transaction(function () use ($payment, $verifier): Receipt {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $this->ensurePending($lockedPayment);
            $bill = Bill::query()->lockForUpdate()->findOrFail($lockedPayment->bill_id);

            $lockedPayment->update([
                'status' => PaymentStatus::CONFIRMED,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);
            $bill->update(['status' => BillStatus::LUNAS, 'paid_at' => now()]);
            $this->audit->log('payments', $lockedPayment->id, 'confirm', ['status' => PaymentStatus::PENDING->value], ['status' => PaymentStatus::CONFIRMED->value, 'verified_by' => $verifier->id], $verifier);

            $receipt = $this->createReceipt($lockedPayment);
            $this->notifier->paymentConfirmed($lockedPayment->fresh(['bill.customer']), $receipt);

            return $receipt;
        });
    }

    public function reject(Payment $payment, User $verifier, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $verifier, $reason): Payment {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $this->ensurePending($lockedPayment);
            $bill = Bill::query()->lockForUpdate()->findOrFail($lockedPayment->bill_id);
            $billStatus = $bill->due_date->lt(today()) ? BillStatus::TERLAMBAT : BillStatus::BELUM_BAYAR;

            $lockedPayment->update([
                'status' => PaymentStatus::REJECTED,
                'rejection_reason' => $reason,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);
            $bill->update(['status' => $billStatus, 'paid_at' => null]);
            $this->audit->log('payments', $lockedPayment->id, 'reject', ['status' => PaymentStatus::PENDING->value], ['status' => PaymentStatus::REJECTED->value, 'rejection_reason' => $reason], $verifier);
            $this->notifier->paymentRejected($lockedPayment->fresh(['bill.customer']));

            return $lockedPayment->fresh(['bill.customer']);
        });
    }

    private function ensurePending(Payment $payment): void
    {
        if ($payment->status !== PaymentStatus::PENDING) {
            throw new PaymentVerificationException('Pembayaran ini sudah diverifikasi sebelumnya.');
        }
    }

    private function createReceipt(Payment $payment): Receipt
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            try {
                return Receipt::create([
                    'payment_id' => $payment->id,
                    'receipt_number' => sprintf('RCP-%s-%04d', now()->format('Ymd'), random_int(1, 9999)),
                    'issued_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if ($attempt === 9) {
                    throw $exception;
                }
            }
        }

        throw new PaymentVerificationException('Nomor kuitansi gagal dibuat.');
    }
}
