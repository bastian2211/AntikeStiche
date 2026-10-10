<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', config('shop.name', 'Antike Stiche')) – @yield('subtitle', 'Historische Grafiken & Landkarten')</title>
    <meta name="description" content="@yield('meta_description', 'Antike Stiche, historische Landkarten und alte Grafiken. Versandantiquariat mit über 13.000 Artikeln.')">

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('title', config('shop.name', 'Antike Stiche'))">
    <meta property="og:description" content="@yield('meta_description', 'Antike Stiche, historische Landkarten und alte Grafiken.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif

    {{-- Schema.org Structured Data --}}
    @hasSection('structured_data')
    <script type="application/ld+json">@yield('structured_data')</script>
    @endif

    {{-- Fonts & Styles --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body class="flex flex-col min-h-full bg-stone-50 text-stone-800">

    {{-- HEADER --}}
    <header class="bg-white border-b border-stone-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="text-2xl font-serif font-bold text-stone-800 tracking-tight">
                        {{ config('shop.name', 'Antike Stiche') }}
                    </span>
                </a>

                {{-- Navigation --}}
                <nav class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="text-sm text-stone-600 hover:text-stone-900 transition-colors">
                        {{ app()->getLocale() === 'en' ? 'Home' : 'Startseite' }}
                    </a>
                    <a href="{{ route('artikel.index') }}" class="text-sm text-stone-600 hover:text-stone-900 transition-colors">
                        {{ app()->getLocale() === 'en' ? 'All Items' : 'Alle Artikel' }}
                    </a>
                    @foreach(\App\Models\Category::active()->topLevel()->orderBy('sort_order')->take(4)->get() as $cat)
                    <a href="{{ route('artikel.index', ['kategorie' => $cat->slug]) }}"
                       class="text-sm text-stone-600 hover:text-stone-900 transition-colors">
                        {{ $cat->name_localized }}
                    </a>
                    @endforeach
                </nav>

                {{-- Suche + Sprache --}}
                <div class="flex items-center gap-3">
                    {{-- Sprachumschalter --}}
                    <div class="flex items-center text-xs gap-1">
                        <a href="{{ route('locale.switch', 'de') }}"
                           class="px-2 py-1 rounded {{ app()->getLocale() === 'de' ? 'bg-stone-800 text-white' : 'text-stone-500 hover:text-stone-800' }}">
                            DE
                        </a>
                        <a href="{{ route('locale.switch', 'en') }}"
                           class="px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-stone-800 text-white' : 'text-stone-500 hover:text-stone-800' }}">
                            EN
                        </a>
                    </div>

                    {{-- Suchfeld --}}
                    <form action="{{ route('artikel.index') }}" method="GET" class="hidden sm:flex">
                        <input type="text" name="q" placeholder="{{ app()->getLocale() === 'en' ? 'Search...' : 'Suchen...' }}"
                               value="{{ request('q') }}"
                               class="w-48 px-3 py-1.5 text-sm border border-stone-300 rounded-l-md focus:outline-none focus:ring-1 focus:ring-stone-500">
                        <button type="submit" class="px-3 py-1.5 bg-stone-800 text-white text-sm rounded-r-md hover:bg-stone-700 transition-colors">
                            🔍
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-green-50 border-b border-green-200 px-4 py-3 text-sm text-green-800 text-center">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border-b border-red-200 px-4 py-3 text-sm text-red-800 text-center">
        {{ session('error') }}
    </div>
    @endif
    @if(session('info'))
    <div class="bg-blue-50 border-b border-blue-200 px-4 py-3 text-sm text-blue-800 text-center">
        {{ session('info') }}
    </div>
    @endif

    {{-- MAIN CONTENT --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-stone-800 text-stone-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                {{-- Shop Info --}}
                <div>
                    <h3 class="font-serif text-white text-lg mb-3">{{ config('shop.name', 'Antike Stiche') }}</h3>
                    <p class="text-sm leading-relaxed">
                        {{ app()->getLocale() === 'en'
                            ? 'Specialist for antique prints, historical maps and old graphics. Shipping worldwide.'
                            : 'Ihr Spezialist für antike Stiche, historische Landkarten und alte Grafiken. Versand weltweit.' }}
                    </p>
                </div>

                {{-- Kategorien --}}
                <div>
                    <h3 class="font-semibold text-white text-sm uppercase tracking-wider mb-3">
                        {{ app()->getLocale() === 'en' ? 'Categories' : 'Kategorien' }}
                    </h3>
                    <ul class="space-y-2 text-sm">
                        @foreach(\App\Models\Category::active()->topLevel()->orderBy('sort_order')->take(6)->get() as $cat)
                        <li>
                            <a href="{{ route('artikel.index', ['kategorie' => $cat->slug]) }}"
                               class="hover:text-white transition-colors">
                                {{ $cat->name_localized }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Links --}}
                <div>
                    <h3 class="font-semibold text-white text-sm uppercase tracking-wider mb-3">
                        {{ app()->getLocale() === 'en' ? 'Information' : 'Informationen' }}
                    </h3>
                    <ul class="space-y-2 text-sm">
                        @foreach(\App\Models\Page::getFooterPages() as $page)
                        <li>
                            <a href="{{ route('page.show', $page->slug) }}" class="hover:text-white transition-colors">
                                {{ $page->title_localized }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="border-t border-stone-700 mt-8 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500">
                <span>© {{ date('Y') }} {{ config('shop.name', 'Antike Stiche') }}. Alle Rechte vorbehalten.</span>
                <span class="mt-2 sm:mt-0">
                    {{ app()->getLocale() === 'en' ? 'Secure payment with' : 'Sichere Zahlung mit' }}
                    PayPal · Kreditkarte · Überweisung
                </span>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
