<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredArticles = Article::active()
            ->with(['images'])
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::active()
            ->topLevel()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return view('shop.home', compact('featuredArticles', 'categories'));
    }
}
