<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EbayService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(private EbayService $ebay) {}

    /**
     * Testlauf: nur die ersten N Artikel importieren.
     */
    public function testImport(Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        $limit = min(max($limit, 1), 100); // max. 100 im Test

        $stats = $this->ebay->importFromEbay($limit);

        return response()->json([
            'mode'    => 'test',
            'limit'   => $limit,
            'stats'   => $stats,
            'message' => "Test abgeschlossen: {$stats['imported']} neu, {$stats['updated']} aktualisiert, {$stats['errors']} Fehler",
        ]);
    }

    /**
     * Vollimport: alle aktiven eBay-Artikel.
     * Läuft synchron — bei 13.000 Artikeln kann das mehrere Minuten dauern.
     */
    public function fullImport()
    {
        // PHP-Timeout erhöhen für den langen Import
        set_time_limit(600);
        ini_set('memory_limit', '256M');

        $stats = $this->ebay->importFromEbay(0);

        return response()->json([
            'mode'    => 'full',
            'stats'   => $stats,
            'message' => "Import abgeschlossen: {$stats['imported']} neu, {$stats['updated']} aktualisiert, {$stats['errors']} Fehler",
        ]);
    }
}
