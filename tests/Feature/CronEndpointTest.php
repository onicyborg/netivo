<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\CronLog;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CronEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.cron.secret' => 'cron-test-secret']);
    }

    public function test_cron_requires_a_valid_bearer_token(): void
    {
        $this->postJson(route('cron.generate-bills'))->assertUnauthorized();
        $this->withHeader('Authorization', 'Bearer salah')->postJson(route('cron.generate-bills'))->assertUnauthorized();
    }

    public function test_valid_cron_token_generates_bills_and_is_idempotent(): void
    {
        $customer = Customer::factory()->create();
        $headers = ['Authorization' => 'Bearer cron-test-secret'];

        $first = $this->withHeaders($headers)->postJson(route('cron.generate-bills'));
        $second = $this->withHeaders($headers)->postJson(route('cron.generate-bills'));

        $first->assertOk()->assertJsonStructure(['message', 'data' => ['created', 'skipped', 'failed']]);
        $second->assertOk()->assertJsonPath('data.created', 0)->assertJsonPath('data.skipped', 1);
        $this->assertSame(1, Bill::where('customer_id', $customer->id)->count());
        $this->assertSame(2, CronLog::where('job', 'bills:generate')->count());
    }

    public function test_cron_fails_closed_when_secret_is_missing(): void
    {
        config(['services.cron.secret' => '']);

        $this->withHeader('Authorization', 'Bearer cron-test-secret')->postJson(route('cron.generate-bills'))->assertUnauthorized();
    }
}
