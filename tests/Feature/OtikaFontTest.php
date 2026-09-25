<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OtikaFontTest extends TestCase
{
    public function test_font_is_proxied_same_origin_with_safe_content_type(): void
    {
        Http::fake([
            'https://otika.namikulo.com/assets/fonts/nunito-v9-latin-regular.woff2' => Http::response('font-data', 200),
        ]);

        $response = $this->get(route('otika.font', 'nunito-v9-latin-regular.woff2'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'font/woff2')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertSee('font-data', false);
    }

    public function test_unknown_font_cannot_be_proxied(): void
    {
        $this->get('/otika-fonts/secret.txt')->assertNotFound();
    }

    public function test_nunito_extra_bold_font_is_proxied(): void
    {
        Http::fake([
            'https://otika.namikulo.com/assets/fonts/nunito-v9-latin-800.woff2' => Http::response('font-data-800', 200),
        ]);

        $this->get(route('otika.font', 'nunito-v9-latin-800.woff2'))
            ->assertOk()
            ->assertHeader('Content-Type', 'font/woff2')
            ->assertSee('font-data-800', false);
    }

    public function test_auth_layout_references_same_origin_font_urls(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('/otika-fonts/nunito-v9-latin-regular.woff2', false)
            ->assertSee('/otika-fonts/nunito-v9-latin-800.woff2', false)
            ->assertSee('/otika-fonts/fa-solid-900.woff2', false);
    }
}
