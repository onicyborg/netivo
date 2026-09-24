<?php

namespace Database\Factories;

use App\Enums\UpgradeStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceUpgradeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ServiceUpgradeRequest> */
class ServiceUpgradeRequestFactory extends Factory
{
    protected $model = ServiceUpgradeRequest::class;

    public function definition(): array
    {
        return ['customer_id' => Customer::factory(), 'from_service_id' => Service::factory(), 'to_service_id' => Service::factory(), 'status' => UpgradeStatus::PENDING, 'effective_period' => null, 'note' => fake()->sentence(), 'reviewed_by' => null, 'reviewed_at' => null];
    }
}
