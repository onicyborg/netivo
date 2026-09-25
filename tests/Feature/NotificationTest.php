<?php

namespace Tests\Feature;

use App\Contracts\Notifier;
use App\Enums\UserRole;
use App\Mail\NotificationMail;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifier_sends_each_available_trigger_to_the_correct_recipient(): void
    {
        Mail::fake();
        config(['mail.skip_dummy_emails' => false]);

        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $inactiveAdmin = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => false]);
        $customer = Customer::factory()->create();
        $bill = Bill::factory()->create(['customer_id' => $customer->id]);
        $payment = Payment::factory()->create(['bill_id' => $bill->id]);
        $receipt = Receipt::factory()->create(['payment_id' => $payment->id]);
        $notifier = app(Notifier::class);

        $notifier->billCreated($customer, $bill);
        $notifier->paymentSubmitted($payment);
        $notifier->paymentConfirmed($payment, $receipt);
        $notifier->paymentRejected($payment);

        $this->assertDatabaseHas('notifications', ['user_id' => $customer->user_id, 'type' => 'bill_created']);
        $this->assertDatabaseHas('notifications', ['user_id' => $customer->user_id, 'type' => 'payment_confirmed']);
        $this->assertDatabaseHas('notifications', ['user_id' => $customer->user_id, 'type' => 'payment_rejected']);
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'type' => 'payment_submitted']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $inactiveAdmin->id]);
        Mail::assertSent(NotificationMail::class, 4);
    }

    public function test_user_only_sees_and_marks_own_notifications(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $firstNotification = Notification::factory()->create(['user_id' => $first->id, 'url' => route('profile')]);
        $secondNotification = Notification::factory()->create(['user_id' => $second->id]);

        $this->actingAs($first)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee($firstNotification->title)
            ->assertDontSee($secondNotification->title);
        $this->actingAs($first)->get(route('notifications.read', $firstNotification))
            ->assertRedirect(route('profile'));
        $this->assertNotNull($firstNotification->fresh()->read_at);

        $this->actingAs($first)->get(route('notifications.read', $secondNotification))->assertForbidden();
    }

    public function test_mark_all_only_updates_authenticated_users_notifications(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $firstNotification = Notification::factory()->create(['user_id' => $first->id]);
        $secondNotification = Notification::factory()->create(['user_id' => $second->id]);

        $this->actingAs($first)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertNotNull($firstNotification->fresh()->read_at);
        $this->assertNull($secondNotification->fresh()->read_at);
    }
}
