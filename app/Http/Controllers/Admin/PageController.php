<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('sort_order')->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $page = new Page();
        return view('admin.pages.form', compact('page'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePage($request);
        Page::create($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Seite erstellt.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $this->validatePage($request, $page);
        $page->update($validated);

        return redirect()->route('admin.pages.edit', $page)
            ->with('success', 'Seite gespeichert.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Seite gelöscht.');
    }

    private function validatePage(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'slug'             => 'required|string|max:100|unique:pages,slug' . ($page ? ",{$page->id}" : ''),
            'title'            => 'required|string|max:255',
            'title_en'         => 'nullable|string|max:255',
            'content'          => 'required|string',
            'content_en'       => 'nullable|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'is_active'        => 'boolean',
            'show_in_footer'   => 'boolean',
            'sort_order'       => 'integer',
        ]);
    }
}
