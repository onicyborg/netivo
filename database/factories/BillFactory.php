<?php

namespace Database\Factories;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Bill> */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        $period = now()->format('Y-m');
        return ['bill_number' => 'INV-'.now()->format('Ym').'-'.fake()->unique()->numerify('######'), 'customer_id' => Customer::factory(), 'service_id' => Service::factory(), 'period' => $period, 'amount' => 250000, 'due_date' => now()->day(10), 'status' => BillStatus::BELUM_BAYAR, 'paid_at' => null];
    }
}
