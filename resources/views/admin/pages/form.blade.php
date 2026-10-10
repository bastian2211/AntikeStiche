@extends('layouts.admin')

@section('page-title', $page->exists ? 'Seite bearbeiten: ' . $page->title : 'Neue Seite')

@section('header-actions')
@if($page->exists)
<a href="{{ route('page.show', $page->slug) }}" target="_blank" class="text-sm text-stone-500 hover:text-stone-700">Im Shop ansehen ↗</a>
@endif
<a href="{{ route('admin.pages.index') }}" class="text-sm text-stone-500 hover:text-stone-700">← Zurück</a>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<form method="POST"
      action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}"
      id="page-form">
    @csrf
    @if($page->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Editor --}}
        <div class="lg:col-span-2 space-y-5">

            <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
                <h2 class="font-semibold text-stone-700">Inhalt (Deutsch)</h2>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Titel *</label>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" required
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Inhalt *</label>
                    <div id="quill-de" style="min-height: 300px;">{{ old('content', $page->content) }}</div>
                    <input type="hidden" name="content" id="content-input">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
                <h2 class="font-semibold text-stone-700">Inhalt (Englisch) <span class="font-normal text-stone-400 text-sm">– optional</span></h2>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Titel (EN)</label>
                    <input type="text" name="title_en" value="{{ old('title_en', $page->title_en) }}"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Inhalt (EN)</label>
                    <div id="quill-en" style="min-height: 200px;">{{ old('content_en', $page->content_en) }}</div>
                    <input type="hidden" name="content_en" id="content-en-input">
                </div>
            </div>
        </div>

        {{-- Einstellungen --}}
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
                <h2 class="font-semibold text-stone-700">Einstellungen</h2>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">URL-Slug *</label>
                    <div class="flex items-center">
                        <span class="px-3 py-2 bg-stone-100 border border-r-0 border-stone-300 rounded-l-lg text-sm text-stone-500">/seite/</span>
                        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required
                               class="flex-1 px-3 py-2 border border-stone-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm">
                    </div>
                    <p class="text-xs text-stone-400 mt-1">Nur Kleinbuchstaben, Bindestriche erlaubt</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Meta-Titel</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                           placeholder="Für Suchmaschinen (optional)"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Meta-Beschreibung</label>
                    <textarea name="meta_description" rows="3"
                              placeholder="Kurzbeschreibung für Google (max. 160 Zeichen)"
                              class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none text-sm">{{ old('meta_description', $page->meta_description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Reihenfolge</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}"
                           class="w-24 px-3 py-2 border border-stone-300 rounded-lg focus:outline-none text-sm">
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
                        <span class="text-sm text-stone-700">Seite aktiv (öffentlich sichtbar)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="show_in_footer" value="0">
                        <input type="checkbox" name="show_in_footer" value="1" {{ old('show_in_footer', $page->show_in_footer) ? 'checked' : '' }}>
                        <span class="text-sm text-stone-700">Im Footer anzeigen</span>
                    </label>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-stone-900 font-bold rounded-xl transition-colors">
                💾 Seite speichern
            </button>

            @if($page->exists)
            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}"
                  onsubmit="return confirm('Seite wirklich löschen?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full py-2 text-red-600 text-sm hover:text-red-800">Seite löschen</button>
            </form>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const toolbarOptions = [
    [{ 'header': [1, 2, 3, false] }],
    ['bold', 'italic', 'underline'],
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    ['link'],
    ['clean']
];

const quillDe = new Quill('#quill-de', { theme: 'snow', modules: { toolbar: toolbarOptions } });
const quillEn = new Quill('#quill-en', { theme: 'snow', modules: { toolbar: toolbarOptions } });

// Vorhandenen Inhalt laden
@if($page->exists && $page->content)
quillDe.root.innerHTML = {!! json_encode($page->content) !!};
@endif
@if($page->exists && $page->content_en)
quillEn.root.innerHTML = {!! json_encode($page->content_en) !!};
@endif

// Beim Submit HTML in hidden fields schreiben
document.getElementById('page-form').addEventListener('submit', function() {
    document.getElementById('content-input').value = quillDe.root.innerHTML;
    document.getElementById('content-en-input').value = quillEn.root.innerHTML;
});
</script>
@endpush
