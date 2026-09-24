<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SystemLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuditHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_logs_crud_and_sanitizes_sensitive_values(): void
    {
        $user = User::factory()->create(['password' => 'password-rahasia']);
        $oldName = $user->name;
        $created = SystemLog::where('table_name', 'users')->where('record_id', $user->id)->where('action', 'created')->latest()->firstOrFail();

        $this->assertSame('[disembunyikan]', $created->new_values['password']);
        $this->assertStringNotContainsString('password-rahasia', json_encode($created->new_values));

        $user->update(['name' => 'Nama Baru']);
        $updated = SystemLog::where('table_name', 'users')->where('record_id', $user->id)->where('action', 'updated')->latest()->firstOrFail();
        $this->assertSame('Nama Baru', $updated->new_values['name']);
        $this->assertSame($oldName, $updated->old_values['name']);
    }

    public function test_login_and_logout_are_audited_without_credentials(): void
    {
        $user = User::factory()->create(['email' => 'audit@example.test', 'password' => 'password']);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('system_logs', ['table_name' => 'users', 'record_id' => $user->id, 'action' => 'login']);
        $this->assertDatabaseHas('system_logs', ['table_name' => 'users', 'record_id' => $user->id, 'action' => 'logout']);
        $this->assertStringNotContainsString('password-rahasia', json_encode(SystemLog::where('record_id', $user->id)->get()->toArray()));
    }

    public function test_sensitive_and_binary_payloads_are_sanitized(): void
    {
        $log = app(AuditLogger::class)->log('payments', null, 'test', [], ['token' => 'abc', 'proof' => UploadedFile::fake()->create('bukti.pdf')]);

        $this->assertSame('[disembunyikan]', $log->new_values['token']);
        $this->assertSame('[data biner disembunyikan]', $log->new_values['proof']);
    }

    public function test_system_logs_are_read_only_for_admin_and_supervisor(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        SystemLog::factory()->create(['action' => 'confirm', 'table_name' => 'payments', 'user_id' => $admin->id]);

        $this->actingAs($admin)->get(route('admin.system-logs.index', ['action' => 'confirm']))->assertOk()->assertSee('confirm');
        $this->actingAs($supervisor)->get(route('supervisor.system-logs.index'))->assertOk()->assertSee('System Logs');
        $this->actingAs($customer)->get(route('admin.system-logs.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.system-logs.index'))->assertMethodNotAllowed();
    }
}
