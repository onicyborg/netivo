<?php

namespace App\Http\Controllers\Customer;

use App\Enums\CustomerStatus;
use App\Enums\UpgradeStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceUpgradeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('create', ServiceUpgradeRequest::class);
        $customer = Customer::query()->with('service')->where('user_id', $request->user()->id)->firstOrFail();
        $pending = $customer->serviceUpgradeRequests()->where('status', UpgradeStatus::PENDING)->with('toService')->latest()->first();
        $lastRequest = $customer->serviceUpgradeRequests()->with(['fromService', 'toService'])->latest()->first();
        $services = Service::query()->where('is_active', true)->where('id', '<>', $customer->service_id)->orderBy('speed_mbps')->orderBy('name')->get();

        return view('customer/services/index', compact('customer', 'pending', 'lastRequest', 'services'));
    }

    public function store(Request $request, \App\Contracts\Notifier $notifier): RedirectResponse
    {
        $this->authorize('create', ServiceUpgradeRequest::class);
        $validated = $request->validate(['to_service_id' => ['required', 'uuid', 'exists:services,id'], 'note' => ['nullable', 'string', 'max:2000']]);

        $error = null;
        DB::transaction(function () use ($request, $validated, $notifier, &$error): void {
            $customer = Customer::query()->lockForUpdate()->with('service')->where('user_id', $request->user()->id)->firstOrFail();
            if ($customer->serviceUpgradeRequests()->where('status', UpgradeStatus::PENDING)->exists()) {
                $error = 'Masih ada satu pengajuan perubahan layanan yang menunggu keputusan.';
                return;
            }

            $service = Service::query()->where('is_active', true)->find($validated['to_service_id']);
            if (! $service) {
                $error = 'Layanan tujuan tidak aktif atau tidak ditemukan.';
                return;
            }
            if ($service->id === $customer->service_id) {
                $error = 'Pilih layanan yang berbeda dari layanan saat ini.';
                return;
            }

            $upgrade = $customer->serviceUpgradeRequests()->create(['from_service_id' => $customer->service_id, 'to_service_id' => $service->id, 'status' => UpgradeStatus::PENDING, 'note' => $validated['note'] ?? null]);
            $notifier->upgradeRequested($upgrade);
        });
        if ($error) {
            return back()->withErrors(['to_service_id' => $error]);
        }

        return back()->with('success', 'Pengajuan upgrade/downgrade berhasil dikirim ke admin.');
    }
}
