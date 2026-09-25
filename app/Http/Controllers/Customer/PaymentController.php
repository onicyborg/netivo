<?php

namespace App\Http\Controllers\Customer;

use App\Contracts\Notifier;
use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $customer = Customer::where('user_id', $request->user()->id)->first();
        $payments = $customer
            ? Payment::with(['bill.service', 'paymentMethod'])->whereHas('bill', fn ($query) => $query->where('customer_id', $customer->id))->latest()->get()
            : collect();
        $bills = $customer ? $customer->bills()->with('service')->latest('period')->get() : collect();

        return view('customer.payments.index', compact('payments', 'bills'));
    }

    public function store(Request $request, Bill $bill, Notifier $notifier): RedirectResponse
    {
        $this->authorize('pay', $bill);
        $validated = $request->validate([
            'payment_method_id' => ['required', 'uuid', Rule::exists('payment_methods', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'paid_date' => ['required', 'date', 'before_or_equal:today'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:2048'],
        ], [
            'payment_method_id.exists' => 'Metode pembayaran tidak aktif atau tidak ditemukan.',
            'paid_date.before_or_equal' => 'Tanggal bayar tidak boleh melebihi hari ini.',
            'proof.required' => 'Bukti pembayaran wajib diunggah.',
            'proof.mimes' => 'Bukti pembayaran harus berupa JPG, JPEG, PNG, atau PDF.',
            'proof.mimetypes' => 'Jenis file bukti pembayaran tidak valid.',
            'proof.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
        ]);

        $path = $request->file('proof')->store('payment-proofs', 'public');

        try {
            DB::transaction(function () use ($bill, $validated, $path, $notifier): void {
                $lockedBill = Bill::query()->lockForUpdate()->findOrFail($bill->id);

                if ($lockedBill->status === BillStatus::LUNAS) {
                    throw ValidationException::withMessages(['payment' => 'Tagihan yang sudah lunas tidak dapat dibayar lagi.']);
                }

                if (Payment::where('bill_id', $lockedBill->id)->where('status', PaymentStatus::PENDING->value)->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['payment' => 'Bukti pembayaran untuk tagihan ini sedang menunggu verifikasi.']);
                }

                $paymentMethod = PaymentMethod::query()->lockForUpdate()->find($validated['payment_method_id']);
                if (! $paymentMethod || ! $paymentMethod->is_active) {
                    throw ValidationException::withMessages(['payment_method_id' => 'Metode pembayaran tidak aktif atau tidak ditemukan.']);
                }

                $payment = Payment::create([
                    'bill_id' => $lockedBill->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $lockedBill->amount,
                    'paid_date' => $validated['paid_date'],
                    'sender_name' => $validated['sender_name'] ?? null,
                    'note' => $validated['note'] ?? null,
                    'proof_path' => $path,
                    'status' => PaymentStatus::PENDING,
                ]);

                $lockedBill->update(['status' => BillStatus::MENUNGGU_VERIFIKASI]);
                $notifier->paymentSubmitted($payment);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        return redirect()->route('customer.bills.show', $bill)->with('success', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.');
    }
}
