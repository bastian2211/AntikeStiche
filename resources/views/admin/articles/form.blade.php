@extends('layouts.admin')

@section('page-title', $article->exists ? 'Artikel bearbeiten' : 'Neuer Artikel')

@section('header-actions')
@if($article->exists)
<a href="{{ route('artikel.show', $article->id) }}" target="_blank"
   class="text-sm text-stone-500 hover:text-stone-700">Im Shop ansehen ↗</a>
@endif
<a href="{{ route('admin.articles.index') }}" class="text-sm text-stone-500 hover:text-stone-700">← Zurück</a>
@endsection

@section('content')
<form method="POST"
      action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if($article->exists)
    @method('PUT')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Linke Spalte: Hauptdaten --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Titel --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
                <h2 class="font-semibold text-stone-700">Titel & Beschreibung</h2>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Titel (Deutsch) *</label>
                    <input type="text" name="title" value="{{ old('title', $article->title) }}" required
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Titel (Englisch)</label>
                    <input type="text" name="title_en" value="{{ old('title_en', $article->title_en) }}"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Beschreibung (Deutsch) *</label>
                    <textarea name="description" rows="8" required
                              class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('description') border-red-400 @enderror">{{ old('description', $article->description) }}</textarea>
                    @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Beschreibung (Englisch)</label>
                    <textarea name="description_en" rows="6"
                              class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">{{ old('description_en', $article->description_en) }}</textarea>
                </div>
            </div>

            {{-- Bilder --}}
            @if($article->exists)
            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-700 mb-4">Bilder</h2>

                {{-- Vorhandene Bilder (sortierbar) --}}
                <div id="image-grid" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-4">
                    @foreach($article->images as $image)
                    <div class="relative group cursor-grab" data-image-id="{{ $image->id }}">
                        <img src="{{ $image->thumbnail_url }}" alt="{{ $image->alt_text }}"
                             class="w-full aspect-square object-cover rounded-lg border-2 {{ $image->is_primary ? 'border-amber-400' : 'border-stone-200' }}">
                        @if($image->is_primary)
                        <span class="absolute top-1 left-1 bg-amber-400 text-stone-900 text-xs font-bold px-1.5 py-0.5 rounded">Haupt</span>
                        @endif
                        <button type="button"
                                onclick="deleteImage({{ $article->id }}, {{ $image->id }}, this)"
                                class="absolute top-1 right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            ×
                        </button>
                    </div>
                    @endforeach
                </div>

                {{-- Upload --}}
                <div class="border-2 border-dashed border-stone-300 rounded-lg p-6 text-center">
                    <input type="file" id="image-upload" accept="image/*" multiple class="hidden"
                           onchange="uploadImages({{ $article->id }}, this)">
                    <label for="image-upload" class="cursor-pointer">
                        <div class="text-3xl mb-2">📸</div>
                        <p class="text-sm font-medium text-stone-700">Bilder hochladen</p>
                        <p class="text-xs text-stone-400 mt-1">Mehrere Bilder auswählen möglich · Max. 10 MB pro Bild</p>
                    </label>
                </div>
                <div id="upload-progress" class="mt-3 space-y-1 hidden"></div>
            </div>
            @else
            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-700 mb-2">Bilder</h2>
                <p class="text-sm text-stone-500 mb-3">Nach dem Speichern können Bilder hochgeladen werden.</p>
                <input type="file" name="images[]" accept="image/*" multiple
                       class="text-sm text-stone-600">
            </div>
            @endif

        </div>

        {{-- Rechte Spalte: Meta --}}
        <div class="space-y-5">

            {{-- Preis & Versand --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
                <h2 class="font-semibold text-stone-700">Preis & Versand</h2>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Preis (€) *</label>
                    <input type="number" name="price" step="0.01" min="0"
                           value="{{ old('price', $article->price) }}" required
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Versand Deutschland (€)</label>
                    <input type="number" name="shipping_cost_de" step="0.01" min="0"
                           value="{{ old('shipping_cost_de', $article->shipping_cost_de ?? 3.80) }}"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Versand EU (€)</label>
                    <input type="number" name="shipping_cost_eu" step="0.01" min="0"
                           value="{{ old('shipping_cost_eu', $article->shipping_cost_eu ?? 6.50) }}"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Versand Welt (€)</label>
                    <input type="number" name="shipping_cost_world" step="0.01" min="0"
                           value="{{ old('shipping_cost_world', $article->shipping_cost_world ?? 12.00) }}"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>

            {{-- Kategorie & Status --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
                <h2 class="font-semibold text-stone-700">Details</h2>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Kategorie</label>
                    <select name="category_id"
                            class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="">— Keine Kategorie —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $article->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->parent ? $cat->parent->name . ' › ' : '' }}{{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="active" {{ old('status', $article->status ?? 'active') === 'active' ? 'selected' : '' }}>✅ Aktiv / Verfügbar</option>
                        <option value="sold" {{ old('status', $article->status) === 'sold' ? 'selected' : '' }}>💰 Verkauft</option>
                        <option value="reserved" {{ old('status', $article->status) === 'reserved' ? 'selected' : '' }}>⏳ Reserviert</option>
                        <option value="inactive" {{ old('status', $article->status) === 'inactive' ? 'selected' : '' }}>🚫 Inaktiv</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Zustand</label>
                    <input type="text" name="condition"
                           value="{{ old('condition', $article->condition ?? 'Gebraucht') }}"
                           placeholder="z.B. Gebraucht, Gut, Sehr gut"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>

            {{-- eBay --}}
            <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-3">
                <h2 class="font-semibold text-stone-700">eBay</h2>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">eBay Artikel-ID</label>
                    <input type="text" name="ebay_item_id"
                           value="{{ old('ebay_item_id', $article->ebay_item_id) }}"
                           placeholder="z.B. 123456789012"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono text-sm">
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="ebay_active" value="1"
                           {{ old('ebay_active', $article->ebay_active) ? 'checked' : '' }}
                           class="text-amber-500 rounded">
                    <span class="text-sm text-stone-700">Bei eBay aktiv gelistet</span>
                </label>

                @if($article->exists && $article->ebay_sync_status)
                <div class="text-xs text-stone-500">
                    Sync-Status:
                    <span class="font-medium
                        {{ match($article->ebay_sync_status) {
                            'synced' => 'text-green-600',
                            'pending' => 'text-amber-600',
                            'error' => 'text-red-600',
                            default => 'text-stone-500'
                        } }}">
                        {{ $article->ebay_sync_status }}
                    </span>
                    @if($article->ebay_last_sync_at)
                    · {{ $article->ebay_last_sync_at->diffForHumans() }}
                    @endif
                </div>
                @endif
            </div>

            {{-- SKU --}}
            @if($article->exists)
            <div class="bg-stone-50 rounded-xl border border-stone-200 p-4">
                <p class="text-xs text-stone-500 font-medium">Artikelnummer</p>
                <p class="font-mono text-stone-800 font-semibold mt-1">{{ $article->sku }}</p>
                <p class="text-xs text-stone-400 mt-1">{{ $article->view_count }} Aufrufe</p>
            </div>
            @endif

            {{-- Speichern --}}
            <button type="submit"
                    class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-stone-900 font-bold rounded-xl transition-colors shadow-sm">
                💾 {{ $article->exists ? 'Änderungen speichern' : 'Artikel erstellen' }}
            </button>

            @if($article->exists)
            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}"
                  onsubmit="return confirm('Artikel wirklich löschen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-2 text-red-600 hover:text-red-800 text-sm transition-colors">
                    Artikel löschen
                </button>
            </form>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
@if($article->exists)
<script>
const articleId = {{ $article->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function uploadImages(articleId, input) {
    const progress = document.getElementById('upload-progress');
    progress.classList.remove('hidden');

    for (const file of input.files) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', csrfToken);

        const bar = document.createElement('div');
        bar.className = 'text-xs text-stone-500 flex items-center gap-2';
        bar.innerHTML = `<span class="animate-spin">⏳</span> ${file.name}`;
        progress.appendChild(bar);

        try {
            const res = await fetch(`/admin/articles/${articleId}/images`, {
                method: 'POST',
                body: formData,
            });
            const data = await res.json();
            if (data.success) {
                bar.innerHTML = `✅ ${file.name}`;
                // Bild-Grid aktualisieren
                const grid = document.getElementById('image-grid');
                const div = document.createElement('div');
                div.className = 'relative group cursor-grab';
                div.dataset.imageId = data.image_id;
                div.innerHTML = `
                    <img src="${data.url}" class="w-full aspect-square object-cover rounded-lg border-2 border-stone-200">
                    <button type="button" onclick="deleteImage(${articleId}, ${data.image_id}, this)"
                            class="absolute top-1 right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">×</button>
                `;
                grid.appendChild(div);
            }
        } catch (e) {
            bar.innerHTML = `❌ ${file.name} – Fehler`;
        }
    }
    input.value = '';
}

async function deleteImage(articleId, imageId, btn) {
    if (!confirm('Bild wirklich löschen?')) return;
    const res = await fetch(`/admin/articles/${articleId}/images/${imageId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });
    if ((await res.json()).success) {
        btn.closest('[data-image-id]').remove();
    }
}
</script>
@endif
@endpush
