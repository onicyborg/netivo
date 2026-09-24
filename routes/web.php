<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::resource('services', ServiceController::class)->except(['show', 'create', 'edit']);
    Route::resource('payment-methods', PaymentMethodController::class)->except(['show', 'create', 'edit']);
    Route::post('/customers/{customer}/reset-password', [CustomerController::class, 'resetPassword'])->name('customers.reset-password');
    Route::resource('customers', CustomerController::class)->except(['show', 'create', 'edit']);
});

Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'supervisor'])->name('dashboard');
});

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'customer'])->name('dashboard');
});
