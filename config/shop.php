<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shop-Grundeinstellungen
    |--------------------------------------------------------------------------
    */

    'name'            => env('SHOP_NAME', 'Antiquariat – Antike Stiche & Druckgrafiken'),
    'tagline'         => env('SHOP_TAGLINE', 'Historische Druckgrafiken, Landkarten & Kupferstiche'),
    'owner_name'      => env('SHOP_OWNER_NAME', 'Inhaber Name'),
    'contact_email'   => env('SHOP_CONTACT_EMAIL', 'info@antike-stiche.de'),
    'contact_phone'   => env('SHOP_CONTACT_PHONE', ''),

    /*
    |--------------------------------------------------------------------------
    | Bankverbindung (für Überweisung)
    |--------------------------------------------------------------------------
    */

    'bank_iban'       => env('SHOP_BANK_IBAN', ''),
    'bank_bic'        => env('SHOP_BANK_BIC', ''),
    'bank_name'       => env('SHOP_BANK_NAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Mehrwertsteuer
    |--------------------------------------------------------------------------
    */

    'vat_rate'        => env('SHOP_VAT_RATE', 0),       // 0 = Differenzbesteuerung (typisch Antiquariat)
    'vat_included'    => env('SHOP_VAT_INCLUDED', true), // Preise inkl. MwSt.

    /*
    |--------------------------------------------------------------------------
    | Standard-Versandkosten (€)
    |--------------------------------------------------------------------------
    */

    'shipping_de'     => env('SHOP_SHIPPING_DE', 3.80),
    'shipping_eu'     => env('SHOP_SHIPPING_EU', 6.50),
    'shipping_world'  => env('SHOP_SHIPPING_WORLD', 12.00),

    /*
    |--------------------------------------------------------------------------
    | Lokalisierung
    |--------------------------------------------------------------------------
    */

    'supported_locales' => ['de', 'en'],
    'default_locale'    => 'de',

    /*
    |--------------------------------------------------------------------------
    | Zahlungsanbieter
    |--------------------------------------------------------------------------
    */

    'payment_methods' => [
        'paypal'        => env('SHOP_PAYMENT_PAYPAL', true),
        'stripe'        => env('SHOP_PAYMENT_STRIPE', true),
        'bank_transfer' => env('SHOP_PAYMENT_BANK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | eBay-Sync
    |--------------------------------------------------------------------------
    */

    'ebay_sync_enabled'  => env('EBAY_SYNC_ENABLED', false),
    'ebay_sync_interval' => env('EBAY_SYNC_INTERVAL', 30), // Minuten

    /*
    |--------------------------------------------------------------------------
    | Bilder
    |--------------------------------------------------------------------------
    */

    'image_max_width'    => 1600,
    'image_max_height'   => 1600,
    'thumb_size'         => 400,

    /*
    |--------------------------------------------------------------------------
    | Artikel pro Seite
    |--------------------------------------------------------------------------
    */

    'articles_per_page'  => 24,

    /*
    |--------------------------------------------------------------------------
    | DeepL (automatische Übersetzung)
    |--------------------------------------------------------------------------
    */

    'deepl_enabled'      => env('DEEPL_ENABLED', false),
    'deepl_api_key'      => env('DEEPL_API_KEY', ''),

];
