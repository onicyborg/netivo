<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\BillController as AdminBillController;
use App\Http\Controllers\Admin\CronLogController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\Customer\BillController as CustomerBillController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\DailyReportController as AdminDailyReportController;
use App\Http\Controllers\Supervisor\DailyReportController as SupervisorDailyReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Supervisor\BillController as SupervisorBillController;
use App\Http\Controllers\Supervisor\PaymentController as SupervisorPaymentController;
use App\Http\Controllers\Admin\ServiceUpgradeController;
use App\Http\Controllers\Customer\ServiceController as CustomerServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['cron.token', 'throttle:cron'])->prefix('cron')->group(function (): void {
    Route::post('/generate-bills', [CronController::class, 'generateBills'])->name('cron.generate-bills');
    Route::post('/mark-overdue', [CronController::class, 'markOverdue'])->name('cron.mark-overdue');
    Route::post('/daily-report', [CronController::class, 'dailyReport'])->name('cron.daily-report');
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
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('/bills', [AdminBillController::class, 'index'])->name('bills.index');
    Route::post('/bills/generate', [AdminBillController::class, 'generate'])->name('bills.generate');
    Route::get('/bills/{bill}', [AdminBillController::class, 'show'])->name('bills.show');
    Route::get('/cron-logs', [CronLogController::class, 'index'])->name('cron-logs.index');
    Route::get('/reports', [AdminDailyReportController::class, 'index'])->name('reports.index');
    Route::post('/reports', [AdminDailyReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [AdminDailyReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/resend', [AdminDailyReportController::class, 'resend'])->name('reports.resend');
    Route::get('/upgrades', [ServiceUpgradeController::class, 'index'])->name('upgrades.index');
    Route::get('/upgrades/{upgrade}', [ServiceUpgradeController::class, 'show'])->name('upgrades.show');
    Route::post('/upgrades/{upgrade}/approve', [ServiceUpgradeController::class, 'approve'])->name('upgrades.approve');
    Route::post('/upgrades/{upgrade}/reject', [ServiceUpgradeController::class, 'reject'])->name('upgrades.reject');
    Route::get('/payments/{payment}/proof', [PaymentProofController::class, 'show'])->name('payments.proof');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/confirm', [AdminPaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::resource('services', ServiceController::class)->except(['show', 'create', 'edit']);
    Route::resource('payment-methods', PaymentMethodController::class)->except(['show', 'create', 'edit']);
    Route::post('/customers/{customer}/reset-password', [CustomerController::class, 'resetPassword'])->name('customers.reset-password');
    Route::resource('customers', CustomerController::class)->except(['show', 'create', 'edit']);
});

Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'supervisor'])->name('dashboard');
    Route::get('/bills', [SupervisorBillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [SupervisorBillController::class, 'show'])->name('bills.show');
    Route::get('/payments/{payment}/proof', [PaymentProofController::class, 'show'])->name('payments.proof');
    Route::get('/payments', [SupervisorPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [SupervisorPaymentController::class, 'show'])->name('payments.show');
    Route::get('/reports', [SupervisorDailyReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [SupervisorDailyReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/archive', [SupervisorDailyReportController::class, 'archive'])->name('reports.archive');
    Route::post('/reports/{report}/revision', [SupervisorDailyReportController::class, 'revision'])->name('reports.revision');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
});

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'customer'])->name('dashboard');
    Route::get('/services', [CustomerServiceController::class, 'index'])->name('services.index');
    Route::post('/services/upgrades', [CustomerServiceController::class, 'store'])->name('services.upgrades.store');
    Route::get('/bills', [CustomerBillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}/pay', [CustomerBillController::class, 'pay'])->name('bills.pay');
    Route::get('/bills/{bill}', [CustomerBillController::class, 'show'])->name('bills.show');
    Route::get('/payments', [CustomerPaymentController::class, 'index'])->name('payments.index');
    Route::post('/bills/{bill}/payments', [CustomerPaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/proof', [PaymentProofController::class, 'show'])->name('payments.proof');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
});
