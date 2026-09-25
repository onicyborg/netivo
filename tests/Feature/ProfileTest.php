<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_personal_profile_without_changing_role(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::SUPERVISOR]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'nama-baru@example.test',
            'profile_photo' => UploadedFile::fake()->image('profile.png'),
        ]);

        $response->assertRedirect(route('profile'));
        $updated = $user->fresh();
        $this->assertSame('Nama Baru', $updated->name);
        $this->assertSame('nama-baru@example.test', $updated->email);
        $this->assertSame(UserRole::SUPERVISOR, $updated->role);
        $this->assertNotNull($updated->profile_photo_path);
        Storage::disk('public')->assertExists($updated->profile_photo_path);
    }
}
