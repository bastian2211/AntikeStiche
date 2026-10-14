<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * eBay Marketplace Account Deletion Notifications
 *
 * eBay schickt zwei Arten von Anfragen:
 *
 * 1. GET ?challenge_code=xxx
 *    → Bestätigung dass die URL uns gehört (Challenge-Response)
 *    → Antwort: JSON mit challengeResponse (Hash)
 *
 * 2. POST mit JSON-Body
 *    → Benachrichtigung dass ein Nutzer seine Daten löschen lässt
 *    → Wir löschen alle gespeicherten Nutzerdaten und antworten mit 200
 *
 * eBay Developer Docs:
 * https://developer.ebay.com/marketplace-account-deletion
 */
class EbayAccountDeletionController extends Controller
{
    /**
     * GET: Challenge-Response (eBay bestätigt Endpoint-Ownership)
     * POST: Account-Deletion-Benachrichtigung
     */
    public function handle(Request $request)
    {
        if ($request->isMethod('GET')) {
            return $this->handleChallenge($request);
        }

        return $this->handleDeletion($request);
    }

    /**
     * Challenge-Response: eBay schickt challenge_code, wir antworten mit Hash.
     * Hash = SHA256( challengeCode + verificationToken + endpoint )
     */
    private function handleChallenge(Request $request)
    {
        $challengeCode = $request->query('challenge_code');

        if (!$challengeCode) {
            return response()->json(['error' => 'Missing challenge_code'], 400);
        }

        $verificationToken = config('services.ebay.deletion_verification_token');
        $endpoint          = config('services.ebay.deletion_endpoint');

        if (!$verificationToken || !$endpoint) {
            Log::error('eBay Account Deletion: EBAY_DELETION_VERIFICATION_TOKEN oder EBAY_DELETION_ENDPOINT fehlt in .env');
            return response()->json(['error' => 'Configuration missing'], 500);
        }

        // Hash exakt wie eBay es erwartet: challengeCode + verificationToken + endpoint
        $hash = hash('sha256', $challengeCode . $verificationToken . $endpoint);

        Log::info('eBay Account Deletion Challenge beantwortet', [
            'challenge_code' => $challengeCode,
        ]);

        return response()->json(['challengeResponse' => $hash]);
    }

    /**
     * POST: eBay meldet dass ein Nutzer seine Daten löschen lässt.
     * Wir löschen alle personenbezogenen Daten die wir von diesem Nutzer gespeichert haben.
     */
    private function handleDeletion(Request $request)
    {
        $payload = $request->json()->all();

        Log::info('eBay Account Deletion Anfrage erhalten', [
            'payload' => $payload,
        ]);

        // eBay-User-ID aus dem Payload
        $ebayUserId   = $payload['userId']        ?? null;
        $ebayUsername = $payload['username']       ?? null;
        $notifId      = $payload['notificationId'] ?? null;

        if (!$ebayUserId && !$ebayUsername) {
            Log::warning('eBay Account Deletion: Kein userId/username im Payload');
            return response('', 200); // eBay erwartet 200 auch bei leeren Payloads
        }

        try {
            DB::transaction(function () use ($ebayUserId, $ebayUsername, $notifId) {
                // Bestellungen die mit diesem eBay-Käufer verknüpft sind.
                // Wir anonymisieren alle personenbezogenen Felder.
                $ordersDeleted = 0;
                $query = Order::query();

                if ($ebayUsername) {
                    $query->where('ebay_buyer_username', $ebayUsername);
                } elseif ($ebayUserId) {
                    // Fallback: eBay-Order-IDs können wir nicht direkt zuordnen,
                    // daher nur loggen wenn keine weiteren Infos vorhanden
                    Log::info('eBay Account Deletion: Nur userId vorhanden, kein Username — keine Zuordnung möglich', [
                        'ebay_user_id' => $ebayUserId,
                    ]);
                }

                if ($ebayUsername) {
                    $ordersDeleted = $query->update([
                        'buyer_name'           => '[gelöscht]',
                        'buyer_email'          => '[gelöscht]',
                        'buyer_phone'          => null,
                        'shipping_name'        => '[gelöscht]',
                        'shipping_street'      => '[gelöscht]',
                        'shipping_city'        => '[gelöscht]',
                        'shipping_zip'         => '[gelöscht]',
                        'ebay_buyer_username'  => null,
                    ]);
                }

                Log::info('eBay Account Deletion: Daten anonymisiert', [
                    'ebay_user_id'     => $ebayUserId,
                    'ebay_username'    => $ebayUsername,
                    'notification_id'  => $notifId,
                    'orders_anonymized'=> $ordersDeleted,
                ]);
            });

        } catch (\Exception $e) {
            Log::error('eBay Account Deletion: Fehler beim Löschen', [
                'error'       => $e->getMessage(),
                'ebay_user_id'=> $ebayUserId,
            ]);
            // eBay erwartet 200 — bei Fehler trotzdem 200 zurück, wir loggen intern
        }

        // eBay erwartet HTTP 200 ohne Body
        return response('', 200);
    }
}
