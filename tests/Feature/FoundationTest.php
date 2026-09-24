<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_foundation_configuration_is_indonesian_and_uses_otika_asset_base_url(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('id', config('app.locale'));
        $this->assertSame('https://otika.namikulo.com/assets', config('app.asset_url'));
        $this->assertSame('https://otika.namikulo.com/assets/css/app.min.css', asset('css/app.min.css'));
    }

    public function test_shared_format_helpers_use_indonesian_formats(): void
    {
        $this->assertSame('Rp 1.500.000', format_rupiah(1500000));
        $this->assertSame('24 Sep 2026', format_tanggal_id(Carbon::create(2026, 9, 24)));
    }

    public function test_user_model_uses_uuid_generation_convention(): void
    {
        $user = new User;

        $this->assertFalse($user->getIncrementing());
        $this->assertSame('string', $user->getKeyType());
    }
}
