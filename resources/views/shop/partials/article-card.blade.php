<article class="group bg-white border border-stone-200 rounded-lg overflow-hidden hover:shadow-lg hover:border-stone-300 transition-all">
    <a href="{{ route('artikel.show', $article->id) }}" class="block">

        {{-- Bild --}}
        <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
            @if($article->primaryImage())
            <img src="{{ $article->primaryImage()->thumbnail_url }}"
                 alt="{{ $article->primaryImage()->alt_text ?? $article->title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy">
            @else
            <div class="w-full h-full flex items-center justify-center text-stone-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            @endif

            {{-- Status Badge --}}
            @if($article->status === 'sold')
            <div class="absolute inset-0 bg-stone-900/50 flex items-center justify-center">
                <span class="bg-stone-900 text-white text-sm font-semibold px-3 py-1 rounded">
                    {{ app()->getLocale() === 'en' ? 'SOLD' : 'VERKAUFT' }}
                </span>
            </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="p-3">
            @if($article->category)
            <p class="text-xs text-stone-400 mb-1">{{ $article->category->name_localized }}</p>
            @endif
            <h3 class="text-sm font-medium text-stone-800 line-clamp-2 leading-snug group-hover:text-amber-700 transition-colors">
                {{ $article->title_localized }}
            </h3>
            <p class="text-base font-semibold text-stone-900 mt-2">
                {{ number_format($article->price, 2, ',', '.') }} €
            </p>
        </div>
    </a>
</article>
