<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    /**
     * Stripe Webhook – Zahlungsbestätigungen verarbeiten.
     */
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        // Signatur prüfen (wenn Secret konfiguriert)
        if ($secret) {
            try {
                \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (\Exception $e) {
                Log::warning('Stripe Webhook: ungültige Signatur', ['error' => $e->getMessage()]);
                return response('Invalid signature', 400);
            }
        }

        $event = json_decode($payload, true);

        match ($event['type'] ?? '') {
            'payment_intent.succeeded'        => $this->handlePaymentSucceeded($event['data']['object']),
            'payment_intent.payment_failed'   => $this->handlePaymentFailed($event['data']['object']),
            default                           => null,
        };

        return response('OK', 200);
    }

    private function handlePaymentSucceeded(array $paymentIntent): void
    {
        $orderNumber = $paymentIntent['metadata']['order_number'] ?? null;
        if (!$orderNumber) return;

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) return;

        $order->update([
            'payment_status'    => 'paid',
            'payment_reference' => $paymentIntent['id'],
            'status'            => 'confirmed',
        ]);

        Log::info("Stripe Zahlung für Bestellung {$orderNumber} bestätigt");
    }

    private function handlePaymentFailed(array $paymentIntent): void
    {
        $orderNumber = $paymentIntent['metadata']['order_number'] ?? null;
        if (!$orderNumber) return;

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) return;

        // Artikel wieder freigeben wenn Payment fehlschlägt
        foreach ($order->items as $item) {
            if ($item->article) {
                $item->article->update(['status' => 'active', 'stock' => 1]);
            }
        }

        $order->update(['status' => 'cancelled']);

        Log::warning("Stripe Zahlung für Bestellung {$orderNumber} fehlgeschlagen");
    }
}
