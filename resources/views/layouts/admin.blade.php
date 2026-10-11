<!DOCTYPE html>
<html lang="de" class="h-full bg-stone-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | {{ config('shop.name', 'Antike Stiche') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('styles')
</head>
<body class="flex h-full">

    {{-- SIDEBAR --}}
    <aside class="w-56 bg-stone-900 text-stone-300 flex flex-col shrink-0 fixed h-full z-40">
        {{-- Logo --}}
        <div class="px-5 py-4 border-b border-stone-700">
            <a href="{{ route('admin.dashboard') }}" class="font-serif text-lg text-white font-bold">
                {{ config('shop.name', 'Antike Stiche') }}
            </a>
            <p class="text-xs text-stone-500 mt-0.5">Administration</p>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-stone-700 text-white' : 'hover:bg-stone-800' }}">
                📊 Dashboard
            </a>
            <a href="{{ route('admin.articles.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.articles.*') ? 'bg-stone-700 text-white' : 'hover:bg-stone-800' }}">
                🖼️ Artikel
            </a>
            <a href="{{ route('admin.categories.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-stone-700 text-white' : 'hover:bg-stone-800' }}">
                📁 Kategorien
            </a>
            <a href="{{ route('admin.orders.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-stone-700 text-white' : 'hover:bg-stone-800' }}">
                📦 Bestellungen
                @php $pending = \App\Models\Order::where('status', 'pending')->count(); @endphp
                @if($pending > 0)
                <span class="ml-auto bg-amber-500 text-stone-900 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $pending }}</span>
                @endif
            </a>
            <a href="{{ route('admin.pages.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.pages.*') ? 'bg-stone-700 text-white' : 'hover:bg-stone-800' }}">
                📄 Seiten
            </a>
            <div class="border-t border-stone-700 my-2"></div>
            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-stone-400 hover:bg-stone-800 hover:text-stone-300 transition-colors">
                🌐 Shop ansehen ↗
            </a>
        </nav>

        {{-- User --}}
        <div class="px-3 py-3 border-t border-stone-700">
            <div class="flex items-center justify-between">
                <div class="text-xs">
                    <p class="text-white font-medium">{{ Auth::guard('admin')->user()?->name }}</p>
                    <p class="text-stone-500">Admin</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-400 hover:text-white text-xs transition-colors">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="ml-56 flex-1 flex flex-col min-h-full">

        {{-- Header --}}
        <header class="bg-white border-b border-stone-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
            <h1 class="font-semibold text-stone-800">@yield('page-title', 'Dashboard')</h1>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="bg-green-50 border-b border-green-200 px-6 py-3 text-sm text-green-800 flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border-b border-red-200 px-6 py-3 text-sm text-red-800 flex items-center gap-2">
            ❌ {{ session('error') }}
        </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
