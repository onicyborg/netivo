<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer as CustomerModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request): View
    {
        $customer = CustomerModel::where('user_id', $request->user()->id)->first();
        $bills = $customer
            ? $customer->bills()->with('service')->latest('period')->get()
            : collect();

        return view('customer.bills.index', compact('bills'));
    }

    public function show(Bill $bill): View
    {
        $this->authorize('view', $bill);
        $bill->load(['service', 'customer.user', 'payments.paymentMethod']);

        return view('customer.bills.show', compact('bill'));
    }

    public function pay(Bill $bill): View|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('pay', $bill);

        if ($bill->status->value === 'lunas') {
            return redirect()->route('customer.bills.show', $bill)->with('error', 'Tagihan yang sudah lunas tidak dapat dibayar lagi.');
        }

        if ($bill->payments()->where('status', 'pending')->exists()) {
            return redirect()->route('customer.bills.show', $bill)->with('error', 'Tagihan ini sedang menunggu verifikasi pembayaran.');
        }

        $bill->load('service');

        return view('customer.bills.pay', [
            'bill' => $bill,
            'paymentMethods' => \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
