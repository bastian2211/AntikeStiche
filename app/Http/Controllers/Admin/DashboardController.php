<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'articles_active'  => Article::where('status', 'active')->count(),
            'articles_sold'    => Article::where('status', 'sold')->count(),
            'articles_total'   => Article::count(),
            'orders_pending'   => Order::where('status', 'pending')->count(),
            'orders_today'     => Order::whereDate('created_at', today())->count(),
            'revenue_month'    => Order::where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
            'ebay_pending_sync' => Article::where('ebay_sync_status', 'pending')->count(),
            'ebay_errors'       => Article::where('ebay_sync_status', 'error')->count(),
        ];

        $recentOrders = Order::with('items')
            ->latest()
            ->take(10)
            ->get();

        $recentArticles = Article::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentArticles'));
    }
}
