@extends('layouts.admin')

@section('page-title', $category->exists ? 'Kategorie bearbeiten: ' . $category->name : 'Neue Kategorie')

@section('header-actions')
<a href="{{ route('admin.categories.index') }}" class="text-sm text-stone-500 hover:text-stone-700">← Zurück</a>
@endsection

@section('content')
<div class="max-w-xl">
    <form method="POST"
          action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="bg-white rounded-xl border border-stone-200 p-6 space-y-4">
        @csrf
        @if($category->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Name (Deutsch) *</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                   class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @enderror"
                   placeholder="z.B. Landkarten, Druckgrafiken">
            @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Name (Englisch)</label>
            <input type="text" name="name_en" value="{{ old('name_en', $category->name_en) }}"
                   class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400"
                   placeholder="z.B. Maps, Engravings">
        </div>

        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">URL-Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                   class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono text-sm"
                   placeholder="wird automatisch generiert">
            <p class="text-xs text-stone-400 mt-1">Leer lassen → wird automatisch aus dem Namen generiert</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Übergeordnete Kategorie</label>
            <select name="parent_id" class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                <option value="">— Keine (Hauptkategorie) —</option>
                @foreach($parentCategories as $parent)
                @if(!$category->exists || $parent->id !== $category->id)
                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                </option>
                @endif
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Sortierung</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                   class="w-24 px-3 py-2 border border-stone-300 rounded-lg focus:outline-none text-sm">
            <p class="text-xs text-stone-400 mt-1">Niedrigere Zahlen erscheinen zuerst</p>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-stone-700">Kategorie aktiv (im Shop sichtbar)</span>
            </label>
        </div>

        <div class="pt-4 flex items-center gap-4">
            <button type="submit"
                    class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-900 font-bold rounded-lg transition-colors">
                💾 Speichern
            </button>

            @if($category->exists)
            <button type="button" onclick="document.getElementById('delete-form').submit()"
                    class="text-sm text-red-600 hover:text-red-800">
                Löschen
            </button>
            @endif
        </div>
    </form>

    @if($category->exists)
    <form id="delete-form" method="POST" action="{{ route('admin.categories.destroy', $category) }}"
          onsubmit="return confirm('Kategorie wirklich löschen? Artikel bleiben erhalten, werden aber keiner Kategorie mehr zugeordnet.')">
        @csrf @method('DELETE')
    </form>
    @endif
</div>

@push('scripts')
<script>
// Slug automatisch aus Name generieren, wenn Slug-Feld leer
document.querySelector('input[name="name"]').addEventListener('input', function() {
    const slugField = document.querySelector('input[name="slug"]');
    if (slugField.value === '' || slugField.dataset.manual !== '1') {
        slugField.value = this.value
            .toLowerCase()
            .replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});
document.querySelector('input[name="slug"]').addEventListener('input', function() {
    this.dataset.manual = '1';
});
</script>
@endpush
@endsection
