<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\PaymentVerificationException;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage', User::class);
        $status = $request->input('status', 'pending');
        $payments = Payment::query()
            ->with(['bill.customer.user', 'bill.service', 'paymentMethod', 'receipt'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('admin.payments.index', compact('payments', 'status'));
    }

    public function show(Payment $payment): View
    {
        $this->authorize('manage', User::class);
        $payment->load(['bill.customer.user', 'bill.service', 'paymentMethod', 'receipt']);

        return view('admin.payments.show', compact('payment'));
    }

    public function confirm(Payment $payment, PaymentVerificationService $verification): RedirectResponse
    {
        $this->authorize('confirm', $payment);

        try {
            $receipt = $verification->confirm($payment, request()->user());
        } catch (PaymentVerificationException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->route('admin.payments.show', $payment)->with('success', 'Pembayaran dikonfirmasi dan kuitansi '.$receipt->receipt_number.' berhasil dibuat.');
    }

    public function reject(Request $request, Payment $payment, PaymentVerificationService $verification): RedirectResponse
    {
        $this->authorize('reject', $payment);
        $validated = $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            $verification->reject($payment, $request->user(), $validated['rejection_reason']);
        } catch (PaymentVerificationException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->route('admin.payments.show', $payment)->with('success', 'Pembayaran ditolak dan status tagihan telah diperbarui.');
    }
}
