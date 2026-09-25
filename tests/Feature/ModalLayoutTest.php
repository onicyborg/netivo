<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_layout_includes_clickable_modal_stacking_fix(): void
    {
        $this->get(route('login'))->assertOk();

        $response = $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]))
            ->get(route('admin.payment-methods.index'));

        $response->assertOk()
            ->assertSee('.modal-backdrop { z-index: 1050 !important; }', false)
            ->assertSee("document.body.appendChild(this)", false);
    }
}
