@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 font-medium uppercase tracking-wide">Aktive Artikel</p>
        <p class="text-3xl font-bold text-stone-800 mt-1">{{ number_format($stats['articles_active']) }}</p>
        <p class="text-xs text-stone-400 mt-1">von {{ number_format($stats['articles_total']) }} gesamt</p>
    </div>
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 font-medium uppercase tracking-wide">Verkauft</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($stats['articles_sold']) }}</p>
        <p class="text-xs text-stone-400 mt-1">Artikel insgesamt</p>
    </div>
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 font-medium uppercase tracking-wide">Offene Bestellungen</p>
        <p class="text-3xl font-bold {{ $stats['orders_pending'] > 0 ? 'text-amber-600' : 'text-stone-800' }} mt-1">
            {{ $stats['orders_pending'] }}
        </p>
        <p class="text-xs text-stone-400 mt-1">{{ $stats['orders_today'] }} heute</p>
    </div>
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 font-medium uppercase tracking-wide">Umsatz (Monat)</p>
        <p class="text-3xl font-bold text-stone-800 mt-1">{{ number_format($stats['revenue_month'], 0, ',', '.') }} €</p>
        <p class="text-xs text-stone-400 mt-1">{{ now()->format('F Y') }}</p>
    </div>
</div>

{{-- eBay Sync Status --}}
@if($stats['ebay_errors'] > 0 || $stats['ebay_pending_sync'] > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-center gap-4">
    <span class="text-2xl">⚠️</span>
    <div>
        <p class="font-semibold text-amber-800">eBay-Synchronisation</p>
        <p class="text-sm text-amber-700">
            {{ $stats['ebay_pending_sync'] }} ausstehend ·
            {{ $stats['ebay_errors'] }} Fehler
        </p>
    </div>
    <a href="{{ route('admin.articles.index', ['ebay_sync' => 'error']) }}"
       class="ml-auto text-sm text-amber-700 font-medium underline">
        Anzeigen →
    </a>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Neueste Bestellungen --}}
    <div class="bg-white rounded-xl border border-stone-200">
        <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-semibold text-stone-700">Neueste Bestellungen</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-amber-600 hover:text-amber-700">Alle →</a>
        </div>
        <div class="divide-y divide-stone-50">
            @forelse($recentOrders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-stone-50 transition-colors">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-stone-800">{{ $order->order_number }}</p>
                    <p class="text-xs text-stone-500 truncate">{{ $order->buyer_name }} · {{ $order->shipping_country }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-stone-800">{{ number_format($order->total, 2, ',', '.') }} €</p>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ match($order->status) {
                            'pending' => 'bg-amber-100 text-amber-700',
                            'confirmed', 'processing' => 'bg-blue-100 text-blue-700',
                            'shipped' => 'bg-purple-100 text-purple-700',
                            'delivered' => 'bg-green-100 text-green-700',
                            default => 'bg-stone-100 text-stone-500'
                        } }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </a>
            @empty
            <p class="px-5 py-8 text-center text-stone-400 text-sm">Keine Bestellungen vorhanden</p>
            @endforelse
        </div>
    </div>

    {{-- Neueste Artikel --}}
    <div class="bg-white rounded-xl border border-stone-200">
        <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-semibold text-stone-700">Zuletzt hinzugefügt</h2>
            <a href="{{ route('admin.articles.create') }}" class="text-xs bg-amber-500 text-stone-900 font-semibold px-3 py-1 rounded-lg hover:bg-amber-400 transition-colors">
                + Neuer Artikel
            </a>
        </div>
        <div class="divide-y divide-stone-50">
            @forelse($recentArticles as $article)
            <a href="{{ route('admin.articles.edit', $article) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-stone-50 transition-colors">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-stone-800 truncate">{{ $article->title }}</p>
                    <p class="text-xs text-stone-500 font-mono">{{ $article->sku }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-stone-800">{{ number_format($article->price, 2, ',', '.') }} €</p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $article->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-500' }}">
                        {{ ucfirst($article->status) }}
                    </span>
                </div>
            </a>
            @empty
            <p class="px-5 py-8 text-center text-stone-400 text-sm">Keine Artikel vorhanden</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
