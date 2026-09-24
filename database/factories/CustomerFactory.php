<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return ['user_id' => User::factory()->state(['role' => UserRole::CUSTOMER]), 'service_id' => Service::factory(), 'customer_number' => 'CUST-'.fake()->unique()->numerify('######'), 'phone' => fake()->phoneNumber(), 'address' => fake()->address(), 'registered_at' => today(), 'status' => CustomerStatus::AKTIF];
    }
}
