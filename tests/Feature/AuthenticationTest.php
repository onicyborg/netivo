<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_can_login_and_is_redirected_to_its_dashboard(): void
    {
        foreach ([
            [UserRole::ADMIN, 'admin.dashboard'],
            [UserRole::SUPERVISOR, 'supervisor.dashboard'],
            [UserRole::CUSTOMER, 'customer.dashboard'],
        ] as [$role, $route]) {
            $user = User::factory()->create(['role' => $role, 'email' => $role->value.'@example.test']);

            $response = $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $response->assertRedirectToRoute($route);
            $this->assertAuthenticatedAs($user);
            $this->get($response->headers->get('Location'))->assertOk();
            $this->post(route('logout'));
        }
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create(['email' => 'valid@example.test']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'salah-total',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login_with_clear_message(): void
    {
        $user = User::factory()->create(['is_active' => false, 'email' => 'inactive@example.test']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.']);
        $this->assertGuest();
    }

    public function test_login_attempts_are_throttled(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))->post(route('login.store'), [
                'email' => 'throttle@example.test',
                'password' => 'salah-'.$attempt,
            ])->assertRedirect(route('login'));
        }

        $this->post(route('login.store'), [
            'email' => 'throttle@example.test',
            'password' => 'salah-6',
        ])->assertStatus(429);
    }

    public function test_role_cannot_access_another_role_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->actingAs($admin)->get(route('supervisor.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('customer.dashboard'))->assertForbidden();

        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_password_can_be_changed_only_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->actingAs($user)->from(route('profile'))->put(route('password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('profile'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_user_can_persist_personal_display_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('preferences.update'), [
            'theme' => 'dark',
            'sidebar' => 'compact',
            'navbar' => 'static',
            'sidebar_color' => 'dark',
            'color_theme' => 'purple',
        ])->assertRedirect();

        $this->assertSame([
            'theme' => 'dark',
            'sidebar' => 'compact',
            'navbar' => 'static',
            'sidebar_color' => 'dark',
            'color_theme' => 'purple',
        ], $user->fresh()->preferences);
    }
}
