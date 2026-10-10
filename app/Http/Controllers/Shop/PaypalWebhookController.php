<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalWebhookController extends Controller
{
    /**
     * PayPal IPN / Webhook – Zahlungsbestätigungen.
     */
    public function handle(Request $request)
    {
        $event     = $request->all();
        $eventType = $event['event_type'] ?? $event['payment_status'] ?? null;

        Log::debug('PayPal Webhook', ['type' => $eventType]);

        match ($eventType) {
            'PAYMENT.CAPTURE.COMPLETED',
            'Completed'  => $this->handlePaymentCompleted($event),
            'PAYMENT.CAPTURE.DENIED',
            'Failed'     => $this->handlePaymentFailed($event),
            default      => null,
        };

        return response('OK', 200);
    }

    private function handlePaymentCompleted(array $event): void
    {
        // Bestellnummer aus custom field / invoice_id
        $orderNumber = $event['custom'] ?? $event['invoice'] ?? null;
        if (!$orderNumber) {
            // Aus resource.custom_id (PayPal v2)
            $orderNumber = $event['resource']['custom_id'] ?? null;
        }
        if (!$orderNumber) return;

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) return;

        $txId = $event['resource']['id'] ?? $event['txn_id'] ?? null;

        $order->update([
            'payment_status'    => 'paid',
            'payment_reference' => $txId,
            'status'            => 'confirmed',
        ]);

        Log::info("PayPal Zahlung für Bestellung {$orderNumber} bestätigt");
    }

    private function handlePaymentFailed(array $event): void
    {
        $orderNumber = $event['custom'] ?? $event['invoice'] ?? $event['resource']['custom_id'] ?? null;
        if (!$orderNumber) return;

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) return;

        foreach ($order->items as $item) {
            if ($item->article) {
                $item->article->update(['status' => 'active', 'stock' => 1]);
            }
        }

        $order->update(['status' => 'cancelled']);
        Log::warning("PayPal Zahlung für Bestellung {$orderNumber} fehlgeschlagen");
    }
}
