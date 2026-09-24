<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\DashboardDataService;

class DashboardController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return match ($request->user()->role) {
            UserRole::ADMIN => redirect()->route('admin.dashboard'),
            UserRole::SUPERVISOR => redirect()->route('supervisor.dashboard'),
            UserRole::CUSTOMER => redirect()->route('customer.dashboard'),
        };
    }

    public function admin(DashboardDataService $dashboard): View
    {
        return view('dashboard.admin', $dashboard->forAdmin());
    }

    public function supervisor(DashboardDataService $dashboard): View
    {
        return view('dashboard.supervisor', $dashboard->forSupervisor());
    }

    public function customer(Request $request, DashboardDataService $dashboard): View
    {
        return view('dashboard.customer', $dashboard->forCustomer($request->user()));
    }
}
