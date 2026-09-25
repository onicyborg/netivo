<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentProofController extends Controller
{
    public function show(Payment $payment): BinaryFileResponse
    {
        $this->authorize('proof', $payment);
        $disk = Storage::disk('public');

        abort_unless($payment->proof_path && $disk->exists($payment->proof_path), 404);

        return response()->file($disk->path($payment->proof_path), [
            'Content-Type' => $disk->mimeType($payment->proof_path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
