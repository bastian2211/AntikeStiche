<?php

use App\Http\Controllers\Shop\EbayNotificationController;
use App\Http\Controllers\Shop\PaypalWebhookController;
use App\Http\Controllers\Shop\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Webhooks von externen Diensten
|--------------------------------------------------------------------------
*/

// Stripe Webhook (Zahlungsbestätigungen)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

// PayPal IPN / Webhook
Route::post('/paypal/webhook', [PaypalWebhookController::class, 'handle'])
    ->name('paypal.webhook');

// eBay Platform Notifications (Artikelverkäufe etc.)
Route::post('/ebay/notification', [EbayNotificationController::class, 'handle'])
    ->name('ebay.notification');
