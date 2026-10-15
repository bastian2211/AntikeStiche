<?php

namespace App\Services;

use App\Models\Article;
use App\Models\EbaySyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * eBay Trading API Integration
 *
 * Dokumentation: https://developer.ebay.com/api-docs/user-guides/static/trading-user-guide.html
 *
 * Konfiguration (in .env):
 *   EBAY_APP_ID=...
 *   EBAY_CERT_ID=...
 *   EBAY_DEV_ID=...
 *   EBAY_AUTH_TOKEN=...
 *   EBAY_SANDBOX=false
 */
class EbayService
{
    private string $apiUrl;
    private string $authToken;
    private string $appId;
    private string $devId;
    private string $certId;

    public function __construct()
    {
        $sandbox = config('services.ebay.sandbox', true);

        $this->apiUrl    = $sandbox
            ? 'https://api.sandbox.ebay.com/ws/api.dll'
            : 'https://api.ebay.com/ws/api.dll';

        $this->authToken = config('services.ebay.auth_token', '');
        $this->appId     = config('services.ebay.app_id', '');
        $this->devId     = config('services.ebay.dev_id', '');
        $this->certId    = config('services.ebay.cert_id', '');
    }

    /**
     * Artikel bei eBay als verkauft markieren (Listing beenden).
     */
    public function endListing(Article $article, string $reason = 'Sold'): bool
    {
        if (!$article->ebay_item_id) {
            return false;
        }

        $xml = $this->buildRequest('EndItem', "
            <ItemID>{$article->ebay_item_id}</ItemID>
            <EndingReason>{$reason}</EndingReason>
        ");

        $response = $this->call('EndItem', $xml);
        $success  = $response && ($response['Ack'] ?? '') === 'Success';

        EbaySyncLog::create([
            'article_id'   => $article->id,
            'ebay_item_id' => $article->ebay_item_id,
            'action'       => 'end_listing',
            'direction'    => 'shop_to_ebay',
            'status'       => $success ? 'success' : 'error',
            'message'      => $success ? 'Listing beendet' : ($response['error'] ?? 'Unbekannter Fehler'),
        ]);

        if ($success) {
            $article->update(['ebay_sync_status' => 'synced', 'ebay_active' => false]);
        } else {
            $article->update(['ebay_sync_status' => 'error']);
        }

        return $success;
    }

    /**
     * Preisänderung zu eBay synchronisieren.
     */
    public function revisePrice(Article $article): bool
    {
        if (!$article->ebay_item_id) {
            return false;
        }

        $price = number_format($article->price, 2, '.', '');

        $xml = $this->buildRequest('ReviseFixedPriceItem', "
            <Item>
                <ItemID>{$article->ebay_item_id}</ItemID>
                <StartPrice>{$price}</StartPrice>
            </Item>
        ");

        $response = $this->call('ReviseFixedPriceItem', $xml);
        $success  = $response && ($response['Ack'] ?? '') === 'Success';

        EbaySyncLog::create([
            'article_id'   => $article->id,
            'ebay_item_id' => $article->ebay_item_id,
            'action'       => 'revise_price',
            'direction'    => 'shop_to_ebay',
            'status'       => $success ? 'success' : 'error',
            'message'      => $success ? "Preis auf {$price} EUR aktualisiert" : ($response['error'] ?? 'Fehler'),
        ]);

        if ($success) {
            $article->update(['ebay_sync_status' => 'synced', 'ebay_last_sync_at' => now()]);
        }

        return $success;
    }

    /**
     * Alle aktiven eBay-Listings (DE) importieren inkl. Fotos.
     * Läuft paginiert in 200er-Blöcken.
     *
     * @param int $limit  0 = alle, sonst max. N Artikel (für Testläufe)
     * @param callable|null $progress  Callback($done, $total) für Fortschritt
     */
    public function importFromEbay(int $limit = 0, ?callable $progress = null): array
    {
        $stats = ['imported' => 0, 'updated' => 0, 'errors' => 0, 'skipped' => 0];
        $page  = 1;
        $perPage = 200;
        $totalPages = 1;
        $totalDone = 0;

        do {
            $xml = $this->buildRequest('GetSellerList', "
                <ActiveList>true</ActiveList>
                <StartTimeFrom>" . date('Y-m-d\TH:i:s\Z', strtotime('-120 days')) . "</StartTimeFrom>
                <StartTimeTo>" . date('Y-m-d\TH:i:s\Z') . "</StartTimeTo>
                <GranularityLevel>Fine</GranularityLevel>
                <IncludeItemSpecifics>false</IncludeItemSpecifics>
                <Pagination>
                    <EntriesPerPage>{$perPage}</EntriesPerPage>
                    <PageNumber>{$page}</PageNumber>
                </Pagination>
            ");

            $response = $this->call('GetSellerList', $xml);

            if (!$response || !in_array($response['Ack'] ?? '', ['Success', 'Warning'])) {
                Log::error('eBay Import: GetSellerList fehlgeschlagen', $response ?? []);
                $stats['errors']++;
                break;
            }

            $totalPages  = (int) ($response['PaginationResult']['TotalNumberOfPages'] ?? 1);
            $totalItems  = (int) ($response['PaginationResult']['TotalNumberOfEntries'] ?? 0);
            $items       = $response['ItemArray']['Item'] ?? [];

            // Einzelner Artikel kommt nicht als Array
            if (isset($items['ItemID'])) {
                $items = [$items];
            }

            foreach ($items as $item) {
                if ($limit > 0 && $totalDone >= $limit) break 2;

                try {
                    $this->upsertArticle($item, $stats);
                    $totalDone++;
                    if ($progress) $progress($totalDone, $totalItems);
                } catch (\Exception $e) {
                    Log::error('eBay Import: Artikel-Fehler', [
                        'item_id' => $item['ItemID'] ?? '?',
                        'error'   => $e->getMessage(),
                    ]);
                    $stats['errors']++;
                }
            }

            $page++;

        } while ($page <= $totalPages);

        return $stats;
    }

    /**
     * Einen eBay-Artikel in die DB schreiben (insert or update).
     */
    private function upsertArticle(array $item, array &$stats): void
    {
        $ebayId   = $item['ItemID'] ?? null;
        $title    = $item['Title'] ?? '';
        $price    = (float) ($item['BuyItNowPrice']['value']
                    ?? $item['StartPrice']['value']
                    ?? $item['SellingStatus']['CurrentPrice']['value']
                    ?? 0);
        $stock    = (int) ($item['Quantity'] ?? 1) - (int) ($item['QuantitySold'] ?? 0);
        $catName  = $item['PrimaryCategory']['CategoryName'] ?? null;
        $desc     = $item['Description'] ?? '';

        // Fotos sammeln
        $photos = [];
        $pic    = $item['PictureDetails'] ?? [];
        if (!empty($pic['PictureURL'])) {
            $urls = is_array($pic['PictureURL']) ? $pic['PictureURL'] : [$pic['PictureURL']];
            $photos = array_values(array_filter($urls));
        }

        // Kategorie anlegen/finden
        $categoryId = null;
        if ($catName) {
            $cat = \App\Models\Category::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($catName)],
                ['name' => $catName, 'is_active' => true]
            );
            $categoryId = $cat->id;
        }

        // Artikel anlegen oder aktualisieren
        $article = \App\Models\Article::updateOrCreate(
            ['ebay_item_id' => $ebayId],
            [
                'title'           => $title,
                'description'     => $desc,
                'price'           => $price > 0 ? $price : 8.25,
                'price_eur'       => $price > 0 ? $price : 8.25,
                'stock'           => max(0, $stock),
                'status'          => $stock > 0 ? 'active' : 'sold',
                'category_id'     => $categoryId,
                'ebay_active'     => true,
                'ebay_sync_status'=> 'synced',
                'ebay_last_sync_at' => now(),
                'primary_image'   => $photos[0] ?? null,
            ]
        );

        $isNew = $article->wasRecentlyCreated;

        // Fotos speichern (nur bei neuen Artikeln oder wenn noch keine vorhanden)
        if ($photos && ($isNew || $article->images()->count() === 0)) {
            $article->images()->delete();
            foreach ($photos as $i => $url) {
                $article->images()->create([
                    'url'        => $url,
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]);
            }
        }

        $isNew ? $stats['imported']++ : $stats['updated']++;
    }

    /**
     * Aktuelle eBay-Listings abrufen und mit Shop abgleichen.
     */
    public function syncFromEbay(): array
    {
        $stats = ['updated' => 0, 'sold' => 0, 'errors' => 0];

        $xml      = $this->buildRequest('GetMyeBaySelling', '
            <ActiveList>
                <Include>true</Include>
                <Pagination><EntriesPerPage>200</EntriesPerPage></Pagination>
            </ActiveList>
        ');
        $response = $this->call('GetMyeBaySelling', $xml);

        if (!$response || ($response['Ack'] ?? '') !== 'Success') {
            Log::error('eBay Sync: GetMyeBaySelling fehlgeschlagen', $response ?? []);
            $stats['errors']++;
            return $stats;
        }

        // Aktive eBay-IDs sammeln
        $activeIds = collect($response['ActiveList']['ItemArray']['Item'] ?? [])
            ->pluck('ItemID')
            ->toArray();

        // Artikel die laut eBay verkauft wurden
        Article::where('ebay_active', true)
            ->whereNotNull('ebay_item_id')
            ->whereNotIn('ebay_item_id', $activeIds)
            ->where('status', 'active')
            ->each(function (Article $article) use (&$stats) {
                $article->markAsSold();
                $stats['sold']++;

                EbaySyncLog::create([
                    'article_id'   => $article->id,
                    'ebay_item_id' => $article->ebay_item_id,
                    'action'       => 'sync_sold',
                    'direction'    => 'ebay_to_shop',
                    'status'       => 'success',
                    'message'      => 'Auf eBay verkauft – im Shop als verkauft markiert',
                ]);
            });

        return $stats;
    }

    // -------------------------------------------------------------------------

    private function isOAuthToken(): bool
    {
        return str_starts_with($this->authToken, 'v^1');
    }

    private function buildRequest(string $callName, string $body): string
    {
        // Bei OAuth: kein Token im XML-Body (kommt als Bearer-Header)
        $credentials = $this->isOAuthToken() ? '' : "
            <RequesterCredentials>
                <eBayAuthToken>{$this->authToken}</eBayAuthToken>
            </RequesterCredentials>";

        return <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <{$callName}Request xmlns="urn:ebay:apis:eBLBaseComponents">
            {$credentials}
            {$body}
        </{$callName}Request>
        XML;
    }

    private function call(string $callName, string $xml): ?array
    {
        $headers = [
            'X-EBAY-API-COMPATIBILITY-LEVEL' => '1155',
            'X-EBAY-API-DEV-NAME'            => $this->devId,
            'X-EBAY-API-APP-NAME'            => $this->appId,
            'X-EBAY-API-CERT-NAME'           => $this->certId,
            'X-EBAY-API-CALL-NAME'           => $callName,
            'X-EBAY-API-SITEID'              => '77', // eBay.de
            'Content-Type'                   => 'text/xml',
        ];

        // OAuth Token als Bearer-Header
        if ($this->isOAuthToken()) {
            $headers['Authorization'] = 'Bearer ' . $this->authToken;
        }

        try {
            $response = Http::withHeaders($headers)->send('POST', $this->apiUrl, ['body' => $xml]);

            if (!$response->successful()) {
                return ['error' => "HTTP {$response->status()}"];
            }

            $xml = simplexml_load_string($response->body());
            return $xml ? json_decode(json_encode($xml), true) : null;

        } catch (\Exception $e) {
            Log::error("eBay API Fehler ({$callName}): " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
