<?php

namespace App\Services;

use App\Contracts\Notifier;
use App\Enums\PaymentStatus;
use App\Enums\ReportStatus;
use App\Exceptions\DailyReportException;
use App\Models\DailyReport;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class DailyReportGenerator
{
    public function __construct(private readonly Notifier $notifier, private readonly AuditLogger $audit) {}

    /** @return array{report: DailyReport, created: bool, skipped: bool} */
    public function generate(string|Carbon $date, string $source = 'manual', ?User $creator = null): array
    {
        $reportDate = $this->date($date);

        return DB::transaction(function () use ($reportDate, $source, $creator): array {
            $existing = DailyReport::query()->whereDate('report_date', $reportDate)->lockForUpdate()->first();
            if ($existing) {
                return ['report' => $existing, 'created' => false, 'skipped' => true];
            }

            try {
                $report = DailyReport::create(array_merge($this->summary($reportDate), [
                    'report_date' => $reportDate->toDateString(),
                    'source' => $source,
                    'status' => ReportStatus::DIKIRIM,
                    'created_by' => $creator?->id,
                    'sent_at' => now(),
                ]));
            } catch (QueryException $exception) {
                $report = DailyReport::query()->whereDate('report_date', $reportDate)->first();
                if (! $report) {
                    throw $exception;
                }

                return ['report' => $report, 'created' => false, 'skipped' => true];
            }

            $this->notifier->reportSent($report);
            if ($source === 'manual') {
                $this->audit->log('daily_reports', $report->id, 'generate_manual', [], ['report_date' => $report->report_date->toDateString()], $creator);
            }

            return ['report' => $report, 'created' => true, 'skipped' => false];
        });
    }

    public function resend(DailyReport $report, User $creator): DailyReport
    {
        return DB::transaction(function () use ($report, $creator): DailyReport {
            $locked = DailyReport::query()->lockForUpdate()->findOrFail($report->id);
            if ($locked->status !== ReportStatus::REVISI) {
                throw new DailyReportException('Laporan hanya dapat dikirim ulang saat berstatus revisi.');
            }

            $locked->update(array_merge($this->summary($locked->report_date), [
                'status' => ReportStatus::DIKIRIM,
                'source' => 'manual',
                'created_by' => $creator->id,
                'sent_at' => now(),
                'reviewed_by' => null,
                'reviewed_at' => null,
                'archived_at' => null,
            ]));
            $this->notifier->reportSent($locked->fresh());
            $this->audit->log('daily_reports', $locked->id, 'resend', [], ['report_date' => $locked->report_date->toDateString()], $creator);

            return $locked->fresh();
        });
    }

    /** @return array<string, int|float> */
    public function summary(string|Carbon $date): array
    {
        $reportDate = $this->date($date);
        $confirmed = Payment::query()->where('status', PaymentStatus::CONFIRMED)->whereDate('verified_at', $reportDate);
        $rejected = Payment::query()->where('status', PaymentStatus::REJECTED)->whereDate('verified_at', $reportDate);
        $pending = Payment::query()->where('status', PaymentStatus::PENDING)->whereDate('paid_date', $reportDate);

        return [
            'total_confirmed_count' => (clone $confirmed)->count(),
            'total_confirmed_amount' => (float) (clone $confirmed)->sum('amount'),
            'rejected_count' => (clone $rejected)->count(),
            'pending_count' => (clone $pending)->count(),
        ];
    }

    public function paymentsForDate(string|Carbon $date)
    {
        $reportDate = $this->date($date);

        return Payment::query()->with(['bill.customer.user', 'bill.service', 'paymentMethod'])->where(function ($query) use ($reportDate): void {
            $query->where(function ($nested) use ($reportDate): void {
                $nested->whereIn('status', [PaymentStatus::CONFIRMED->value, PaymentStatus::REJECTED->value])->whereDate('verified_at', $reportDate);
            })->orWhere(function ($nested) use ($reportDate): void {
                $nested->where('status', PaymentStatus::PENDING->value)->whereDate('paid_date', $reportDate);
            });
        })->latest('paid_date')->get();
    }

    private function date(string|Carbon $date): Carbon
    {
        return $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date, config('app.timezone'))->startOfDay();
    }
}
