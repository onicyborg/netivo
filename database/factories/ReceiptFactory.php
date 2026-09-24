<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Receipt> */
class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition(): array
    {
        return ['payment_id' => Payment::factory(), 'receipt_number' => 'RCP-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'), 'issued_at' => now()];
    }
}
