<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', User::class);

        return view('admin.payment-methods.index', ['paymentMethods' => PaymentMethod::withCount('payments')->latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', User::class);
        PaymentMethod::create($this->validated($request));

        return back()->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $paymentMethod->update($this->validated($request, $paymentMethod));

        return back()->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('manage', User::class);

        if ($paymentMethod->payments()->exists()) {
            return back()->with('error', 'Metode pembayaran yang sudah dipakai tidak dapat dihapus. Nonaktifkan metode tersebut.');
        }

        DB::transaction(fn () => $paymentMethod->delete());

        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    private function validated(Request $request, ?PaymentMethod $paymentMethod = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['transfer', 'ewallet'])],
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
