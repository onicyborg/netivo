<?php

namespace App\Contracts;

use App\Models\Bill;
use App\Models\Customer;

interface Notifier
{
    public function billCreated(Customer $customer, Bill $bill): void;
}
