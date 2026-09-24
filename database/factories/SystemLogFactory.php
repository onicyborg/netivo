<?php

namespace Database\Factories;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SystemLog> */
class SystemLogFactory extends Factory
{
    protected $model = SystemLog::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'table_name' => 'users', 'record_id' => null, 'action' => 'created', 'method' => 'POST', 'url' => '/users', 'ip_address' => '127.0.0.1', 'old_values' => null, 'new_values' => ['phase' => 1]];
    }
}
