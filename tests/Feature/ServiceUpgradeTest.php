<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\CustomerStatus;
use App\Enums\UpgradeStatus;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceUpgradeRequest;
use App\Models\User;
use App\Services\Billing\BillGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceUpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_only_one_pending_upgrade_and_service_stays_unchanged(): void
    {
        $old = Service::factory()->create(['price' => 100000]);
        $new = Service::factory()->create(['price' => 150000]);
        $another = Service::factory()->create(['price' => 200000]);
        $customer = Customer::factory()->create(['service_id' => $old->id]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($customer->user)->get(route('customer.services.index'))->assertOk();
        $this->actingAs($customer->user)->post(route('customer.services.upgrades.store'), ['to_service_id' => $new->id])->assertRedirect();
        $this->actingAs($customer->user)->post(route('customer.services.upgrades.store'), ['to_service_id' => $another->id])->assertSessionHasErrors('to_service_id');

        $this->assertSame($old->id, $customer->fresh()->service_id);
        $this->assertDatabaseCount('service_upgrade_requests', 1);
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'type' => 'upgrade_requested']);
    }

    public function test_approval_uses_next_period_across_year_boundary_and_applies_before_effective_bill(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-15', config('app.timezone')));
        try {
            $old = Service::factory()->create(['price' => 100000]);
            $new = Service::factory()->create(['price' => 250000]);
            $customer = Customer::factory()->create(['service_id' => $old->id, 'status' => CustomerStatus::AKTIF]);
            $admin = User::factory()->create(['role' => UserRole::ADMIN]);
            $billDecember = Bill::factory()->create(['customer_id' => $customer->id, 'service_id' => $old->id, 'period' => '2026-12', 'amount' => 100000, 'status' => BillStatus::BELUM_BAYAR]);
            $upgrade = ServiceUpgradeRequest::factory()->create(['customer_id' => $customer->id, 'from_service_id' => $old->id, 'to_service_id' => $new->id, 'status' => UpgradeStatus::PENDING]);

            $this->actingAs($admin)->post(route('admin.upgrades.approve', $upgrade))->assertRedirect();
            $approved = $upgrade->fresh();
            $this->assertSame(UpgradeStatus::APPROVED, $approved->status);
            $this->assertSame('2027-01', $approved->effective_period);
            $this->assertSame($old->id, $customer->fresh()->service_id);
            $this->assertDatabaseHas('notifications', ['user_id' => $customer->user_id, 'type' => 'upgrade_approved']);
            $this->assertDatabaseHas('system_logs', ['table_name' => 'service_upgrade_requests', 'record_id' => $upgrade->id, 'action' => 'approve']);

            app(BillGenerator::class)->generateForPeriod('2026-12');
            $this->assertSame($old->id, $customer->fresh()->service_id);
            $this->assertSame('100000.00', (string) $billDecember->fresh()->amount);
            $this->assertSame(UpgradeStatus::APPROVED, $upgrade->fresh()->status);

            app(BillGenerator::class)->generateForPeriod('2027-01');
            $this->assertSame($new->id, $customer->fresh()->service_id);
            $this->assertSame(UpgradeStatus::APPLIED, $upgrade->fresh()->status);
            $this->assertDatabaseHas('bills', ['customer_id' => $customer->id, 'period' => '2027-01', 'service_id' => $new->id, 'amount' => '250000.00']);
            $this->assertDatabaseHas('notifications', ['user_id' => $customer->user_id, 'type' => 'upgrade_applied']);
            $this->assertDatabaseHas('system_logs', ['table_name' => 'service_upgrade_requests', 'record_id' => $upgrade->id, 'action' => 'applied']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_rejection_requires_reason_and_notifies_customer(): void
    {
        $customer = Customer::factory()->create();
        $new = Service::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $upgrade = ServiceUpgradeRequest::factory()->create(['customer_id' => $customer->id, 'from_service_id' => $customer->service_id, 'to_service_id' => $new->id]);

        $this->actingAs($admin)->post(route('admin.upgrades.reject', $upgrade), [])->assertSessionHasErrors('rejection_reason');
        $this->actingAs($admin)->post(route('admin.upgrades.reject', $upgrade), ['rejection_reason' => 'Layanan belum tersedia di area Anda.'])->assertRedirect();

        $this->assertSame(UpgradeStatus::REJECTED, $upgrade->fresh()->status);
        $this->assertSame('Layanan belum tersedia di area Anda.', $upgrade->fresh()->note);
        $this->assertDatabaseHas('notifications', ['user_id' => $customer->user_id, 'type' => 'upgrade_rejected']);
        $this->assertDatabaseHas('system_logs', ['table_name' => 'service_upgrade_requests', 'record_id' => $upgrade->id, 'action' => 'reject']);
    }

    public function test_upgrade_routes_are_authorized_by_role(): void
    {
        $customer = Customer::factory()->create();
        $target = Service::factory()->create();
        $upgrade = ServiceUpgradeRequest::factory()->create(['customer_id' => $customer->id, 'from_service_id' => $customer->service_id, 'to_service_id' => $target->id]);
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

        $this->actingAs($supervisor)->get(route('admin.upgrades.index'))->assertForbidden();
        $this->actingAs($customer->user)->post(route('admin.upgrades.approve', $upgrade))->assertForbidden();
        $this->actingAs($supervisor)->get(route('customer.services.index'))->assertForbidden();
    }
}
