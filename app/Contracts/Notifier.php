<?php

namespace App\Contracts;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Notification;
use App\Enums\UserRole;

interface Notifier
{
    public function notifyUser(User $user, string $type, string $title, string $message, ?string $url = null): Notification;

    public function notifyRole(UserRole|string $role, string $type, string $title, string $message, ?string $url = null): int;

    public function billCreated(Customer $customer, Bill $bill): void;

    public function paymentSubmitted(Payment $payment): void;

    public function paymentConfirmed(Payment $payment, Receipt $receipt): void;

    public function paymentRejected(Payment $payment): void;
}
