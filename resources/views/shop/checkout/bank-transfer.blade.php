@extends('layouts.shop')

@section('title', 'Banküberweisung – Bestellung ' . $order->order_number)

@section('content')
<div class="max-w-xl mx-auto px-4 py-12">

    <div class="text-center mb-8">
        <div class="text-5xl mb-4">🏦</div>
        <h1 class="text-2xl font-bold text-stone-800">Bitte überweisen Sie jetzt</h1>
        <p class="text-stone-500 mt-2">Ihre Bestellung wird nach Zahlungseingang versendet</p>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6">
        <h2 class="font-bold text-stone-800 mb-4">Bankverbindung</h2>
        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="text-stone-500">Empfänger</dt>
                <dd class="font-semibold text-stone-800">{{ config('shop.owner_name', 'Inhaber') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-stone-500">IBAN</dt>
                <dd class="font-mono font-semibold text-stone-800">{{ config('shop.bank_iban', 'DEXX XXXX XXXX XXXX XXXX XX') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-stone-500">BIC</dt>
                <dd class="font-mono font-semibold text-stone-800">{{ config('shop.bank_bic', 'XXXXXXXX') }}</dd>
            </div>
            <div class="flex justify-between border-t border-amber-200 pt-3">
                <dt class="text-stone-500">Betrag</dt>
                <dd class="text-xl font-bold text-stone-800">{{ number_format($order->total, 2, ',', '.') }} €</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-stone-500 shrink-0">Verwendungszweck</dt>
                <dd class="font-semibold text-amber-700 text-right font-mono">{{ $order->order_number }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white border border-stone-200 rounded-2xl p-5 mb-6">
        <h2 class="font-semibold text-stone-700 mb-3">Ihre Bestellung</h2>
        <div class="flex justify-between text-sm text-stone-600 mb-1">
            <span>Bestellnummer</span>
            <span class="font-mono">{{ $order->order_number }}</span>
        </div>
        <div class="flex justify-between text-sm text-stone-600 mb-1">
            <span>Lieferadresse</span>
            <span class="text-right">{{ $order->shipping_name }}, {{ $order->shipping_city }}</span>
        </div>
        <div class="flex justify-between text-sm text-stone-600">
            <span>Zahlungsfrist</span>
            <span>7 Tage ab heute</span>
        </div>
    </div>

    <div class="text-center text-sm text-stone-500">
        <p>Eine Bestellbestätigung wurde an <strong>{{ $order->buyer_email }}</strong> gesendet.</p>
        <p class="mt-2">Bei Fragen: <a href="mailto:{{ config('shop.contact_email', 'info@antiquariat.de') }}" class="text-amber-600 hover:underline">{{ config('shop.contact_email', 'info@antiquariat.de') }}</a></p>
    </div>

</div>
@endsection
