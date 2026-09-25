<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class OtikaFontController extends Controller
{
    /** @var array<string, string> */
    private const FONTS = [
        'nunito-v9-latin-regular.woff2' => 'font/woff2',
        'nunito-v9-latin-regular.woff' => 'font/woff',
        'nunito-v9-latin-regular.ttf' => 'font/ttf',
        'nunito-v9-latin-600.woff2' => 'font/woff2',
        'nunito-v9-latin-600.woff' => 'font/woff',
        'nunito-v9-latin-600.ttf' => 'font/ttf',
        'nunito-v9-latin-700.woff2' => 'font/woff2',
        'nunito-v9-latin-700.woff' => 'font/woff',
        'nunito-v9-latin-700.ttf' => 'font/ttf',
        'nunito-v9-latin-800.woff2' => 'font/woff2',
        'nunito-v9-latin-800.woff' => 'font/woff',
        'nunito-v9-latin-800.ttf' => 'font/ttf',
        'fa-solid-900.woff2' => 'font/woff2',
        'fa-solid-900.woff' => 'font/woff',
        'fa-solid-900.ttf' => 'font/ttf',
    ];

    public function show(string $font): Response
    {
        abort_unless(isset(self::FONTS[$font]), 404);

        $directory = str_starts_with($font, 'fa-') ? 'fonts/webfonts' : 'fonts';
        $url = rtrim((string) config('app.asset_url'), '/').'/'.$directory.'/'.$font;
        $fontResponse = Http::timeout(10)->get($url);

        abort_unless($fontResponse->successful(), 404);

        return response($fontResponse->body(), 200, [
            'Content-Type' => self::FONTS[$font],
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
