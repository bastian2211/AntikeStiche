<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children', 'parent')
            ->topLevel()
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents  = Category::topLevel()->orderBy('name')->get();
        $category = new Category();
        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function store(Request $request)
    {
        $validated           = $this->validateCategory($request);
        $validated['slug']   = Str::slug($validated['name']);
        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategorie erstellt.');
    }

    public function edit(Category $category)
    {
        $parents = Category::topLevel()->where('id', '!=', $category->id)->orderBy('name')->get();
        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $this->validateCategory($request);
        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategorie aktualisiert.');
    }

    public function destroy(Category $category)
    {
        // Artikel auf null setzen statt löschen
        $category->articles()->update(['category_id' => null]);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategorie gelöscht.');
    }

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:255',
            'name_en'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);
    }
}
