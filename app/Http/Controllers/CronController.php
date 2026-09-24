<?php

namespace App\Http\Controllers;

use App\Models\CronLog;
use App\Services\Billing\BillGenerator;
use App\Services\Billing\OverdueMarker;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class CronController extends Controller
{
    public function generateBills(BillGenerator $generator): JsonResponse
    {
        $period = now()->format('Y-m');

        return $this->run('bills:generate', function () use ($generator, $period): array {
            return $generator->generateForPeriod($period);
        });
    }

    public function markOverdue(OverdueMarker $marker): JsonResponse
    {
        return $this->run('bills:mark-overdue', function () use ($marker): array {
            return ['created' => $marker->mark(), 'skipped' => 0, 'failed' => 0];
        });
    }

    /** @param callable(): array<string, int> $callback */
    private function run(string $job, callable $callback): JsonResponse
    {
        $startedAt = Carbon::now();
        $status = 'success';
        $httpStatus = 200;
        $summary = ['created' => 0, 'skipped' => 0, 'failed' => 0];

        try {
            $summary = $callback();
            $status = ($summary['failed'] ?? 0) > 0
                ? (($summary['created'] ?? 0) > 0 || ($summary['skipped'] ?? 0) > 0 ? 'partial' : 'failed')
                : 'success';
        } catch (\Throwable $exception) {
            $status = 'failed';
            $httpStatus = 500;
            $summary['failed'] = 1;
        }

        CronLog::create([
            'job' => $job,
            'status' => $status,
            'summary' => $summary,
            'started_at' => $startedAt,
            'finished_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => $status === 'success' ? 'Cron berhasil dijalankan.' : 'Cron selesai dengan status '.$status.'.',
            'data' => $summary,
        ], $httpStatus);
    }
}
