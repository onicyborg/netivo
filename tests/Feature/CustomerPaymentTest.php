<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\CustomerStatus;
use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_another_customers_bill_or_proof(): void
    {
        Storage::fake('public');
        $owner = Customer::factory()->create();
        $other = Customer::factory()->create();
        $bill = Bill::factory()->create(['customer_id' => $owner->id]);
        $payment = Payment::factory()->create([
            'bill_id' => $bill->id,
            'proof_path' => 'payment-proofs/private-proof.pdf',
        ]);
        Storage::disk('public')->put($payment->proof_path, 'proof');

        $this->actingAs($other->user)
            ->get(route('customer.bills.show', $bill))
            ->assertForbidden();
        $this->actingAs($other->user)
            ->get(route('customer.bills.pay', $bill))
            ->assertForbidden();
        $this->actingAs($other->user)
            ->get(route('customer.payments.proof', $payment))
            ->assertForbidden();
    }

    public function test_customer_can_submit_valid_payment_and_bill_enters_verification(): void
    {
        Storage::fake('public');
        [$customer, $bill] = $this->customerWithBill();
        $method = PaymentMethod::factory()->create(['type' => PaymentMethodType::TRANSFER, 'is_active' => true]);

        $response = $this->actingAs($customer->user)->post(route('customer.payments.store', $bill), [
            'payment_method_id' => $method->id,
            'paid_date' => today()->toDateString(),
            'sender_name' => 'Nama Pengirim',
            'note' => 'Pembayaran September',
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect(route('customer.bills.show', $bill));
        $payment = Payment::firstOrFail();
        $this->assertSame('250000.00', (string) $payment->amount);
        $this->assertSame(PaymentStatus::PENDING, $payment->status);
        $this->assertSame(BillStatus::MENUNGGU_VERIFIKASI, $bill->fresh()->status);
        Storage::disk('public')->assertExists($payment->proof_path);
    }

    public function test_invalid_proof_type_and_size_are_rejected(): void
    {
        Storage::fake('public');
        [$customer, $bill] = $this->customerWithBill();
        $method = PaymentMethod::factory()->create();

        $this->actingAs($customer->user)->post(route('customer.payments.store', $bill), [
            'payment_method_id' => $method->id,
            'paid_date' => today()->toDateString(),
            'proof' => UploadedFile::fake()->create('bukti.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('proof');
        $this->assertDatabaseCount('payments', 0);

        $this->actingAs($customer->user)->post(route('customer.payments.store', $bill), [
            'payment_method_id' => $method->id,
            'paid_date' => today()->toDateString(),
            'proof' => UploadedFile::fake()->create('besar.jpg', 2049, 'image/jpeg'),
        ])->assertSessionHasErrors('proof');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_second_pending_upload_is_rejected(): void
    {
        Storage::fake('public');
        [$customer, $bill] = $this->customerWithBill();
        $method = PaymentMethod::factory()->create();
        $payload = fn () => [
            'payment_method_id' => $method->id,
            'paid_date' => today()->toDateString(),
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ];

        $this->actingAs($customer->user)->post(route('customer.payments.store', $bill), $payload())->assertRedirect();
        $this->actingAs($customer->user)->post(route('customer.payments.store', $bill), $payload())
            ->assertSessionHasErrors('payment');
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_paid_bill_cannot_be_paid(): void
    {
        Storage::fake('public');
        [$customer, $bill] = $this->customerWithBill(['status' => BillStatus::LUNAS]);
        $method = PaymentMethod::factory()->create();

        $this->actingAs($customer->user)->post(route('customer.payments.store', $bill), [
            'payment_method_id' => $method->id,
            'paid_date' => today()->toDateString(),
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertSessionHasErrors('payment');
        $this->assertDatabaseCount('payments', 0);
    }

    private function customerWithBill(array $billOverrides = []): array
    {
        $customer = Customer::factory()->create(['status' => CustomerStatus::AKTIF]);
        $bill = Bill::factory()->create(array_merge(['customer_id' => $customer->id], $billOverrides));

        return [$customer, $bill];
    }
}
