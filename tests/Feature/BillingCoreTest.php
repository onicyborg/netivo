<?php

namespace Tests\Feature;

use App\Contracts\Notifier;
use App\Enums\BillStatus;
use App\Enums\CustomerStatus;
use App\Enums\UpgradeStatus;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceUpgradeRequest;
use App\Models\Setting;
use App\Models\User;
use App\Services\Billing\BillGenerator;
use App\Services\Billing\OverdueMarker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BillingCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_generator_is_idempotent_and_skips_inactive_customers(): void
    {
        $active = Customer::factory()->create(['status' => CustomerStatus::AKTIF]);
        $inactive = Customer::factory()->create(['status' => CustomerStatus::NONAKTIF]);

        $generator = app(BillGenerator::class);
        $first = $generator->generateForPeriod('2026-09');
        $second = $generator->generateForPeriod('2026-09');

        $this->assertSame(1, $first['created']);
        $this->assertSame(1, $second['skipped']);
        $this->assertSame(0, Bill::where('customer_id', $inactive->id)->count());
        $this->assertSame(1, Bill::where('customer_id', $active->id)->count());
    }

    public function test_bill_snapshots_price_and_uses_configured_due_day(): void
    {
        Setting::create(['key' => 'bill_due_day', 'value' => '15']);
        $service = Service::factory()->create(['price' => 125000]);
        $customer = Customer::factory()->create(['service_id' => $service->id]);

        app(BillGenerator::class)->generateForPeriod('2026-02');
        $oldBill = Bill::where('customer_id', $customer->id)->firstOrFail();
        $service->update(['price' => 225000]);
        app(BillGenerator::class)->generateForPeriod('2026-03');
        $newBill = Bill::where('customer_id', $customer->id)->where('period', '2026-03')->firstOrFail();

        $this->assertSame('125000.00', (string) $oldBill->amount);
        $this->assertSame('225000.00', (string) $newBill->amount);
        $this->assertSame('2026-02-15', $oldBill->due_date->toDateString());
    }

    public function test_one_customer_failure_does_not_stop_other_customers(): void
    {
        $failedCustomer = Customer::factory()->create();
        $successfulCustomer = Customer::factory()->create();
        $failedId = $failedCustomer->id;

        $this->app->instance(Notifier::class, new class($failedId) implements Notifier {
            public function __construct(private readonly string $failedId) {}

            public function billCreated(Customer $customer, Bill $bill): void
            {
                if ($customer->id === $this->failedId) {
                    throw new \RuntimeException('Simulasi notifier gagal.');
                }
            }

            public function paymentSubmitted(Payment $payment): void {}

            public function paymentConfirmed(Payment $payment, Receipt $receipt): void {}

            public function paymentRejected(Payment $payment): void {}
        });

        $summary = app(BillGenerator::class)->generateForPeriod('2026-09');

        $this->assertSame(1, $summary['failed']);
        $this->assertSame(1, $summary['created']);
        $this->assertDatabaseMissing('bills', ['customer_id' => $failedCustomer->id, 'period' => '2026-09']);
        $this->assertDatabaseHas('bills', ['customer_id' => $successfulCustomer->id, 'period' => '2026-09']);
    }

    public function test_approved_upgrade_is_applied_before_bill_creation(): void
    {
        $oldService = Service::factory()->create(['price' => 100000]);
        $newService = Service::factory()->create(['price' => 200000]);
        $customer = Customer::factory()->create(['service_id' => $oldService->id]);
        $upgrade = ServiceUpgradeRequest::factory()->create([
            'customer_id' => $customer->id,
            'from_service_id' => $oldService->id,
            'to_service_id' => $newService->id,
            'status' => UpgradeStatus::APPROVED,
            'effective_period' => '2026-09',
        ]);

        app(BillGenerator::class)->generateForPeriod('2026-09');

        $this->assertSame($newService->id, $customer->fresh()->service_id);
        $this->assertSame(UpgradeStatus::APPLIED, $upgrade->fresh()->status);
        $this->assertDatabaseHas('bills', ['customer_id' => $customer->id, 'service_id' => $newService->id, 'amount' => '200000.00']);
    }

    public function test_overdue_marker_only_changes_overdue_unpaid_bills(): void
    {
        $overdue = Bill::factory()->create(['status' => BillStatus::BELUM_BAYAR, 'due_date' => today()->subDay()]);
        $future = Bill::factory()->create(['status' => BillStatus::BELUM_BAYAR, 'due_date' => today()->addDay()]);
        $verification = Bill::factory()->create(['status' => BillStatus::MENUNGGU_VERIFIKASI, 'due_date' => today()->subDay()]);
        $paid = Bill::factory()->create(['status' => BillStatus::LUNAS, 'due_date' => today()->subDay()]);

        $this->assertSame(1, app(OverdueMarker::class)->mark());
        $this->assertSame(BillStatus::TERLAMBAT, $overdue->fresh()->status);
        $this->assertSame(BillStatus::BELUM_BAYAR, $future->fresh()->status);
        $this->assertSame(BillStatus::MENUNGGU_VERIFIKASI, $verification->fresh()->status);
        $this->assertSame(BillStatus::LUNAS, $paid->fresh()->status);
        $this->assertSame(0, app(OverdueMarker::class)->mark());
    }

    public function test_new_customer_gets_current_period_bill_in_same_creation_flow(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $service = Service::factory()->create();

        $this->actingAs($admin)->post(route('admin.customers.store'), [
            'name' => 'Customer Tengah Bulan',
            'email' => 'tengah-bulan@example.test',
            'password' => 'password-awal',
            'phone' => '081234567890',
            'address' => 'Alamat tengah bulan',
            'service_id' => $service->id,
            'status' => CustomerStatus::AKTIF->value,
        ])->assertRedirect();

        $customer = Customer::whereHas('user', fn ($query) => $query->where('email', 'tengah-bulan@example.test'))->firstOrFail();
        $this->assertDatabaseHas('bills', ['customer_id' => $customer->id, 'period' => now()->format('Y-m')]);
    }
}
