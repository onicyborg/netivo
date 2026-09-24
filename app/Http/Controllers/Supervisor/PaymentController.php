<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewBilling', User::class);
        $status = $request->input('status', 'all');
        $payments = Payment::query()
            ->with(['bill.customer.user', 'bill.service', 'paymentMethod', 'receipt'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('supervisor.payments.index', compact('payments', 'status'));
    }

    public function show(Payment $payment): View
    {
        $this->authorize('viewBilling', User::class);
        $payment->load(['bill.customer.user', 'bill.service', 'paymentMethod', 'receipt']);

        return view('supervisor.payments.show', compact('payment'));
    }
}
