<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_confirmation_updates_payment_bill_and_receipt_atomically(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $payment = Payment::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.payments.confirm', $payment))
            ->assertRedirect(route('admin.payments.show', $payment));

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => PaymentStatus::CONFIRMED->value, 'verified_by' => $admin->id]);
        $this->assertDatabaseHas('bills', ['id' => $payment->bill_id, 'status' => BillStatus::LUNAS->value]);
        $this->assertDatabaseCount('receipts', 1);
        $this->assertMatchesRegularExpression('/^RCP-\d{8}-\d{4}$/', Receipt::firstOrFail()->receipt_number);
        $this->assertDatabaseHas('system_logs', ['table_name' => 'payments', 'record_id' => $payment->id, 'action' => 'confirm', 'user_id' => $admin->id]);
    }

    public function test_double_confirmation_does_not_create_second_receipt(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $payment = Payment::factory()->create();
        $this->actingAs($admin)->post(route('admin.payments.confirm', $payment))->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.payments.confirm', $payment))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('receipts', 1);
    }

    public function test_rejection_requires_reason_and_restores_due_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $payment = Payment::factory()->create(['bill_id' => Bill::factory()->create(['due_date' => today()->addDay()])->id]);

        $this->actingAs($admin)->post(route('admin.payments.reject', $payment), [])->assertSessionHasErrors('rejection_reason');
        $this->actingAs($admin)->post(route('admin.payments.reject', $payment), ['rejection_reason' => 'Bukti tidak terbaca.'])->assertRedirect();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => PaymentStatus::REJECTED->value, 'rejection_reason' => 'Bukti tidak terbaca.']);
        $this->assertDatabaseHas('bills', ['id' => $payment->bill_id, 'status' => BillStatus::BELUM_BAYAR->value]);
        $this->assertDatabaseHas('system_logs', ['table_name' => 'payments', 'record_id' => $payment->id, 'action' => 'reject', 'user_id' => $admin->id]);

        $overduePayment = Payment::factory()->create(['bill_id' => Bill::factory()->create(['due_date' => today()->subDay()])->id]);
        $this->actingAs($admin)->post(route('admin.payments.reject', $overduePayment), ['rejection_reason' => 'Bukti tidak sesuai.'])->assertRedirect();
        $this->assertDatabaseHas('bills', ['id' => $overduePayment->bill_id, 'status' => BillStatus::TERLAMBAT->value]);
    }

    public function test_only_admin_can_confirm_or_reject(): void
    {
        $payment = Payment::factory()->create();
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
        $customer = Customer::factory()->create();

        $this->actingAs($supervisor)->post(route('admin.payments.confirm', $payment))->assertForbidden();
        $this->actingAs($customer->user)->post(route('admin.payments.reject', $payment), ['rejection_reason' => 'Tidak sesuai.'])->assertForbidden();
    }

    public function test_receipt_pdf_is_only_available_to_owner_admin_or_supervisor(): void
    {
        $customer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $payment = Payment::factory()->create(['bill_id' => Bill::factory()->create(['customer_id' => $customer->id])->id, 'status' => PaymentStatus::CONFIRMED]);
        $receipt = Receipt::factory()->create(['payment_id' => $payment->id]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

        $this->actingAs($customer->user)->get(route('customer.receipts.show', $receipt))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($otherCustomer->user)->get(route('customer.receipts.show', $receipt))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.receipts.show', $receipt))->assertOk();
        $this->actingAs($supervisor)->get(route('supervisor.receipts.show', $receipt))->assertOk();
    }
}
