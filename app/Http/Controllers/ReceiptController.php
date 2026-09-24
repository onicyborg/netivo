<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Services\SettingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReceiptController extends Controller
{
    public function show(Receipt $receipt, SettingService $settings): Response
    {
        $receipt->load(['payment.bill.customer.user', 'payment.bill.service', 'payment.paymentMethod']);
        $this->authorize('view', $receipt);

        return Pdf::loadView('receipts.pdf', compact('receipt', 'settings'))->stream($receipt->receipt_number.'.pdf');
    }
}
