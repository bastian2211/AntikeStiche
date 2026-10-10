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

    private function buildRequest(string $callName, string $body): string
    {
        return <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <{$callName}Request xmlns="urn:ebay:apis:eBLBaseComponents">
            <RequesterCredentials>
                <eBayAuthToken>{$this->authToken}</eBayAuthToken>
            </RequesterCredentials>
            {$body}
        </{$callName}Request>
        XML;
    }

    private function call(string $callName, string $xml): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-EBAY-API-COMPATIBILITY-LEVEL' => '1155',
                'X-EBAY-API-DEV-NAME'            => $this->devId,
                'X-EBAY-API-APP-NAME'            => $this->appId,
                'X-EBAY-API-CERT-NAME'           => $this->certId,
                'X-EBAY-API-CALL-NAME'           => $callName,
                'X-EBAY-API-SITEID'              => '77', // eBay.de
                'Content-Type'                   => 'text/xml',
            ])->send('POST', $this->apiUrl, ['body' => $xml]);

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
