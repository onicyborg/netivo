<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\CronLog;
use App\Models\Customer;
use App\Models\DailyReport;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceUpgradeRequest;
use App\Models\Setting;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_application_models_use_non_incrementing_uuid_keys(): void
    {
        foreach ([
            User::class,
            Setting::class,
            Service::class,
            Customer::class,
            PaymentMethod::class,
            Bill::class,
            Payment::class,
            Receipt::class,
            ServiceUpgradeRequest::class,
            DailyReport::class,
            Notification::class,
            CronLog::class,
            SystemLog::class,
        ] as $modelClass) {
            $model = new $modelClass;

            $this->assertFalse($model->getIncrementing(), $modelClass);
            $this->assertSame('string', $model->getKeyType(), $modelClass);
        }
    }

    public function test_bill_period_is_unique_per_customer(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id, 'service_id' => $service->id]);

        Bill::factory()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'period' => '2026-09',
        ]);

        $this->expectException(QueryException::class);

        Bill::factory()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'period' => '2026-09',
        ]);
    }

    public function test_core_relationships_are_available(): void
    {
        $service = Service::factory()->create();
        $customer = Customer::factory()->create(['service_id' => $service->id]);
        $bill = Bill::factory()->create(['customer_id' => $customer->id, 'service_id' => $service->id]);
        $paymentMethod = PaymentMethod::factory()->create();
        $payment = Payment::factory()->create(['bill_id' => $bill->id, 'payment_method_id' => $paymentMethod->id]);
        $receipt = Receipt::factory()->create(['payment_id' => $payment->id]);

        $this->assertTrue($customer->service->is($service));
        $this->assertTrue($bill->customer->is($customer));
        $this->assertTrue($payment->bill->is($bill));
        $this->assertTrue($payment->receipt->is($receipt));
    }
}
