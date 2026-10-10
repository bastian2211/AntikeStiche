@extends('layouts.shop')

@section('title', $article->title_localized)
@section('meta_description', Str::limit(strip_tags($article->description_localized), 160))
@section('og_image', $article->primaryImage()?->url)

@section('structured_data', json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-stone-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-stone-700">{{ app()->getLocale() === 'en' ? 'Home' : 'Startseite' }}</a>
        <span>/</span>
        <a href="{{ route('artikel.index') }}" class="hover:text-stone-700">{{ app()->getLocale() === 'en' ? 'All Items' : 'Alle Artikel' }}</a>
        @if($article->category)
        <span>/</span>
        <a href="{{ route('artikel.index', ['kategorie' => $article->category->slug]) }}" class="hover:text-stone-700">
            {{ $article->category->name_localized }}
        </a>
        @endif
        <span>/</span>
        <span class="text-stone-800 truncate max-w-xs">{{ $article->title_localized }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        {{-- Bildergalerie --}}
        <div x-data="imageGallery()" class="space-y-3">

            {{-- Hauptbild --}}
            <div class="relative bg-stone-100 rounded-xl overflow-hidden aspect-square">
                @if($article->images->count() > 0)
                <img :src="activeImage"
                     alt="{{ $article->title_localized }}"
                     class="w-full h-full object-contain cursor-zoom-in"
                     @click="openLightbox()"
                     x-ref="mainImage">
                @else
                <div class="w-full h-full flex items-center justify-center text-stone-400">
                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif

                {{-- Zoom-Hinweis --}}
                @if($article->images->count() > 0)
                <div class="absolute bottom-2 right-2 bg-white/80 text-stone-600 text-xs px-2 py-1 rounded-full">
                    🔍 {{ app()->getLocale() === 'en' ? 'Click to zoom' : 'Klick zum Zoomen' }}
                </div>
                @endif
            </div>

            {{-- Thumbnails --}}
            @if($article->images->count() > 1)
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach($article->images as $image)
                <button @click="setActive('{{ $image->url }}')"
                        :class="activeImage === '{{ $image->url }}' ? 'ring-2 ring-amber-500' : 'ring-1 ring-stone-200'"
                        class="shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-stone-100">
                    <img src="{{ $image->thumbnail_url }}" alt="{{ $image->alt_text }}" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif

            {{-- Lightbox --}}
            <div x-show="lightboxOpen" x-cloak
                 class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4"
                 @click.self="lightboxOpen = false"
                 @keyup.escape.window="lightboxOpen = false">
                <button @click="lightboxOpen = false" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
                <img :src="activeImage" class="max-w-full max-h-full object-contain" alt="">
            </div>
        </div>

        {{-- Artikel-Info --}}
        <div class="space-y-5">
            {{-- Kategorie + SKU --}}
            <div class="flex items-center justify-between text-sm text-stone-500">
                @if($article->category)
                <a href="{{ route('artikel.index', ['kategorie' => $article->category->slug]) }}"
                   class="hover:text-amber-600 transition-colors">
                    {{ $article->category->name_localized }}
                </a>
                @endif
                <span class="font-mono text-xs bg-stone-100 px-2 py-0.5 rounded">{{ $article->sku }}</span>
            </div>

            <h1 class="font-serif text-2xl sm:text-3xl text-stone-800 leading-tight">
                {{ $article->title_localized }}
            </h1>

            {{-- Preis --}}
            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-bold text-stone-900">
                    {{ number_format($article->price, 2, ',', '.') }} €
                </span>
                @if($article->shipping_cost_de > 0)
                <span class="text-sm text-stone-500">
                    + {{ number_format($article->shipping_cost_de, 2, ',', '.') }} €
                    {{ app()->getLocale() === 'en' ? 'shipping (DE)' : 'Versand (DE)' }}
                </span>
                @else
                <span class="text-sm text-green-600 font-medium">
                    {{ app()->getLocale() === 'en' ? 'Free shipping' : 'Versandkostenfrei' }}
                </span>
                @endif
            </div>

            {{-- Verfügbarkeit --}}
            <div class="flex items-center gap-2">
                @if($article->isAvailable())
                <span class="inline-flex items-center gap-1.5 text-sm text-green-700 bg-green-50 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    {{ app()->getLocale() === 'en' ? 'In stock – 1 piece' : 'Auf Lager – 1 Stück' }}
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 text-sm text-red-700 bg-red-50 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    {{ app()->getLocale() === 'en' ? 'Sold' : 'Verkauft' }}
                </span>
                @endif
            </div>

            {{-- Beschreibung --}}
            <div class="prose prose-stone prose-sm max-w-none">
                {!! nl2br(e($article->description_localized)) !!}
            </div>

            {{-- Details Tabelle --}}
            <div class="border-t border-stone-100 pt-4">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-stone-100">
                        <tr>
                            <td class="py-2 text-stone-500 w-1/2">{{ app()->getLocale() === 'en' ? 'Condition' : 'Zustand' }}</td>
                            <td class="py-2 text-stone-800">{{ $article->condition }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-stone-500">{{ app()->getLocale() === 'en' ? 'Item number' : 'Artikelnummer' }}</td>
                            <td class="py-2 font-mono text-stone-800">{{ $article->sku }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-stone-500">{{ app()->getLocale() === 'en' ? 'Shipping EU' : 'Versand EU' }}</td>
                            <td class="py-2 text-stone-800">
                                {{ $article->shipping_cost_eu > 0 ? number_format($article->shipping_cost_eu, 2, ',', '.') . ' €' : (app()->getLocale() === 'en' ? 'Free' : 'Kostenlos') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-stone-500">{{ app()->getLocale() === 'en' ? 'Shipping worldwide' : 'Versand Welt' }}</td>
                            <td class="py-2 text-stone-800">
                                {{ $article->shipping_cost_world > 0 ? number_format($article->shipping_cost_world, 2, ',', '.') . ' €' : (app()->getLocale() === 'en' ? 'Free' : 'Kostenlos') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Kaufen-Button --}}
            @if($article->isAvailable())
            <a href="{{ route('checkout.show', $article->id) }}"
               class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold text-lg rounded-xl transition-colors shadow-sm">
                🛒 {{ app()->getLocale() === 'en' ? 'Buy Now' : 'Jetzt kaufen' }}
            </a>
            <p class="text-xs text-stone-400 text-center">
                {{ app()->getLocale() === 'en'
                    ? 'Secure checkout with PayPal, credit card or bank transfer'
                    : 'Sicherer Kauf mit PayPal, Kreditkarte oder Überweisung' }}
            </p>
            @else
            <div class="w-full text-center py-4 bg-stone-100 text-stone-500 rounded-xl">
                {{ app()->getLocale() === 'en' ? 'This item has been sold.' : 'Dieser Artikel wurde bereits verkauft.' }}
            </div>
            @endif

            {{-- Zahlungsarten --}}
            <div class="flex items-center gap-3 text-stone-400 text-xs border-t border-stone-100 pt-4">
                <span>{{ app()->getLocale() === 'en' ? 'We accept:' : 'Wir akzeptieren:' }}</span>
                <span class="bg-stone-100 px-2 py-1 rounded">PayPal</span>
                <span class="bg-stone-100 px-2 py-1 rounded">{{ app()->getLocale() === 'en' ? 'Credit Card' : 'Kreditkarte' }}</span>
                <span class="bg-stone-100 px-2 py-1 rounded">{{ app()->getLocale() === 'en' ? 'Bank Transfer' : 'Überweisung' }}</span>
            </div>
        </div>
    </div>

    {{-- Ähnliche Artikel --}}
    @if($similar->count() > 0)
    <section class="mt-16 border-t border-stone-100 pt-10">
        <h2 class="font-serif text-xl text-stone-800 mb-6">
            {{ app()->getLocale() === 'en' ? 'Similar Items' : 'Ähnliche Artikel' }}
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($similar as $article)
            @include('shop.partials.article-card', ['article' => $article])
            @endforeach
        </div>
    </section>
    @endif

</div>

@push('scripts')
<script>
function imageGallery() {
    return {
        activeImage: '{{ $article->primaryImage()?->url ?? '' }}',
        lightboxOpen: false,
        setActive(url) { this.activeImage = url; },
        openLightbox() { this.lightboxOpen = true; },
    }
}
</script>
@endpush

@endsection
