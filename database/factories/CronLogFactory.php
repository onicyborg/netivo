<?php

namespace Database\Factories;

use App\Models\CronLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CronLog> */
class CronLogFactory extends Factory
{
    protected $model = CronLog::class;

    public function definition(): array
    {
        return ['job' => 'bills:generate', 'status' => 'success', 'summary' => ['created' => 0, 'skipped' => 0, 'failed' => 0], 'started_at' => now(), 'finished_at' => now()];
    }
}
