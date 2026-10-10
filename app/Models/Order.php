<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'source', 'ebay_order_id',
        'buyer_name', 'buyer_email', 'buyer_phone',
        'shipping_name', 'shipping_street', 'shipping_city',
        'shipping_zip', 'shipping_country', 'shipping_state',
        'subtotal', 'shipping_cost', 'total', 'currency',
        'vat_rate', 'vat_amount',
        'payment_method', 'payment_status', 'payment_reference', 'paid_at',
        'status', 'tracking_number', 'shipping_provider',
        'notes', 'compliance_hints',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total'         => 'decimal:2',
        'vat_rate'      => 'decimal:2',
        'vat_amount'    => 'decimal:2',
        'paid_at'       => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ANT-' . date('Ymd') . '-' . strtoupper(Str::random(5));
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canBeShipped(): bool
    {
        return $this->isPaid() && in_array($this->status, ['confirmed', 'processing']);
    }

    /**
     * Gibt länderspezifische Hinweise zurück die im Admin angezeigt werden
     */
    public static function getComplianceHints(string $countryCode): array
    {
        $hints = [];

        $euCountries = ['AT','BE','BG','CY','CZ','DK','EE','ES','FI','FR',
                        'GR','HR','HU','IE','IT','LT','LU','LV','MT','NL',
                        'PL','PT','RO','SE','SI','SK'];

        if ($countryCode === 'GB') {
            $hints[] = '🇬🇧 UK: Seit Brexit gilt UK VAT. Bei Sendungen über £135 muss UK-VAT erhoben werden. Zollanmeldung CN22/CN23 erforderlich.';
            $hints[] = 'HS-Code für Grafiken/Kunstdrucke: 4911.91 oder 9702 (Originalkunstwerk).';
        } elseif ($countryCode === 'US') {
            $hints[] = '🇺🇸 USA: Sales Tax variiert je nach Bundesstaat. Bei niedrigem Umsatz meist keine Pflicht. Zollanmeldung CN22/CN23 erforderlich.';
            $hints[] = 'Customs value muss korrekt deklariert werden. Keine Unterdeklaration.';
        } elseif ($countryCode === 'CH') {
            $hints[] = '🇨🇭 Schweiz: Nicht EU! Zollanmeldung + Einfuhrumsatzsteuer 7,7% für Käufer. CN22/CN23 ausfüllen.';
            $hints[] = 'Sendungen bis CHF 65 Warenwert meist zollfrei.';
        } elseif ($countryCode === 'AU') {
            $hints[] = '🇦🇺 Australien: GST 10% bei Warenwert über AUD 1000. Zollanmeldung erforderlich.';
        } elseif ($countryCode === 'CA') {
            $hints[] = '🇨🇦 Kanada: GST/HST je nach Provinz. Zollanmeldung CN22/CN23 erforderlich.';
        } elseif (in_array($countryCode, $euCountries, true)) {
            $hints[] = '🇪🇺 EU-Inland: Keine Zollanmeldung nötig. Sendung als Warenpaket mit Rechnung.';
        } elseif ($countryCode === 'DE') {
            $hints[] = '🇩🇪 Deutschland: Standard-Versand. DHL Päckchen oder Paket je nach Gewicht.';
        }

        return $hints;
    }
}
