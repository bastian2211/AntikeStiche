@extends('layouts.shop')

@section('title', 'Bestellung erfolgreich – ' . $order->order_number)

@section('content')
<div class="max-w-xl mx-auto px-4 py-16 text-center">

    <div class="text-6xl mb-6">🎉</div>

    <h1 class="text-3xl font-bold text-stone-800 mb-3">Vielen Dank!</h1>
    <p class="text-lg text-stone-500 mb-8">Ihre Bestellung wurde erfolgreich aufgegeben.</p>

    <div class="bg-white border border-stone-200 rounded-2xl p-6 text-left mb-8">
        <h2 class="font-semibold text-stone-700 mb-4 text-center">Bestellzusammenfassung</h2>

        <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
                <dt class="text-stone-500">Bestellnummer</dt>
                <dd class="font-mono font-semibold text-stone-800">{{ $order->order_number }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-stone-500">Datum</dt>
                <dd class="text-stone-700">{{ $order->created_at->format('d.m.Y, H:i') }} Uhr</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-stone-500">Zahlung</dt>
                <dd class="text-stone-700">
                    @switch($order->payment_method)
                        @case('paypal') PayPal @break
                        @case('stripe') Kreditkarte @break
                        @case('bank_transfer') Banküberweisung @break
                        @default {{ ucfirst($order->payment_method) }}
                    @endswitch
                </dd>
            </div>
            @if($order->payment_method === 'bank_transfer')
            <div class="flex justify-between">
                <dt class="text-stone-500">Zahlungsstatus</dt>
                <dd class="text-amber-600 font-medium">Überweisung ausstehend</dd>
            </div>
            @else
            <div class="flex justify-between">
                <dt class="text-stone-500">Zahlungsstatus</dt>
                <dd class="text-green-600 font-medium">Bezahlt ✓</dd>
            </div>
            @endif
            <div class="flex justify-between border-t border-stone-100 pt-2">
                <dt class="text-stone-600 font-medium">Gesamtbetrag</dt>
                <dd class="font-bold text-stone-800">{{ number_format($order->total, 2, ',', '.') }} €</dd>
            </div>
        </dl>
    </div>

    <div class="bg-stone-50 rounded-xl p-4 text-sm text-stone-600 mb-8">
        <p>Eine Bestätigungs-E-Mail wurde an <strong>{{ $order->buyer_email }}</strong> gesendet.</p>
        <p class="mt-1">Bei Fragen schreiben Sie uns an <a href="mailto:{{ config('shop.contact_email') }}" class="text-amber-600 hover:underline">{{ config('shop.contact_email') }}</a></p>
    </div>

    <a href="{{ route('artikel.index') }}"
       class="inline-block px-8 py-3 bg-amber-500 hover:bg-amber-400 text-stone-900 font-bold rounded-xl transition-colors">
        Weiter stöbern
    </a>

</div>
@endsection
