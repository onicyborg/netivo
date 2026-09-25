<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UpgradeRequestException;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceUpgradeRequest;
use App\Services\ServiceUpgradeWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceUpgradeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceUpgradeRequest::class);
        $upgrades = ServiceUpgradeRequest::query()
            ->with(['customer.user', 'fromService', 'toService', 'reviewer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('from_service_id'), fn ($query) => $query->where('from_service_id', $request->string('from_service_id')))
            ->when($request->filled('to_service_id'), fn ($query) => $query->where('to_service_id', $request->string('to_service_id')))
            ->latest()
            ->get();
        $services = Service::query()->orderBy('speed_mbps')->orderBy('name')->get();

        return view('admin.upgrades.index', compact('upgrades', 'services'));
    }

    public function show(ServiceUpgradeRequest $upgrade): View
    {
        $this->authorize('view', $upgrade);

        return view('admin.upgrades.show', ['upgrade' => $upgrade->load(['customer.user', 'fromService', 'toService', 'reviewer'])]);
    }

    public function approve(ServiceUpgradeRequest $upgrade, Request $request, ServiceUpgradeWorkflow $workflow): RedirectResponse
    {
        $this->authorize('review', $upgrade);

        try {
            $workflow->approve($upgrade, $request->user());
        } catch (UpgradeRequestException $exception) {
            return back()->withErrors(['upgrade' => $exception->getMessage()]);
        }

        return back()->with('success', 'Pengajuan disetujui dan dijadwalkan untuk periode berikutnya.');
    }

    public function reject(Request $request, ServiceUpgradeRequest $upgrade, ServiceUpgradeWorkflow $workflow): RedirectResponse
    {
        $this->authorize('review', $upgrade);
        $validated = $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']], ['rejection_reason.required' => 'Alasan penolakan wajib diisi.']);

        try {
            $workflow->reject($upgrade, $request->user(), $validated['rejection_reason']);
        } catch (UpgradeRequestException $exception) {
            return back()->withErrors(['upgrade' => $exception->getMessage()]);
        }

        return back()->with('success', 'Pengajuan upgrade ditolak.');
    }
}
