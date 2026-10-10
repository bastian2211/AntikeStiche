@extends('layouts.shop')

@section('title', $currentCategory ? $currentCategory->name_localized : (app()->getLocale() === 'en' ? 'All Items' : 'Alle Artikel'))
@section('meta_description', $currentCategory
    ? ($currentCategory->description ?? 'Antike ' . $currentCategory->name . ' - historische Grafiken und Landkarten')
    : 'Alle antiken Stiche, historischen Landkarten und alten Grafiken im Überblick.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-stone-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-stone-700">
            {{ app()->getLocale() === 'en' ? 'Home' : 'Startseite' }}
        </a>
        <span>/</span>
        @if($currentCategory)
        <a href="{{ route('artikel.index') }}" class="hover:text-stone-700">
            {{ app()->getLocale() === 'en' ? 'All Items' : 'Alle Artikel' }}
        </a>
        <span>/</span>
        <span class="text-stone-800">{{ $currentCategory->name_localized }}</span>
        @else
        <span class="text-stone-800">{{ app()->getLocale() === 'en' ? 'All Items' : 'Alle Artikel' }}</span>
        @endif
    </nav>

    <div class="flex gap-8">

        {{-- Sidebar --}}
        <aside class="hidden lg:block w-56 shrink-0">

            {{-- Kategorien --}}
            <div class="bg-white border border-stone-200 rounded-lg p-4 mb-4">
                <h2 class="font-semibold text-stone-700 text-sm uppercase tracking-wider mb-3">
                    {{ app()->getLocale() === 'en' ? 'Categories' : 'Kategorien' }}
                </h2>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('artikel.index', array_merge(request()->except('kategorie', 'page'), [])) }}"
                           class="flex items-center justify-between text-sm py-1 {{ !$currentCategory ? 'font-semibold text-amber-700' : 'text-stone-600 hover:text-stone-900' }}">
                            <span>{{ app()->getLocale() === 'en' ? 'All Items' : 'Alle Artikel' }}</span>
                        </a>
                    </li>
                    @foreach($categories as $cat)
                    <li>
                        <a href="{{ route('artikel.index', array_merge(request()->except('kategorie', 'page'), ['kategorie' => $cat->slug])) }}"
                           class="flex items-center justify-between text-sm py-1 {{ $currentCategory?->id === $cat->id ? 'font-semibold text-amber-700' : 'text-stone-600 hover:text-stone-900' }}">
                            <span>{{ $cat->name_localized }}</span>
                            <span class="text-xs text-stone-400">{{ $cat->articles()->active()->count() }}</span>
                        </a>
                        @if($cat->children->count() > 0 && $currentCategory?->id === $cat->id)
                        <ul class="ml-3 mt-1 space-y-1 border-l border-stone-200 pl-3">
                            @foreach($cat->children as $child)
                            <li>
                                <a href="{{ route('artikel.index', ['kategorie' => $child->slug]) }}"
                                   class="text-xs text-stone-500 hover:text-stone-800 block py-0.5">
                                    {{ $child->name_localized }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Preis-Filter --}}
            <div class="bg-white border border-stone-200 rounded-lg p-4">
                <h2 class="font-semibold text-stone-700 text-sm uppercase tracking-wider mb-3">
                    {{ app()->getLocale() === 'en' ? 'Price Range' : 'Preisspanne' }}
                </h2>
                <form method="GET" action="{{ route('artikel.index') }}" class="space-y-2">
                    @foreach(request()->except(['preis_min', 'preis_max', 'page']) as $key => $val)
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <div class="flex gap-2">
                        <input type="number" name="preis_min" placeholder="Min" value="{{ request('preis_min') }}"
                               class="w-full px-2 py-1.5 text-sm border border-stone-300 rounded focus:outline-none focus:ring-1 focus:ring-stone-400">
                        <input type="number" name="preis_max" placeholder="Max" value="{{ request('preis_max') }}"
                               class="w-full px-2 py-1.5 text-sm border border-stone-300 rounded focus:outline-none focus:ring-1 focus:ring-stone-400">
                    </div>
                    <button type="submit" class="w-full px-3 py-1.5 bg-stone-800 text-white text-sm rounded hover:bg-stone-700 transition-colors">
                        {{ app()->getLocale() === 'en' ? 'Apply' : 'Anwenden' }}
                    </button>
                </form>
            </div>

        </aside>

        {{-- Main Content --}}
        <div class="flex-1 min-w-0">

            {{-- Toolbar --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="font-serif text-xl text-stone-800">
                        {{ $currentCategory ? $currentCategory->name_localized : (app()->getLocale() === 'en' ? 'All Items' : 'Alle Artikel') }}
                    </h1>
                    <p class="text-sm text-stone-500 mt-0.5">
                        {{ $articles->total() }} {{ app()->getLocale() === 'en' ? 'items found' : 'Artikel gefunden' }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Mobilsuche --}}
                    <form action="{{ route('artikel.index') }}" method="GET" class="sm:hidden flex">
                        <input type="text" name="q" placeholder="{{ app()->getLocale() === 'en' ? 'Search...' : 'Suchen...' }}"
                               value="{{ $search }}"
                               class="w-36 px-2 py-1.5 text-sm border border-stone-300 rounded-l focus:outline-none">
                        <button class="px-2 py-1.5 bg-stone-800 text-white text-sm rounded-r">🔍</button>
                    </form>

                    {{-- Sortierung --}}
                    <form method="GET" action="{{ route('artikel.index') }}">
                        @foreach(request()->except(['sort', 'page']) as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <select name="sort" onchange="this.form.submit()"
                                class="text-sm border border-stone-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-stone-400">
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'en' ? 'Newest first' : 'Neueste zuerst' }}
                            </option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'en' ? 'Price: low to high' : 'Preis: aufsteigend' }}
                            </option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'en' ? 'Price: high to low' : 'Preis: absteigend' }}
                            </option>
                            <option value="title_asc" {{ $sort === 'title_asc' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'en' ? 'Name A–Z' : 'Name A–Z' }}
                            </option>
                        </select>
                    </form>
                </div>
            </div>

            {{-- Aktive Filter anzeigen --}}
            @if($search || request('preis_min') || request('preis_max'))
            <div class="flex flex-wrap gap-2 mb-4">
                @if($search)
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-100 text-amber-800 text-sm rounded">
                    🔍 „{{ $search }}"
                    <a href="{{ route('artikel.index', request()->except(['q', 'page'])) }}" class="hover:text-amber-900">×</a>
                </span>
                @endif
                @if(request('preis_min') || request('preis_max'))
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-100 text-amber-800 text-sm rounded">
                    💶 {{ request('preis_min', '0') }}–{{ request('preis_max', '∞') }} €
                    <a href="{{ route('artikel.index', request()->except(['preis_min', 'preis_max', 'page'])) }}" class="hover:text-amber-900">×</a>
                </span>
                @endif
            </div>
            @endif

            {{-- Artikel-Grid --}}
            @if($articles->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($articles as $article)
                @include('shop.partials.article-card', ['article' => $article])
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $articles->links() }}
            </div>

            @else
            <div class="text-center py-20 text-stone-400">
                <div class="text-5xl mb-4">🔍</div>
                <p class="text-lg font-medium text-stone-600">
                    {{ app()->getLocale() === 'en' ? 'No items found.' : 'Keine Artikel gefunden.' }}
                </p>
                <p class="text-sm mt-2">
                    {{ app()->getLocale() === 'en' ? 'Try different search terms or remove filters.' : 'Bitte andere Suchbegriffe probieren oder Filter entfernen.' }}
                </p>
                <a href="{{ route('artikel.index') }}" class="mt-4 inline-block text-amber-600 hover:text-amber-700 text-sm font-medium">
                    {{ app()->getLocale() === 'en' ? '← All items' : '← Alle Artikel' }}
                </a>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
