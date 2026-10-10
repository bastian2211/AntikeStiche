<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        if ($search = $request->get('q')) {
            $query->where(fn($q) => $q
                ->where('order_number', 'like', "%{$search}%")
                ->orWhere('buyer_name', 'like', "%{$search}%")
                ->orWhere('buyer_email', 'like', "%{$search}%")
            );
        }

        $orders = $query->latest()->paginate(30)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.article');

        // Compliance-Hinweise für dieses Land
        $complianceHints = Order::getComplianceHints($order->shipping_country);

        return view('admin.orders.show', compact('order', 'complianceHints'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status'            => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tracking_number'   => 'nullable|string|max:255',
            'shipping_provider' => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Bestellung aktualisiert.');
    }
}
