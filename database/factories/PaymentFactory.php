<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return ['bill_id' => Bill::factory(), 'payment_method_id' => PaymentMethod::factory(), 'amount' => 250000, 'paid_date' => today(), 'sender_name' => fake()->name(), 'note' => null, 'proof_path' => 'payments/'.fake()->uuid().'.jpg', 'status' => PaymentStatus::PENDING, 'rejection_reason' => null, 'verified_by' => null, 'verified_at' => null];
    }
}
