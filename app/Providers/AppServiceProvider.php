<?php

namespace App\Providers;

use App\Contracts\Notifier;
use App\Models\User;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Notification;
use App\Models\DailyReport;
use App\Models\ServiceUpgradeRequest;
use App\Models\SystemLog;
use App\Models\Setting;
use App\Models\Service;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Policies\AdminPolicy;
use App\Policies\BillPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\DailyReportPolicy;
use App\Policies\ServiceUpgradeRequestPolicy;
use App\Policies\SystemLogPolicy;
use App\Observers\AuditObserver;
use App\Services\DatabaseNotifier;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Notifier::class, DatabaseNotifier::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, AdminPolicy::class);
        Gate::policy(Bill::class, BillPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(DailyReport::class, DailyReportPolicy::class);
        Gate::policy(ServiceUpgradeRequest::class, ServiceUpgradeRequestPolicy::class);
        Gate::policy(SystemLog::class, SystemLogPolicy::class);

        foreach ([User::class, Setting::class, Service::class, Customer::class, PaymentMethod::class, Bill::class, Payment::class, Receipt::class, ServiceUpgradeRequest::class, DailyReport::class] as $model) {
            $model::observe(AuditObserver::class);
        }

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('cron', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $view->with('unreadNotificationCount', $user?->notifications()->whereNull('read_at')->count() ?? 0);
            $view->with('navbarNotifications', $user?->notifications()->latest()->limit(5)->get() ?? collect());
            $view->with('userPreferences', array_merge([
                'theme' => 'light',
                'sidebar' => 'expanded',
                'navbar' => 'sticky',
                'sidebar_color' => 'light',
                'color_theme' => 'white',
            ], $user?->preferences ?? []));
        });
    }
}
