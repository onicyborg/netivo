<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', User::class);

        return view('admin.customers.index', [
            'customers' => Customer::with(['user', 'service'])->latest()->get(),
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $validated = $this->validated($request);

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => UserRole::CUSTOMER,
                'is_active' => $validated['status'] === CustomerStatus::AKTIF->value,
            ]);

            Customer::create([
                'user_id' => $user->id,
                'service_id' => $validated['service_id'],
                'customer_number' => $this->nextCustomerNumber(),
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'registered_at' => today(),
                'status' => $validated['status'],
            ]);

            // TODO Fase 4: panggil BillGenerator untuk membuat tagihan periode berjalan.
        });

        return back()->with('success', 'Customer berhasil ditambahkan.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $validated = $this->validated($request, $customer);

        DB::transaction(function () use ($validated, $customer): void {
            $customer->update([
                'service_id' => $validated['service_id'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'],
            ]);
            $customer->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['status'] === CustomerStatus::AKTIF->value,
            ]);
        });

        return back()->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('manage', User::class);

        if ($customer->bills()->exists()) {
            return back()->with('error', 'Customer yang sudah memiliki tagihan tidak dapat dihapus. Nonaktifkan customer tersebut.');
        }

        DB::transaction(function () use ($customer): void {
            $user = $customer->user;
            $customer->delete();
            $user->delete();
        });

        return back()->with('success', 'Customer berhasil dihapus.');
    }

    public function resetPassword(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        $customer->user->update(['password' => $validated['password']]);

        return back()->with('success', 'Password customer berhasil direset.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer?->user_id)],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'service_id' => ['required', 'uuid', 'exists:services,id'],
            'status' => ['required', Rule::in([CustomerStatus::AKTIF->value, CustomerStatus::NONAKTIF->value])],
        ];

        if ($customer === null) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        return $request->validate($rules, [
            'email.unique' => 'Email tersebut sudah digunakan.',
            'service_id.exists' => 'Layanan yang dipilih tidak ditemukan.',
        ]);
    }

    private function nextCustomerNumber(): string
    {
        do {
            $number = 'CUST-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Customer::where('customer_number', $number)->exists());

        return $number;
    }
}
