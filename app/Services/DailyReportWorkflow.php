<?php

namespace App\Services;

use App\Contracts\Notifier;
use App\Enums\ReportStatus;
use App\Exceptions\DailyReportException;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class DailyReportWorkflow
{
    public function __construct(private readonly Notifier $notifier, private readonly AuditLogger $audit) {}

    public function requestRevision(DailyReport $report, User $reviewer, string $note): DailyReport
    {
        return DB::transaction(function () use ($report, $reviewer, $note): DailyReport {
            $locked = DailyReport::query()->lockForUpdate()->findOrFail($report->id);
            if ($locked->status !== ReportStatus::DIKIRIM) {
                throw new DailyReportException('Hanya laporan terkirim yang dapat diminta revisi.');
            }

            $locked->update(['status' => ReportStatus::REVISI, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now(), 'revision_note' => $note]);
            $this->notifier->reportRevisionRequested($locked->fresh());
            $this->audit->log('daily_reports', $locked->id, 'revise', ['status' => ReportStatus::DIKIRIM->value], ['status' => ReportStatus::REVISI->value, 'revision_note' => $note], $reviewer);

            return $locked->fresh();
        });
    }

    public function archive(DailyReport $report, User $reviewer): DailyReport
    {
        return DB::transaction(function () use ($report, $reviewer): DailyReport {
            $locked = DailyReport::query()->lockForUpdate()->findOrFail($report->id);
            if ($locked->status !== ReportStatus::DIKIRIM) {
                throw new DailyReportException('Hanya laporan terkirim yang dapat diarsipkan.');
            }

            $locked->update(['status' => ReportStatus::DIARSIPKAN, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now(), 'archived_at' => now()]);
            $this->notifier->reportArchived($locked->fresh());
            $this->audit->log('daily_reports', $locked->id, 'archive', ['status' => ReportStatus::DIKIRIM->value], ['status' => ReportStatus::DIARSIPKAN->value], $reviewer);

            return $locked->fresh();
        });
    }
}
