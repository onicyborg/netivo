<?php

namespace App\Http\Controllers\Supervisor;

use App\Exceptions\DailyReportException;
use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Services\DailyReportGenerator;
use App\Services\DailyReportWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view', DailyReport::class);
        $reports = DailyReport::query()->with(['creator', 'reviewer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('report_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('report_date', '<=', $request->date('to')))
            ->latest('report_date')->paginate(15)->withQueryString();

        return view('supervisor.reports.index', compact('reports'));
    }

    public function show(DailyReport $report, DailyReportGenerator $generator): View
    {
        $this->authorize('view', $report);

        return view('supervisor.reports.show', ['report' => $report->load(['creator', 'reviewer']), 'payments' => $generator->paymentsForDate($report->report_date)]);
    }

    public function archive(DailyReport $report, Request $request, DailyReportWorkflow $workflow): RedirectResponse
    {
        $this->authorize('review', $report);

        try {
            $workflow->archive($report, $request->user());
        } catch (DailyReportException $exception) {
            return back()->withErrors(['report' => $exception->getMessage()]);
        }

        return back()->with('success', 'Laporan berhasil disetujui dan diarsipkan.');
    }

    public function revision(Request $request, DailyReport $report, DailyReportWorkflow $workflow): RedirectResponse
    {
        $this->authorize('review', $report);
        $validated = $request->validate(['revision_note' => ['required', 'string', 'max:2000']], ['revision_note.required' => 'Catatan revisi wajib diisi.']);

        try {
            $workflow->requestRevision($report, $request->user(), $validated['revision_note']);
        } catch (DailyReportException $exception) {
            return back()->withErrors(['report' => $exception->getMessage()]);
        }

        return back()->with('success', 'Laporan ditandai untuk revisi.');
    }
}
