<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewBilling', User::class);

        return view('bills.index', [
            'bills' => $this->query($request)->paginate(10)->withQueryString(),
            'isAdmin' => false,
        ]);
    }

    public function show(Bill $bill): View
    {
        $this->authorize('viewBilling', User::class);
        $bill->load(['customer.user', 'service', 'payments.paymentMethod']);

        return view('bills.show', ['bill' => $bill, 'isAdmin' => false]);
    }

    private function query(Request $request)
    {
        return Bill::query()
            ->with(['customer.user', 'service'])
            ->when($request->filled('period'), fn ($query) => $query->where('period', (string) $request->string('period')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', (string) $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q').'%';
                $query->where(function ($nested) use ($search): void {
                    $nested->where('bill_number', 'like', $search)
                        ->orWhereHas('customer', fn ($customer) => $customer->where('customer_number', 'like', $search)->orWhereHas('user', fn ($user) => $user->where('name', 'like', $search)));
                });
            })
            ->latest();
    }
}
