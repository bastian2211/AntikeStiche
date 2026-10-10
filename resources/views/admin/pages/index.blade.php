@extends('layouts.admin')

@section('page-title', 'Seiten')

@section('header-actions')
<a href="{{ route('admin.pages.create') }}"
   class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold text-sm rounded-lg transition-colors">
    + Neue Seite
</a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Titel</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">URL</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider">Footer</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">Reihenfolge</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($pages as $page)
            <tr class="hover:bg-stone-50 transition-colors">
                <td class="px-4 py-3">
                    <p class="font-medium text-stone-800">{{ $page->title }}</p>
                    @if($page->title_en)
                    <p class="text-xs text-stone-400">EN: {{ $page->title_en }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <code class="text-xs text-stone-500 bg-stone-100 px-2 py-0.5 rounded">/seite/{{ $page->slug }}</code>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $page->is_active ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-500' }}">
                        {{ $page->is_active ? 'Aktiv' : 'Inaktiv' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-stone-500">
                    {{ $page->show_in_footer ? '✅' : '—' }}
                </td>
                <td class="px-4 py-3 text-center text-stone-500 hidden md:table-cell">
                    {{ $page->sort_order }}
                </td>
                <td class="px-4 py-3 text-right flex items-center justify-end gap-3">
                    <a href="{{ route('page.show', $page->slug) }}" target="_blank"
                       class="text-xs text-stone-400 hover:text-stone-600">↗ Ansehen</a>
                    <a href="{{ route('admin.pages.edit', $page) }}"
                       class="text-amber-600 hover:text-amber-700 text-sm font-medium">Bearbeiten</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-stone-400">
                    Noch keine Seiten vorhanden.
                    <a href="{{ route('admin.pages.create') }}" class="text-amber-600 hover:underline ml-1">Erste Seite erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-xs text-stone-400 mt-3">
    💡 Typische Seiten: Impressum, Datenschutzerklärung, AGB, Über uns, Kontakt
</p>
@endsection
