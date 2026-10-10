<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::active()->with(['images', 'category']);

        // Volltextsuche
        if ($search = $request->get('q')) {
            $query->search($search);
        }

        // Kategorie-Filter
        if ($categorySlug = $request->get('kategorie')) {
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            // Auch Unterkategorien einschließen
            $categoryIds = Category::where('parent_id', $category->id)
                ->pluck('id')
                ->prepend($category->id);
            $query->whereIn('category_id', $categoryIds);
        }

        // Preis-Filter
        if ($priceMin = $request->get('preis_min')) {
            $query->where('price', '>=', (float) $priceMin);
        }
        if ($priceMax = $request->get('preis_max')) {
            $query->where('price', '<=', (float) $priceMax);
        }

        // Sortierung
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'title_asc'  => $query->orderBy('title', 'asc'),
            default      => $query->latest(),
        };

        $articles   = $query->paginate(24)->withQueryString();
        $categories = Category::active()->topLevel()->with('children')->orderBy('sort_order')->get();

        $currentCategory = $categorySlug
            ? Category::where('slug', $categorySlug)->first()
            : null;

        return view('shop.articles.index', compact('articles', 'categories', 'currentCategory', 'search', 'sort'));
    }

    public function show(string $id)
    {
        $article = Article::with(['images', 'category'])->findOrFail($id);

        // Viewcount erhöhen
        $article->increment('view_count');

        // Ähnliche Artikel (gleiche Kategorie)
        $similar = Article::active()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->with('images')
            ->take(4)
            ->get();

        // Schema.org Structured Data
        $structuredData = $this->buildStructuredData($article);

        return view('shop.articles.show', compact('article', 'similar', 'structuredData'));
    }

    private function buildStructuredData(Article $article): array
    {
        $images = $article->images->map(fn($img) => $img->url)->toArray();

        return [
            '@context'    => 'https://schema.org/',
            '@type'       => 'Product',
            'name'        => $article->title,
            'description' => strip_tags($article->description),
            'sku'         => $article->sku,
            'image'       => $images,
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => route('artikel.show', $article->id),
                'priceCurrency' => 'EUR',
                'price'         => $article->price,
                'availability'  => $article->isAvailable()
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'condition'     => 'https://schema.org/UsedCondition',
                'seller'        => [
                    '@type' => 'Organization',
                    'name'  => config('shop.name', 'Antike Stiche'),
                ],
            ],
        ];
    }
}
