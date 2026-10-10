<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    /**
     * Bestellformular anzeigen
     */
    public function show(Request $request, int $articleId)
    {
        $article = Article::with('images')->findOrFail($articleId);

        if (! $article->isAvailable()) {
            return redirect()->route('artikel.show', $articleId)
                ->with('error', 'Dieser Artikel ist leider nicht mehr verfügbar.');
        }

        return view('shop.checkout', compact('article'));
    }

    /**
     * Bestellung aufgeben
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id'      => 'required|integer|exists:articles,id',
            'buyer_name'      => 'required|string|max:255',
            'buyer_email'     => 'required|email|max:255',
            'buyer_phone'     => 'nullable|string|max:50',
            'shipping_name'   => 'required|string|max:255',
            'shipping_street' => 'required|string|max:255',
            'shipping_city'   => 'required|string|max:255',
            'shipping_zip'    => 'required|string|max:20',
            'shipping_country' => 'required|string|size:2',
            'shipping_state'  => 'nullable|string|max:100',
            'payment_method'  => 'required|in:paypal,stripe,bank_transfer',
            'accept_terms'    => 'required|accepted',
        ]);

        $article = Article::lockForUpdate()->findOrFail($validated['article_id']);

        // Nochmal prüfen ob noch verfügbar (Oversell-Schutz)
        if (! $article->isAvailable()) {
            return back()->with('error', 'Dieser Artikel wurde soeben verkauft. Bitte wählen Sie einen anderen Artikel.');
        }

        $country      = $validated['shipping_country'];
        $shippingCost = $article->getShippingCostForCountry($country);
        $subtotal     = (float) $article->price;
        $total        = $subtotal + $shippingCost;

        // Länderspezifische Compliance-Hinweise speichern
        $complianceHints = Order::getComplianceHints($country);

        DB::beginTransaction();
        try {
            // Artikel sofort reservieren (Oversell-Schutz)
            $article->update(['status' => 'reserved']);

            $order = Order::create([
                'order_number'     => Order::generateOrderNumber(),
                'source'           => 'shop',
                'buyer_name'       => $validated['buyer_name'],
                'buyer_email'      => $validated['buyer_email'],
                'buyer_phone'      => $validated['buyer_phone'] ?? null,
                'shipping_name'    => $validated['shipping_name'],
                'shipping_street'  => $validated['shipping_street'],
                'shipping_city'    => $validated['shipping_city'],
                'shipping_zip'     => $validated['shipping_zip'],
                'shipping_country' => $country,
                'shipping_state'   => $validated['shipping_state'] ?? null,
                'subtotal'         => $subtotal,
                'shipping_cost'    => $shippingCost,
                'total'            => $total,
                'currency'         => 'EUR',
                'payment_method'   => $validated['payment_method'],
                'payment_status'   => 'pending',
                'status'           => 'pending',
                'compliance_hints' => implode("\n", $complianceHints),
            ]);

            OrderItem::create([
                'order_id'      => $order->id,
                'article_id'    => $article->id,
                'article_sku'   => $article->sku,
                'article_title' => $article->title,
                'price'         => $article->price,
                'quantity'      => 1,
            ]);

            DB::commit();

            // Weiterleitung zur Zahlung
            return match ($validated['payment_method']) {
                'paypal'       => redirect()->route('checkout.paypal', $order->id),
                'stripe'       => redirect()->route('checkout.stripe', $order->id),
                'bank_transfer' => redirect()->route('checkout.bank-transfer', $order->id),
            };

        } catch (\Throwable $e) {
            DB::rollBack();
            $article->update(['status' => 'active']); // Reservierung rückgängig
            Log::error('Checkout error: ' . $e->getMessage(), ['article_id' => $article->id]);
            return back()->with('error', 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
        }
    }

    /**
     * Banküberweisung Bestätigungsseite
     */
    public function bankTransfer(int $orderId)
    {
        $order = Order::with('items')->findOrFail($orderId);
        return view('shop.checkout.bank-transfer', compact('order'));
    }

    /**
     * PayPal Weiterleitung (Platzhalter — wird mit PayPal SDK implementiert)
     */
    public function paypal(int $orderId)
    {
        $order = Order::findOrFail($orderId);
        // TODO: PayPal SDK Integration
        return view('shop.checkout.paypal', compact('order'));
    }

    /**
     * Stripe Weiterleitung (Platzhalter — wird mit Stripe SDK implementiert)
     */
    public function stripe(int $orderId)
    {
        $order = Order::findOrFail($orderId);
        // TODO: Stripe SDK Integration
        return view('shop.checkout.stripe', compact('order'));
    }

    /**
     * Bestellung erfolgreich (nach Zahlung)
     */
    public function success(int $orderId)
    {
        $order = Order::with('items.article')->findOrFail($orderId);

        // Artikel als verkauft markieren
        foreach ($order->items as $item) {
            if ($item->article) {
                $item->article->markAsSold();
            }
        }

        $order->update(['payment_status' => 'paid', 'status' => 'confirmed', 'paid_at' => now()]);

        return view('shop.checkout.success', compact('order'));
    }

    /**
     * Zahlung abgebrochen
     */
    public function cancel(int $orderId)
    {
        $order = Order::with('items.article')->findOrFail($orderId);

        // Artikel wieder freischalten
        foreach ($order->items as $item) {
            if ($item->article && $item->article->status === 'reserved') {
                $item->article->update(['status' => 'active', 'stock' => 1]);
            }
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('artikel.show', $order->items->first()?->article_id)
            ->with('info', 'Die Zahlung wurde abgebrochen. Der Artikel ist wieder verfügbar.');
    }
}
