<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function admin(): View
    {
        return view('dashboard.placeholder', ['roleLabel' => 'Admin']);
    }

    public function supervisor(): View
    {
        return view('dashboard.placeholder', ['roleLabel' => 'Supervisor']);
    }

    public function customer(): View
    {
        return view('dashboard.placeholder', ['roleLabel' => 'Customer']);
    }
}
