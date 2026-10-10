<?php

use App\Services\EbayService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Scheduler
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// eBay-Sync alle 30 Minuten (konfigurierbar)
Artisan::command('ebay:sync', function () {
    if (!config('shop.ebay_sync_enabled')) {
        $this->info('eBay-Sync ist deaktiviert.');
        return;
    }

    $this->info('eBay-Sync gestartet...');
    $service = app(EbayService::class);
    $stats   = $service->syncFromEbay();

    $this->info("✅ Fertig: {$stats['sold']} verkauft, {$stats['updated']} aktualisiert, {$stats['errors']} Fehler");
})->purpose('eBay-Listings mit dem Shop synchronisieren');

// Scheduler (benötigt Cron-Eintrag auf dem Server)
Schedule::command('ebay:sync')
    ->everyThirtyMinutes()
    ->when(fn() => config('shop.ebay_sync_enabled'));
