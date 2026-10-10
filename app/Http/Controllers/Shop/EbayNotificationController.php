<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\EbaySyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EbayNotificationController extends Controller
{
    /**
     * eBay Platform Notifications (Verkäufe, Preisänderungen etc.)
     *
     * eBay sendet XML-Benachrichtigungen bei:
     * - FixedPriceTransaction: Artikel verkauft
     * - AuctionCheckoutComplete: Auktion beendet + bezahlt
     * - ItemSold: Artikel verkauft
     */
    public function handle(Request $request)
    {
        $body = $request->getContent();
        Log::debug('eBay Notification empfangen', ['body' => substr($body, 0, 500)]);

        // XML parsen
        try {
            $xml = simplexml_load_string($body);
            if (!$xml) {
                return response('Parse error', 400);
            }
        } catch (\Exception $e) {
            return response('Invalid XML', 400);
        }

        $ns = $xml->getNamespaces(true);
        $notificationType = (string) ($xml->children($ns[''] ?? '')->NotificationDetails->NotificationType ?? '');

        match ($notificationType) {
            'FixedPriceTransaction',
            'AuctionCheckoutComplete',
            'ItemSold' => $this->handleItemSold($xml, $ns),
            default    => Log::debug("eBay Notification ignoriert: {$notificationType}"),
        };

        return response('<Ack>Success</Ack>', 200)
            ->header('Content-Type', 'text/xml');
    }

    private function handleItemSold($xml, array $ns): void
    {
        $itemId = (string) ($xml->children($ns[''] ?? '')->NotificationDetails->ItemID ?? '');
        if (!$itemId) return;

        $article = Article::where('ebay_item_id', $itemId)->first();
        if (!$article) {
            Log::warning("eBay Verkauf: Artikel {$itemId} nicht in Datenbank");
            return;
        }

        // Oversell-Schutz: Artikel sofort als verkauft markieren
        $article->markAsSold();

        EbaySyncLog::create([
            'article_id'   => $article->id,
            'ebay_item_id' => $itemId,
            'action'       => 'item_sold',
            'direction'    => 'ebay_to_shop',
            'status'       => 'success',
            'message'      => "Artikel als verkauft markiert (eBay Notification: {$itemId})",
        ]);

        Log::info("eBay Verkauf: Artikel {$article->sku} ({$itemId}) als verkauft markiert");
    }
}
