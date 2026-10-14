<?php

return [

    /*
    |--------------------------------------------------------------------------
    | eBay Trading API
    |--------------------------------------------------------------------------
    */
    'ebay' => [
        'app_id'     => env('EBAY_APP_ID', ''),
        'cert_id'    => env('EBAY_CERT_ID', ''),
        'dev_id'     => env('EBAY_DEV_ID', ''),
        'auth_token' => env('EBAY_AUTH_TOKEN', ''),
        'sandbox'    => env('EBAY_SANDBOX', false),

        // Marketplace Account Deletion Notification
        'deletion_verification_token' => env('EBAY_DELETION_VERIFICATION_TOKEN', ''),
        'deletion_endpoint'           => env('EBAY_DELETION_ENDPOINT', 'https://antikestiche.de/api/ebay/account-deletion'),
    ],

];
