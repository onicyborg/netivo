<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index(Request $request): View
    {
        $this->authorize('manage', User::class);

        return view('admin.settings.index', ['settings' => $this->settings->all()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage', User::class);

        $validated = $request->validate([
            'bill_due_day' => ['required', 'integer', 'between:1,28'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['required', 'string', 'max:1000'],
            'company_phone' => ['required', 'string', 'max:30'],
        ], [
            'bill_due_day.between' => 'Jatuh tempo harus antara tanggal 1 dan 28.',
        ]);

        $this->settings->updateMany($validated);

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
