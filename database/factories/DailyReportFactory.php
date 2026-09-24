<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DailyReport> */
class DailyReportFactory extends Factory
{
    protected $model = DailyReport::class;

    public function definition(): array
    {
        return ['report_date' => today(), 'source' => 'manual', 'status' => ReportStatus::DIKIRIM, 'total_confirmed_count' => 0, 'total_confirmed_amount' => 0, 'rejected_count' => 0, 'pending_count' => 0, 'created_by' => User::factory(), 'reviewed_by' => null, 'revision_note' => null, 'sent_at' => now(), 'reviewed_at' => null, 'archived_at' => null];
    }
}
