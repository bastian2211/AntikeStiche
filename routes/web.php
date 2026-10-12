<?php

use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ArticleController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use Illuminate\Support\Facades\Route;
use App\Models\Page;

// ═══════════════════════════════════════════════════════════
// SHOP — Öffentliche Routen
// ═══════════════════════════════════════════════════════════

Route::get('/', [HomeController::class, 'index'])->name('home');

// Sprachumschalter
Route::get('/sprache/{locale}', function (string $locale) {
    if (in_array($locale, ['de', 'en'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('locale.switch');

// Artikel
Route::get('/artikel', [ArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{id}', [ArticleController::class, 'show'])->name('artikel.show');

// Checkout
Route::get('/bestellung/{articleId}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/bestellung', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/bestellung/{orderId}/bankueberweisung', [CheckoutController::class, 'bankTransfer'])->name('checkout.bank-transfer');
Route::get('/bestellung/{orderId}/paypal', [CheckoutController::class, 'paypal'])->name('checkout.paypal');
Route::get('/bestellung/{orderId}/stripe', [CheckoutController::class, 'stripe'])->name('checkout.stripe');
Route::get('/bestellung/{orderId}/erfolg', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/bestellung/{orderId}/abbruch', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Statische Seiten (aus Datenbank)
Route::get('/seite/{slug}', function (string $slug) {
    $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();
    return view('shop.page', compact('page'));
})->name('page.show');

// ═══════════════════════════════════════════════════════════
// ADMIN — Geschützte Routen
// ═══════════════════════════════════════════════════════════

Route::prefix('admin')->name('admin.')->group(function () {

    // Auth (ohne Middleware)
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Alle folgenden Routen brauchen Admin-Login
    Route::middleware('auth:admin')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Artikel-Verwaltung
        Route::resource('articles', AdminArticleController::class);
        Route::post('articles/{article}/images', [AdminArticleController::class, 'uploadImage'])->name('articles.images.upload');
        Route::post('articles/{article}/images/reorder', [AdminArticleController::class, 'reorderImages'])->name('articles.images.reorder');
        Route::delete('articles/{article}/images/{image}', [AdminArticleController::class, 'deleteImage'])->name('articles.images.delete');

        // Kategorien
        Route::resource('categories', CategoryController::class);

        // Bestellungen
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);

        // Statische Seiten
        Route::resource('pages', PageController::class);

        // eBay Import
        Route::get('import/test', [\App\Http\Controllers\Admin\ImportController::class, 'testImport'])->name('import.test');
        Route::get('import/full', [\App\Http\Controllers\Admin\ImportController::class, 'fullImport'])->name('import.full');
    });
});

// ═══════════════════════════════════════════════════════════
// API — Webhooks (eBay, Stripe, PayPal)
// ═══════════════════════════════════════════════════════════
Route::prefix('api')->name('api.')->group(function () {
    // Stripe Webhook
    Route::post('/stripe/webhook', [\App\Http\Controllers\Shop\StripeWebhookController::class, 'handle'])
        ->name('stripe.webhook');

    // PayPal Webhook
    Route::post('/paypal/webhook', [\App\Http\Controllers\Shop\PaypalWebhookController::class, 'handle'])
        ->name('paypal.webhook');

    // eBay Notification (Availability Alert)
    Route::post('/ebay/notification', [\App\Http\Controllers\Shop\EbayNotificationController::class, 'handle'])
        ->name('ebay.notification');
});
