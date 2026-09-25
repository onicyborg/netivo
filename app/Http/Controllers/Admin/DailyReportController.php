<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DailyReportException;
use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\User;
use App\Services\DailyReportGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('create', DailyReport::class);
        $reports = DailyReport::query()->with(['creator', 'reviewer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('report_date', $request->date('date')))
            ->latest('report_date')->get();

        return view('admin.reports.index', compact('reports'));
    }

    public function store(Request $request, DailyReportGenerator $generator): RedirectResponse
    {
        $this->authorize('create', DailyReport::class);
        $validated = $request->validate(['report_date' => ['required', 'date']]);
        $result = $generator->generate($validated['report_date'], 'manual', $request->user());

        if ($result['skipped']) {
            return back()->withErrors(['report_date' => 'Laporan untuk tanggal tersebut sudah ada.']);
        }

        return redirect()->route('admin.reports.show', $result['report'])->with('success', 'Laporan harian berhasil dibuat dan dikirim.');
    }

    public function show(DailyReport $report, DailyReportGenerator $generator): View
    {
        $this->authorize('view', $report);

        return view('admin.reports.show', ['report' => $report->load(['creator', 'reviewer']), 'payments' => $generator->paymentsForDate($report->report_date)]);
    }

    public function resend(DailyReport $report, Request $request, DailyReportGenerator $generator): RedirectResponse
    {
        $this->authorize('update', $report);

        try {
            $generator->resend($report, $request->user());
        } catch (DailyReportException $exception) {
            return back()->withErrors(['report' => $exception->getMessage()]);
        }

        return back()->with('success', 'Laporan berhasil dihitung ulang dan dikirim kembali.');
    }
}
