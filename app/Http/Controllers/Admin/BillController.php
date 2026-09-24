<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use App\Services\Billing\BillGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage', User::class);
        $bills = $this->query($request)->paginate(10)->withQueryString();

        return view('bills.index', ['bills' => $bills, 'isAdmin' => true]);
    }

    public function show(Bill $bill): View
    {
        $this->authorize('manage', User::class);
        $bill->load(['customer.user', 'service', 'payments.paymentMethod']);

        return view('bills.show', ['bill' => $bill, 'isAdmin' => true]);
    }

    public function generate(Request $request, BillGenerator $generator): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $validated = $request->validate(['period' => ['required', 'date_format:Y-m']]);
        $summary = $generator->generateForPeriod($validated['period']);

        return back()->with('success', sprintf('Generate selesai: %d dibuat, %d dilewati, %d gagal.', $summary['created'], $summary['skipped'], $summary['failed']));
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
