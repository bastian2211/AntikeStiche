@extends('layouts.admin')

@section('page-title', 'Bestellung ' . $order->order_number)

@section('header-actions')
<a href="{{ route('admin.orders.index') }}" class="text-sm text-stone-500 hover:text-stone-700">← Zurück</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Hauptbereich --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Länder-Compliance Hinweise (wichtig!) --}}
        @if(count($complianceHints) > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <h3 class="font-semibold text-amber-800 mb-2 flex items-center gap-2">
                ⚠️ Hinweise für Versand nach {{ $order->shipping_country }}
            </h3>
            <ul class="space-y-1.5">
                @foreach($complianceHints as $hint)
                <li class="text-sm text-amber-700">{{ $hint }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Artikel --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-700 mb-4">Bestellte Artikel</h2>
            <div class="divide-y divide-stone-100">
                @foreach($order->items as $item)
                <div class="py-3 flex items-center gap-4">
                    @if($item->article?->primaryImage())
                    <img src="{{ $item->article->primaryImage()->thumbnail_url }}" alt=""
                         class="w-16 h-16 object-cover rounded-lg shrink-0">
                    @endif
                    <div class="flex-1">
                        <p class="font-medium text-stone-800">{{ $item->article_title }}</p>
                        <p class="text-xs font-mono text-stone-400 mt-0.5">{{ $item->article_sku }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-stone-800">{{ number_format($item->price, 2, ',', '.') }} €</p>
                        <p class="text-xs text-stone-400">{{ $item->quantity }}×</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="border-t border-stone-100 mt-3 pt-3 space-y-1 text-sm">
                <div class="flex justify-between text-stone-600">
                    <span>Zwischensumme</span>
                    <span>{{ number_format($order->subtotal, 2, ',', '.') }} €</span>
                </div>
                <div class="flex justify-between text-stone-600">
                    <span>Versandkosten</span>
                    <span>{{ number_format($order->shipping_cost, 2, ',', '.') }} €</span>
                </div>
                @if($order->vat_amount > 0)
                <div class="flex justify-between text-stone-600">
                    <span>MwSt. ({{ $order->vat_rate }}%)</span>
                    <span>{{ number_format($order->vat_amount, 2, ',', '.') }} €</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-stone-800 text-base border-t border-stone-100 pt-2">
                    <span>Gesamt</span>
                    <span>{{ number_format($order->total, 2, ',', '.') }} {{ $order->currency }}</span>
                </div>
            </div>
        </div>

        {{-- Status aktualisieren --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-700 mb-4">Status aktualisieren</h2>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Bestellstatus</label>
                        <select name="status" class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none">
                            @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $s)
                            <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Versanddienstleister</label>
                        <input type="text" name="shipping_provider" value="{{ $order->shipping_provider }}"
                               placeholder="DHL, Deutsche Post..."
                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Sendungsnummer</label>
                    <input type="text" name="tracking_number" value="{{ $order->tracking_number }}"
                           placeholder="Trackingnummer eingeben"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Interne Notizen</label>
                    <textarea name="notes" rows="3" placeholder="Interne Anmerkungen..."
                              class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none text-sm">{{ $order->notes }}</textarea>
                </div>

                <button type="submit" class="px-6 py-2 bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold rounded-lg transition-colors">
                    💾 Speichern
                </button>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">

        {{-- Bestellinfos --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-700 mb-3">Bestellung</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-stone-500">Nummer</dt>
                    <dd class="font-mono font-medium">{{ $order->order_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-stone-500">Datum</dt>
                    <dd>{{ $order->created_at->format('d.m.Y H:i') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-stone-500">Quelle</dt>
                    <dd class="uppercase font-semibold text-xs {{ $order->source === 'ebay' ? 'text-amber-600' : 'text-stone-600' }}">
                        {{ $order->source }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-stone-500">Zahlung</dt>
                    <dd>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-stone-500">Zahlungsart</dt>
                    <dd>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? '—')) }}</dd>
                </div>
                @if($order->payment_reference)
                <div class="flex justify-between">
                    <dt class="text-stone-500">Referenz</dt>
                    <dd class="font-mono text-xs truncate max-w-32">{{ $order->payment_reference }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Kunde --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-700 mb-3">Käufer</h2>
            <div class="text-sm space-y-1.5 text-stone-700">
                <p class="font-semibold">{{ $order->buyer_name }}</p>
                <p><a href="mailto:{{ $order->buyer_email }}" class="text-amber-600 hover:underline">{{ $order->buyer_email }}</a></p>
                @if($order->buyer_phone)
                <p>{{ $order->buyer_phone }}</p>
                @endif
            </div>
        </div>

        {{-- Lieferadresse --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-700 mb-3">Lieferadresse</h2>
            <address class="text-sm text-stone-700 not-italic leading-relaxed">
                {{ $order->shipping_name }}<br>
                {{ $order->shipping_street }}<br>
                {{ $order->shipping_zip }} {{ $order->shipping_city }}<br>
                @if($order->shipping_state){{ $order->shipping_state }}<br>@endif
                <strong>{{ $order->shipping_country }}</strong>
            </address>
        </div>

    </div>
</div>
@endsection
