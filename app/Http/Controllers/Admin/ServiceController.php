<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', User::class);

        return view('admin.services.index', ['services' => Service::withCount(['customers', 'bills'])->latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', User::class);
        Service::create($this->validated($request));

        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $service->update($this->validated($request, $service));

        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('manage', User::class);

        if ($service->customers()->exists() || $service->bills()->exists()) {
            return back()->with('error', 'Layanan yang sudah dipakai customer atau tagihan tidak dapat dihapus. Nonaktifkan layanan tersebut.');
        }

        DB::transaction(fn () => $service->delete());

        return back()->with('success', 'Layanan berhasil dihapus.');
    }

    private function validated(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('services', 'name')->ignore($service?->id)],
            'speed_mbps' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
