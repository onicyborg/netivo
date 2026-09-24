<?php

namespace App\Contracts;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;

interface Notifier
{
    public function billCreated(Customer $customer, Bill $bill): void;

    public function paymentSubmitted(Payment $payment): void;

    public function paymentConfirmed(Payment $payment, Receipt $receipt): void;

    public function paymentRejected(Payment $payment): void;
}
