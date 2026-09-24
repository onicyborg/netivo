<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true), 'speed_mbps' => fake()->randomElement([10, 20, 50, 100]), 'price' => fake()->randomElement([150000, 250000, 400000]), 'description' => fake()->sentence(), 'is_active' => true];
    }
}
