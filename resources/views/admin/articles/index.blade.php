@extends('layouts.admin')

@section('page-title', 'Artikel')

@section('header-actions')
<a href="{{ route('admin.articles.create') }}"
   class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold text-sm rounded-lg transition-colors">
    + Neuer Artikel
</a>
@endsection

@section('content')

{{-- Filter --}}
<div class="bg-white rounded-xl border border-stone-200 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-stone-500 mb-1">Suche</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Titel, SKU..."
                   class="px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-400 w-48">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none">
                <option value="">Alle</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktiv</option>
                <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Verkauft</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserviert</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inaktiv</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1">Kategorie</label>
            <select name="category_id" class="px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none">
                <option value="">Alle Kategorien</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1">eBay-Sync</label>
            <select name="ebay_sync" class="px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none">
                <option value="">Alle</option>
                <option value="error" {{ request('ebay_sync') === 'error' ? 'selected' : '' }}>⚠️ Fehler</option>
                <option value="pending" {{ request('ebay_sync') === 'pending' ? 'selected' : '' }}>⏳ Ausstehend</option>
                <option value="synced" {{ request('ebay_sync') === 'synced' ? 'selected' : '' }}>✅ Synchron</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm rounded-lg hover:bg-stone-700 transition-colors">
            Filtern
        </button>
        @if(request()->hasAny(['q', 'status', 'category_id', 'ebay_sync']))
        <a href="{{ route('admin.articles.index') }}" class="text-sm text-stone-500 hover:text-stone-700 py-2">
            × Zurücksetzen
        </a>
        @endif
    </form>
</div>

{{-- Tabelle --}}
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Artikel</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">SKU</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider hidden lg:table-cell">Kategorie</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-stone-500 uppercase tracking-wider">Preis</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider hidden lg:table-cell">eBay</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($articles as $article)
            <tr class="hover:bg-stone-50 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        @if($article->primaryImage())
                        <img src="{{ $article->primaryImage()->thumbnail_url }}" alt=""
                             class="w-10 h-10 object-cover rounded-lg shrink-0">
                        @else
                        <div class="w-10 h-10 bg-stone-100 rounded-lg flex items-center justify-center text-stone-400 text-xs shrink-0">🖼️</div>
                        @endif
                        <p class="font-medium text-stone-800 line-clamp-1 max-w-xs">{{ $article->title }}</p>
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell font-mono text-xs text-stone-500">{{ $article->sku }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-stone-500 text-xs">{{ $article->category?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-semibold text-stone-800">{{ number_format($article->price, 2, ',', '.') }} €</td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ match($article->status) {
                            'active' => 'bg-green-100 text-green-700',
                            'sold' => 'bg-stone-100 text-stone-500',
                            'reserved' => 'bg-amber-100 text-amber-700',
                            default => 'bg-red-100 text-red-600'
                        } }}">
                        {{ ucfirst($article->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center hidden lg:table-cell">
                    @if($article->ebay_active)
                    <span class="text-xs {{ match($article->ebay_sync_status) {
                        'synced' => 'text-green-600',
                        'pending' => 'text-amber-600',
                        'error' => 'text-red-600',
                        default => 'text-stone-400'
                    } }}">
                        {{ $article->ebay_sync_status ?? '—' }}
                    </span>
                    @else
                    <span class="text-stone-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.articles.edit', $article) }}"
                       class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                        Bearbeiten
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-12 text-center text-stone-400">
                    Keine Artikel gefunden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($articles->hasPages())
    <div class="px-4 py-3 border-t border-stone-100">
        {{ $articles->links() }}
    </div>
    @endif
</div>

<p class="text-xs text-stone-400 mt-2">{{ $articles->total() }} Artikel gesamt</p>

@endsection
