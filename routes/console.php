<?php

use App\Services\Billing\BillGenerator;
use App\Services\Billing\OverdueMarker;
use Illuminate\Support\Facades\Artisan;

Artisan::command('bills:generate {--period= : Periode dengan format YYYY-MM}', function (BillGenerator $generator): int {
    $summary = $generator->generateForPeriod($this->option('period') ?: now()->format('Y-m'));
    $this->table(['Dibuat', 'Dilewati', 'Gagal'], [[$summary['created'], $summary['skipped'], $summary['failed']]]);

    return $summary['failed'] > 0 ? 1 : 0;
})->purpose('Membuat tagihan untuk periode tertentu.');

Artisan::command('bills:mark-overdue', function (OverdueMarker $marker): int {
    $this->info('Tagihan ditandai terlambat: '.$marker->mark());

    return 0;
})->purpose('Menandai tagihan yang melewati jatuh tempo.');
