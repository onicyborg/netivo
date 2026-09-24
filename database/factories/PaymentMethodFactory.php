<?php

namespace Database\Factories;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentMethod> */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return ['type' => fake()->randomElement(PaymentMethodType::cases()), 'name' => fake()->company(), 'account_number' => fake()->numerify('############'), 'account_name' => fake()->name(), 'is_active' => true];
    }
}
