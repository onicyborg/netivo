<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use App\Services\DashboardDataService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_supervisor_kpis_match_current_period_data(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-25 12:00:00', config('app.timezone')));
        try {
            foreach ([BillStatus::LUNAS, BillStatus::BELUM_BAYAR, BillStatus::TERLAMBAT, BillStatus::MENUNGGU_VERIFIKASI] as $status) {
                Bill::factory()->create(['period' => '2026-09', 'status' => $status]);
            }
            $todayBill = Bill::factory()->create(['period' => '2026-09', 'status' => BillStatus::LUNAS]);
            Payment::factory()->create(['bill_id' => $todayBill->id, 'status' => PaymentStatus::CONFIRMED, 'amount' => 125000, 'verified_at' => now()]);
            $monthBill = Bill::factory()->create(['period' => '2026-09', 'status' => BillStatus::LUNAS]);
            Payment::factory()->create(['bill_id' => $monthBill->id, 'status' => PaymentStatus::CONFIRMED, 'amount' => 75000, 'verified_at' => now()->subDays(3)]);
            Payment::factory()->create(['status' => PaymentStatus::PENDING, 'amount' => 999000]);

            $admin = User::factory()->create(['role' => UserRole::ADMIN]);
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
            $adminData = app(DashboardDataService::class)->forAdmin();
            $supervisorData = app(DashboardDataService::class)->forSupervisor();

            $this->assertSame(7, $adminData['billTotal']);
            $this->assertSame(3, $adminData['billStatuses']['lunas']);
            $this->assertSame(2, $adminData['billStatuses']['belum_bayar']);
            $this->assertSame(1, $adminData['billStatuses']['terlambat']);
            $this->assertSame(1, $adminData['billStatuses']['menunggu_verifikasi']);
            $this->assertEquals(125000, (float) $adminData['revenueToday']);
            $this->assertEquals(200000, (float) $adminData['revenueMonth']);
            $this->assertSame(1, $adminData['pendingPayments']);
            $this->assertSame($adminData['billStatuses'], $supervisorData['billStatuses']);
            $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Pendapatan Hari Ini');
            $this->actingAs($supervisor)->get(route('supervisor.dashboard'))->assertOk()->assertSee('10 Transaksi Terbaru');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_customer_dashboard_only_contains_customer_owned_data(): void
    {
        $first = Customer::factory()->create();
        $second = Customer::factory()->create();
        $firstBill = Bill::factory()->create(['customer_id' => $first->id, 'period' => now()->format('Y-m')]);
        $secondBill = Bill::factory()->create(['customer_id' => $second->id, 'period' => now()->format('Y-m')]);
        Notification::factory()->create(['user_id' => $first->user_id, 'title' => 'Notifikasi milik pertama']);
        Notification::factory()->create(['user_id' => $second->user_id, 'title' => 'Notifikasi milik kedua']);

        $this->actingAs($first->user)->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee($firstBill->bill_number)
            ->assertDontSee($secondBill->bill_number)
            ->assertSee('Notifikasi milik pertama')
            ->assertDontSee('Notifikasi milik kedua');
    }

    public function test_dashboard_route_redirects_each_role_to_its_dashboard(): void
    {
        foreach ([[UserRole::ADMIN, 'admin.dashboard'], [UserRole::SUPERVISOR, 'supervisor.dashboard'], [UserRole::CUSTOMER, 'customer.dashboard']] as [$role, $route]) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route($route));
        }
    }
}
