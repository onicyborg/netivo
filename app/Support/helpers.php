<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;

if (! function_exists('format_rupiah')) {
    /**
     * Format nominal rupiah without decimal places.
     */
    function format_rupiah(int|float|string|null $amount): string
    {
        return 'Rp '.number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}

if (! function_exists('format_tanggal_id')) {
    /**
     * Format tanggal dengan pola Indonesia, contoh: 24 Sep 2026.
     */
    function format_tanggal_id(CarbonInterface|string|null $date): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        return Carbon::parse($date)->locale('id')->translatedFormat('d M Y');
    }
}
