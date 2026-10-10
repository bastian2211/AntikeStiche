<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['category', 'images']);

        if ($search = $request->get('q')) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->get('category_id')) {
            $query->where('category_id', $category);
        }

        if ($ebaySync = $request->get('ebay_sync')) {
            $query->where('ebay_sync_status', $ebaySync);
        }

        $articles   = $query->latest()->paginate(50)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $article    = new Article();
        return view('admin.articles.form', compact('categories', 'article'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateArticle($request);

        $article = Article::create([
            ...$validated,
            'sku' => Article::generateSku(),
        ]);

        $this->handleImageUploads($request, $article);

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Artikel erfolgreich erstellt.');
    }

    public function edit(Article $article)
    {
        $categories = Category::active()->orderBy('name')->get();
        $article->load('images');
        return view('admin.articles.form', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $this->validateArticle($request, $article);
        $article->update($validated);
        $this->handleImageUploads($request, $article);

        // eBay-Sync vormerken wenn aktiv bei eBay
        if ($article->ebay_active && $article->ebay_item_id) {
            $article->update(['ebay_sync_status' => 'pending']);
        }

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Artikel erfolgreich aktualisiert.');
    }

    public function destroy(Article $article)
    {
        // Bilder löschen
        foreach ($article->images as $image) {
            Storage::delete('public/articles/' . $image->filename);
            Storage::delete('public/articles/thumbs/' . $image->filename);
        }
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel gelöscht.');
    }

    /**
     * Bild hochladen (AJAX)
     */
    public function uploadImage(Request $request, Article $article)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $file     = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Original speichern (max 1600px)
        $img = Image::make($file)->orientate();
        if ($img->width() > 1600) {
            $img->resize(1600, null, fn($c) => $c->aspectRatio());
        }
        Storage::put('public/articles/' . $filename, $img->encode());

        // Thumbnail (400px)
        $thumb = Image::make($file)->orientate();
        $thumb->fit(400, 400);
        Storage::put('public/articles/thumbs/' . $filename, $thumb->encode());

        $isPrimary = $article->images()->count() === 0;

        $image = ArticleImage::create([
            'article_id' => $article->id,
            'filename'   => $filename,
            'alt_text'   => $article->title,
            'sort_order' => $article->images()->max('sort_order') + 1,
            'is_primary' => $isPrimary,
        ]);

        if ($isPrimary) {
            $article->update(['primary_image' => $filename]);
        }

        return response()->json([
            'success'  => true,
            'image_id' => $image->id,
            'url'      => $image->url,
        ]);
    }

    /**
     * Bild-Reihenfolge speichern (AJAX)
     */
    public function reorderImages(Request $request, Article $article)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $imageId) {
            ArticleImage::where('id', $imageId)
                ->where('article_id', $article->id)
                ->update([
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
        }

        // Primärbild aktualisieren
        $primary = $article->images()->orderBy('sort_order')->first();
        if ($primary) {
            $article->update(['primary_image' => $primary->filename]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Bild löschen (AJAX)
     */
    public function deleteImage(Request $request, Article $article, ArticleImage $image)
    {
        Storage::delete('public/articles/' . $image->filename);
        Storage::delete('public/articles/thumbs/' . $image->filename);
        $image->delete();

        // Neues Primärbild setzen
        $newPrimary = $article->images()->orderBy('sort_order')->first();
        $article->update([
            'primary_image' => $newPrimary?->filename,
        ]);
        if ($newPrimary) {
            $newPrimary->update(['is_primary' => true]);
        }

        return response()->json(['success' => true]);
    }

    private function validateArticle(Request $request, ?Article $article = null): array
    {
        return $request->validate([
            'title'              => 'required|string|max:255',
            'title_en'           => 'nullable|string|max:255',
            'description'        => 'required|string',
            'description_en'     => 'nullable|string',
            'category_id'        => 'nullable|exists:categories,id',
            'price'              => 'required|numeric|min:0',
            'shipping_cost_de'   => 'nullable|numeric|min:0',
            'shipping_cost_eu'   => 'nullable|numeric|min:0',
            'shipping_cost_world' => 'nullable|numeric|min:0',
            'condition'          => 'nullable|string|max:100',
            'status'             => 'required|in:active,sold,reserved,inactive',
            'ebay_active'        => 'boolean',
            'ebay_item_id'       => 'nullable|string|max:100',
        ]);
    }

    private function handleImageUploads(Request $request, Article $article): void
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $uploadRequest = new Request();
                $uploadRequest->files->set('image', $file);
                $this->uploadImage($uploadRequest, $article);
            }
        }
    }
}
