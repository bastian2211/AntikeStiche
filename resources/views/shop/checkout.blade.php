@extends('layouts.shop')

@section('title', app()->getLocale() === 'en' ? 'Checkout' : 'Bestellung aufgeben')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="font-serif text-2xl text-stone-800 mb-8">
        {{ app()->getLocale() === 'en' ? 'Your Order' : 'Ihre Bestellung' }}
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        {{-- Formular --}}
        <div class="lg:col-span-3">
            <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="article_id" value="{{ $article->id }}">

                {{-- Kontakt --}}
                <fieldset class="bg-white border border-stone-200 rounded-xl p-5 space-y-4">
                    <legend class="font-semibold text-stone-700 px-1">
                        {{ app()->getLocale() === 'en' ? 'Contact Information' : 'Kontaktdaten' }}
                    </legend>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">
                            {{ app()->getLocale() === 'en' ? 'Full Name' : 'Vor- und Nachname' }} *
                        </label>
                        <input type="text" name="buyer_name" value="{{ old('buyer_name') }}" required
                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('buyer_name') border-red-400 @enderror">
                        @error('buyer_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">E-Mail *</label>
                        <input type="email" name="buyer_email" value="{{ old('buyer_email') }}" required
                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('buyer_email') border-red-400 @enderror">
                        @error('buyer_email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">
                            {{ app()->getLocale() === 'en' ? 'Phone (optional)' : 'Telefon (optional)' }}
                        </label>
                        <input type="tel" name="buyer_phone" value="{{ old('buyer_phone') }}"
                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </fieldset>

                {{-- Lieferadresse --}}
                <fieldset class="bg-white border border-stone-200 rounded-xl p-5 space-y-4">
                    <legend class="font-semibold text-stone-700 px-1">
                        {{ app()->getLocale() === 'en' ? 'Shipping Address' : 'Lieferadresse' }}
                    </legend>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">
                            {{ app()->getLocale() === 'en' ? 'Name / Company' : 'Name / Firma' }} *
                        </label>
                        <input type="text" name="shipping_name" value="{{ old('shipping_name') }}" required
                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">
                            {{ app()->getLocale() === 'en' ? 'Street and House Number' : 'Straße und Hausnummer' }} *
                        </label>
                        <input type="text" name="shipping_street" value="{{ old('shipping_street') }}" required
                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">PLZ *</label>
                            <input type="text" name="shipping_zip" value="{{ old('shipping_zip') }}" required
                                   class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-stone-700 mb-1">
                                {{ app()->getLocale() === 'en' ? 'City' : 'Stadt' }} *
                            </label>
                            <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" required
                                   class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">
                                {{ app()->getLocale() === 'en' ? 'Country' : 'Land' }} *
                            </label>
                            <select name="shipping_country" required id="shipping_country"
                                    class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                                <option value="DE" {{ old('shipping_country', 'DE') === 'DE' ? 'selected' : '' }}>🇩🇪 Deutschland</option>
                                <option value="AT" {{ old('shipping_country') === 'AT' ? 'selected' : '' }}>🇦🇹 Österreich</option>
                                <option value="CH" {{ old('shipping_country') === 'CH' ? 'selected' : '' }}>🇨🇭 Schweiz</option>
                                <option value="FR" {{ old('shipping_country') === 'FR' ? 'selected' : '' }}>🇫🇷 Frankreich</option>
                                <option value="NL" {{ old('shipping_country') === 'NL' ? 'selected' : '' }}>🇳🇱 Niederlande</option>
                                <option value="BE" {{ old('shipping_country') === 'BE' ? 'selected' : '' }}>🇧🇪 Belgien</option>
                                <option value="GB" {{ old('shipping_country') === 'GB' ? 'selected' : '' }}>🇬🇧 United Kingdom</option>
                                <option value="US" {{ old('shipping_country') === 'US' ? 'selected' : '' }}>🇺🇸 United States</option>
                                <option value="AU" {{ old('shipping_country') === 'AU' ? 'selected' : '' }}>🇦🇺 Australia</option>
                                <option value="CA" {{ old('shipping_country') === 'CA' ? 'selected' : '' }}>🇨🇦 Canada</option>
                                <option value="JP" {{ old('shipping_country') === 'JP' ? 'selected' : '' }}>🇯🇵 Japan</option>
                                <option value="OTHER" {{ old('shipping_country') === 'OTHER' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'en' ? 'Other country' : 'Anderes Land' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">
                                {{ app()->getLocale() === 'en' ? 'State/Region (optional)' : 'Bundesland (optional)' }}
                            </label>
                            <input type="text" name="shipping_state" value="{{ old('shipping_state') }}"
                                   class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                    </div>
                </fieldset>

                {{-- Zahlungsmethode --}}
                <fieldset class="bg-white border border-stone-200 rounded-xl p-5 space-y-3">
                    <legend class="font-semibold text-stone-700 px-1">
                        {{ app()->getLocale() === 'en' ? 'Payment Method' : 'Zahlungsmethode' }}
                    </legend>

                    <label class="flex items-center gap-3 p-3 border border-stone-200 rounded-lg cursor-pointer hover:border-amber-400 transition-colors has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                        <input type="radio" name="payment_method" value="paypal" {{ old('payment_method') === 'paypal' ? 'checked' : '' }} class="text-amber-500" required>
                        <span class="font-medium text-stone-700">PayPal</span>
                        <span class="text-xs text-stone-400 ml-auto">{{ app()->getLocale() === 'en' ? 'Instant payment' : 'Sofortige Zahlung' }}</span>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-stone-200 rounded-lg cursor-pointer hover:border-amber-400 transition-colors has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                        <input type="radio" name="payment_method" value="stripe" {{ old('payment_method') === 'stripe' ? 'checked' : '' }} class="text-amber-500">
                        <span class="font-medium text-stone-700">{{ app()->getLocale() === 'en' ? 'Credit Card' : 'Kreditkarte' }}</span>
                        <span class="text-xs text-stone-400 ml-auto">Visa · Mastercard · Amex</span>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-stone-200 rounded-lg cursor-pointer hover:border-amber-400 transition-colors has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                        <input type="radio" name="payment_method" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }} class="text-amber-500">
                        <span class="font-medium text-stone-700">{{ app()->getLocale() === 'en' ? 'Bank Transfer' : 'Banküberweisung' }}</span>
                        <span class="text-xs text-stone-400 ml-auto">{{ app()->getLocale() === 'en' ? '2–3 days' : '2–3 Werktage' }}</span>
                    </label>
                    @error('payment_method')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                </fieldset>

                {{-- AGB --}}
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="accept_terms" id="accept_terms" value="1"
                           {{ old('accept_terms') ? 'checked' : '' }}
                           class="mt-0.5 text-amber-500" required>
                    <label for="accept_terms" class="text-sm text-stone-600">
                        {{ app()->getLocale() === 'en' ? 'I accept the' : 'Ich akzeptiere die' }}
                        <a href="{{ route('page.show', 'agb') }}" class="text-amber-600 hover:underline" target="_blank">
                            {{ app()->getLocale() === 'en' ? 'Terms & Conditions' : 'Allgemeinen Geschäftsbedingungen' }}
                        </a>
                        {{ app()->getLocale() === 'en' ? 'and the' : 'und die' }}
                        <a href="{{ route('page.show', 'datenschutz') }}" class="text-amber-600 hover:underline" target="_blank">
                            {{ app()->getLocale() === 'en' ? 'Privacy Policy' : 'Datenschutzerklärung' }}
                        </a>.
                    </label>
                </div>
                @error('accept_terms')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

                <button type="submit"
                        class="w-full py-4 bg-amber-500 hover:bg-amber-400 text-stone-900 font-bold text-lg rounded-xl transition-colors shadow-sm">
                    {{ app()->getLocale() === 'en' ? '🛒 Place Order' : '🛒 Bestellung aufgeben' }}
                </button>
            </form>
        </div>

        {{-- Bestellübersicht --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-stone-200 rounded-xl p-5 sticky top-20">
                <h2 class="font-semibold text-stone-700 mb-4">
                    {{ app()->getLocale() === 'en' ? 'Order Summary' : 'Bestellübersicht' }}
                </h2>

                {{-- Artikel --}}
                <div class="flex gap-3 mb-4">
                    @if($article->primaryImage())
                    <img src="{{ $article->primaryImage()->thumbnail_url }}" alt="{{ $article->title }}"
                         class="w-20 h-20 object-cover rounded-lg shrink-0">
                    @endif
                    <div>
                        <p class="font-medium text-stone-800 text-sm leading-snug">{{ $article->title_localized }}</p>
                        <p class="text-xs text-stone-400 mt-1">{{ $article->sku }}</p>
                        <p class="font-semibold text-stone-800 mt-2">{{ number_format($article->price, 2, ',', '.') }} €</p>
                    </div>
                </div>

                <div class="border-t border-stone-100 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-stone-600">
                        <span>{{ app()->getLocale() === 'en' ? 'Item price' : 'Artikelpreis' }}</span>
                        <span>{{ number_format($article->price, 2, ',', '.') }} €</span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>{{ app()->getLocale() === 'en' ? 'Shipping (DE)' : 'Versand (DE)' }}</span>
                        <span>
                            @if($article->shipping_cost_de > 0)
                                {{ number_format($article->shipping_cost_de, 2, ',', '.') }} €
                            @else
                                {{ app()->getLocale() === 'en' ? 'Free' : 'Kostenlos' }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between font-bold text-stone-800 border-t border-stone-100 pt-2 text-base">
                        <span>Total</span>
                        <span>{{ number_format($article->price + $article->shipping_cost_de, 2, ',', '.') }} €</span>
                    </div>
                </div>

                <p class="text-xs text-stone-400 mt-4 text-center">
                    {{ app()->getLocale() === 'en'
                        ? 'Final shipping cost depends on your country.'
                        : 'Endgültige Versandkosten hängen von Ihrem Land ab.' }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
