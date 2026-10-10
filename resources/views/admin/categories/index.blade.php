@extends('layouts.admin')

@section('page-title', 'Kategorien')

@section('header-actions')
<a href="{{ route('admin.categories.create') }}"
   class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold text-sm rounded-lg transition-colors">
    + Neue Kategorie
</a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">Übergeordnet</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">URL-Slug</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider hidden lg:table-cell">Sortierung</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($categories as $cat)
            <tr class="hover:bg-stone-50 transition-colors {{ $cat->parent_id ? 'bg-stone-50/50' : '' }}">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        @if($cat->parent_id)
                        <span class="text-stone-300 text-sm">↳</span>
                        @endif
                        <div>
                            <p class="font-medium text-stone-800 {{ $cat->parent_id ? '' : 'font-semibold' }}">{{ $cat->name }}</p>
                            @if($cat->name_en)
                            <p class="text-xs text-stone-400">EN: {{ $cat->name_en }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-stone-500 text-sm">
                    {{ $cat->parent?->name ?? '—' }}
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <code class="text-xs text-stone-500 bg-stone-100 px-2 py-0.5 rounded">{{ $cat->slug }}</code>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $cat->is_active ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-500' }}">
                        {{ $cat->is_active ? 'Aktiv' : 'Inaktiv' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-stone-500 hidden lg:table-cell">
                    {{ $cat->sort_order }}
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.categories.edit', $cat) }}"
                       class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                        Bearbeiten
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-stone-400">
                    Noch keine Kategorien vorhanden.
                    <a href="{{ route('admin.categories.create') }}" class="text-amber-600 hover:underline ml-1">Erste Kategorie erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-xs text-stone-400 mt-3">
    {{ $categories->count() }} Kategorien · Unterkategorien werden eingerückt dargestellt
</p>
@endsection
