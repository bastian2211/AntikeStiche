@extends('layouts.shop')

@section('title', config('shop.name', 'Antike Stiche'))
@section('subtitle', app()->getLocale() === 'en' ? 'Antique Prints & Historical Maps' : 'Antike Stiche & Historische Landkarten')

@section('content')

{{-- Hero --}}
<section class="relative bg-stone-900 text-white">
    <div class="absolute inset-0 bg-gradient-to-r from-stone-900 via-stone-900/90 to-transparent"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <div class="max-w-2xl">
            <p class="text-amber-400 text-sm font-semibold uppercase tracking-widest mb-3">
                {{ app()->getLocale() === 'en' ? 'Antique Mail-Order Bookshop' : 'Versandantiquariat' }}
            </p>
            <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                {{ app()->getLocale() === 'en'
                    ? 'Antique Prints & Historical Maps'
                    : 'Antike Stiche & Historische Landkarten' }}
            </h1>
            <p class="text-stone-300 text-lg mb-8 leading-relaxed">
                {{ app()->getLocale() === 'en'
                    ? 'Discover our collection of over 13,000 unique antique prints, historic maps and old graphics. Each piece is unique and ships worldwide.'
                    : 'Entdecken Sie unsere Sammlung von über 13.000 einzigartigen antiken Stichen, historischen Landkarten und alten Grafiken. Jedes Stück ist ein Unikat und wird weltweit versandt.' }}
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('artikel.index') }}"
                   class="inline-flex items-center px-6 py-3 bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold rounded-md transition-colors">
                    {{ app()->getLocale() === 'en' ? 'Browse All Items' : 'Alle Artikel ansehen' }}
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <form action="{{ route('artikel.index') }}" method="GET" class="flex">
                    <input type="text" name="q" placeholder="{{ app()->getLocale() === 'en' ? 'Search articles...' : 'Artikel suchen...' }}"
                           class="px-4 py-3 bg-white/10 border border-white/20 text-white placeholder-stone-400 rounded-l-md focus:outline-none focus:bg-white/20 w-56">
                    <button type="submit" class="px-4 py-3 bg-white/10 border border-l-0 border-white/20 text-white rounded-r-md hover:bg-white/20 transition-colors">
                        🔍
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- Kategorien --}}
@if($categories->count() > 0)
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="font-serif text-2xl text-stone-800 mb-6">
        {{ app()->getLocale() === 'en' ? 'Browse by Category' : 'Nach Kategorie stöbern' }}
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($categories as $category)
        <a href="{{ route('artikel.index', ['kategorie' => $category->slug]) }}"
           class="group flex items-center justify-between p-4 bg-white border border-stone-200 rounded-lg hover:border-amber-400 hover:shadow-md transition-all">
            <span class="font-medium text-stone-700 group-hover:text-stone-900">
                {{ $category->name_localized }}
            </span>
            <span class="text-xs text-stone-400 bg-stone-100 px-2 py-0.5 rounded-full">
                {{ $category->articles()->active()->count() }}
            </span>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- Neueste Artikel --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-serif text-2xl text-stone-800">
            {{ app()->getLocale() === 'en' ? 'Recently Added' : 'Neu eingestellt' }}
        </h2>
        <a href="{{ route('artikel.index') }}" class="text-sm text-amber-600 hover:text-amber-700 font-medium">
            {{ app()->getLocale() === 'en' ? 'View all →' : 'Alle ansehen →' }}
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($featuredArticles as $article)
        @include('shop.partials.article-card', ['article' => $article])
        @endforeach
    </div>
</section>

{{-- Trust Badges --}}
<section class="bg-white border-t border-stone-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="text-3xl mb-2">🌍</div>
                <div class="font-semibold text-stone-700 text-sm">
                    {{ app()->getLocale() === 'en' ? 'Worldwide Shipping' : 'Weltweiter Versand' }}
                </div>
                <div class="text-xs text-stone-500 mt-1">DHL, Deutsche Post</div>
            </div>
            <div>
                <div class="text-3xl mb-2">🔒</div>
                <div class="font-semibold text-stone-700 text-sm">
                    {{ app()->getLocale() === 'en' ? 'Secure Payment' : 'Sichere Zahlung' }}
                </div>
                <div class="text-xs text-stone-500 mt-1">PayPal · Kreditkarte · Überweisung</div>
            </div>
            <div>
                <div class="text-3xl mb-2">🎁</div>
                <div class="font-semibold text-stone-700 text-sm">
                    {{ app()->getLocale() === 'en' ? '13,000+ Unique Items' : '13.000+ Unikate' }}
                </div>
                <div class="text-xs text-stone-500 mt-1">
                    {{ app()->getLocale() === 'en' ? 'Each piece is one-of-a-kind' : 'Jeder Artikel einzigartig' }}
                </div>
            </div>
            <div>
                <div class="text-3xl mb-2">⭐</div>
                <div class="font-semibold text-stone-700 text-sm">
                    {{ app()->getLocale() === 'en' ? 'Trusted Seller' : 'Geprüfter Händler' }}
                </div>
                <div class="text-xs text-stone-500 mt-1">
                    {{ app()->getLocale() === 'en' ? 'Years of experience on eBay' : 'Langjährige Erfahrung bei eBay' }}
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
