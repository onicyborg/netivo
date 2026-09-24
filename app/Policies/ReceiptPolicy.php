<?php

namespace App\Policies;

use App\Models\Receipt;
use App\Models\User;

class ReceiptPolicy
{
    public function view(User $user, Receipt $receipt): bool
    {
        return app(PaymentPolicy::class)->view($user, $receipt->payment);
    }
}
