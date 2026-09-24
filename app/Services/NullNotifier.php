<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Contracts\Notifier;

class NullNotifier implements Notifier
{
    public function billCreated(Customer $customer, Bill $bill): void
    {
        // Implementasi notifikasi in-app akan menggantikan stub ini di Fase 7.
    }

    public function paymentSubmitted(Payment $payment): void
    {
        // Implementasi notifikasi ke admin akan menggantikan stub ini di Fase 7.
    }

    public function paymentConfirmed(Payment $payment, Receipt $receipt): void
    {
        // Implementasi notifikasi ke customer akan menggantikan stub ini di Fase 7.
    }

    public function paymentRejected(Payment $payment): void
    {
        // Implementasi notifikasi ke customer akan menggantikan stub ini di Fase 7.
    }
}
