<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\PaymentMethodType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::ADMIN]);
    }

    public function test_admin_can_read_and_update_system_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk();
        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'bill_due_day' => 15,
            'company_name' => 'PT Netivo Baru',
            'company_address' => 'Jalan Billing No. 1',
            'company_phone' => '0211234567',
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'bill_due_day', 'value' => '15']);
        $this->assertDatabaseHas('settings', ['key' => 'company_name', 'value' => 'PT Netivo Baru']);
    }

    public function test_admin_can_crud_services_and_used_service_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $response = $this->actingAs($admin)->post(route('admin.services.store'), [
            'name' => 'Paket Uji', 'speed_mbps' => 25, 'price' => 275000, 'description' => 'Uji', 'is_active' => 1,
        ]);
        $response->assertRedirect();
        $service = Service::where('name', 'Paket Uji')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.services.update', $service), [
            'name' => 'Paket Uji Pro', 'speed_mbps' => 50, 'price' => 350000, 'description' => 'Uji diperbarui', 'is_active' => 0,
        ])->assertRedirect();
        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'Paket Uji Pro', 'is_active' => false]);

        Customer::factory()->create(['service_id' => $service->id]);
        $this->actingAs($admin)->delete(route('admin.services.destroy', $service))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    public function test_admin_can_crud_payment_methods(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.payment-methods.index'))->assertOk();
        $this->actingAs($admin)->post(route('admin.payment-methods.store'), [
            'type' => PaymentMethodType::EWALLET->value,
            'name' => 'OVO',
            'account_number' => '0812345678',
            'account_name' => 'PT Netivo',
            'is_active' => 1,
        ])->assertRedirect();
        $method = PaymentMethod::where('name', 'OVO')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.payment-methods.update', $method), [
            'type' => PaymentMethodType::TRANSFER->value,
            'name' => 'Bank Uji',
            'account_number' => '1234567890',
            'account_name' => 'PT Netivo Baru',
            'is_active' => 0,
        ])->assertRedirect();
        $this->assertDatabaseHas('payment_methods', ['id' => $method->id, 'name' => 'Bank Uji', 'is_active' => false]);

        $this->actingAs($admin)->delete(route('admin.payment-methods.destroy', $method))->assertRedirect();
        $this->assertDatabaseMissing('payment_methods', ['id' => $method->id]);
    }

    public function test_customer_creation_is_transactional_has_automatic_number_and_can_be_reset(): void
    {
        $admin = $this->admin();
        $service = Service::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.customers.store'), [
            'name' => 'Customer Uji',
            'email' => 'customer-uji@example.test',
            'password' => 'password-awal',
            'phone' => '08123456789',
            'address' => 'Alamat uji',
            'service_id' => $service->id,
            'status' => CustomerStatus::AKTIF->value,
        ])->assertRedirect();

        $customer = Customer::with('user')->whereHas('user', fn ($query) => $query->where('email', 'customer-uji@example.test'))->firstOrFail();
        $this->assertMatchesRegularExpression('/^CUST-\d{6}$/', $customer->customer_number);
        $this->assertTrue($customer->user->is_active);

        $this->actingAs($admin)->post(route('admin.customers.reset-password', $customer), [
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('password-baru', $customer->user->fresh()->password));

        $this->actingAs($admin)->put(route('admin.customers.update', $customer), [
            'name' => 'Customer Nonaktif',
            'email' => 'customer-uji@example.test',
            'phone' => '08123456789',
            'address' => 'Alamat uji',
            'service_id' => $service->id,
            'status' => CustomerStatus::NONAKTIF->value,
        ])->assertRedirect();
        $this->assertFalse($customer->user->fresh()->is_active);
    }

    public function test_customer_email_must_be_unique(): void
    {
        $admin = $this->admin();
        $service = Service::factory()->create();
        User::factory()->create(['email' => 'duplicate@example.test']);

        $this->actingAs($admin)->from(route('admin.customers.index'))->post(route('admin.customers.store'), [
            'name' => 'Duplikat', 'email' => 'duplicate@example.test', 'password' => 'password-awal',
            'phone' => '0812', 'address' => 'Alamat', 'service_id' => $service->id, 'status' => 'aktif',
        ])->assertSessionHasErrors('email');
    }

    public function test_non_admin_cannot_access_master_data(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPERVISOR]);

        $this->actingAs($user)->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.services.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.payment-methods.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.customers.index'))->assertForbidden();
    }
}
