<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Customer;
use App\Contracts\Notifier;

class NullNotifier implements Notifier
{
    public function billCreated(Customer $customer, Bill $bill): void
    {
        // Implementasi notifikasi in-app akan menggantikan stub ini di Fase 7.
    }
}
