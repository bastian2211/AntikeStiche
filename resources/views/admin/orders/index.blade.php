@extends('layouts.admin')

@section('page-title', 'Bestellungen')

@section('content')

{{-- Filter --}}
<div class="bg-white rounded-xl border border-stone-200 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-stone-500 mb-1">Suche</label>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Bestellnr., Name, E-Mail..."
                   class="px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-400 w-48">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none">
                <option value="">Alle</option>
                @foreach(['pending' => 'Ausstehend', 'confirmed' => 'Bestätigt', 'processing' => 'In Bearbeitung', 'shipped' => 'Versendet', 'delivered' => 'Zugestellt', 'cancelled' => 'Storniert'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1">Zahlung</label>
            <select name="payment" class="px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none">
                <option value="">Alle</option>
                <option value="pending" {{ request('payment') === 'pending' ? 'selected' : '' }}>Ausstehend</option>
                <option value="paid" {{ request('payment') === 'paid' ? 'selected' : '' }}>Bezahlt</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm rounded-lg hover:bg-stone-700 transition-colors">
            Filtern
        </button>
        @if(request()->hasAny(['q', 'status', 'payment']))
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-stone-500 hover:text-stone-700 py-2">
            × Zurücksetzen
        </a>
        @endif
    </form>
</div>

{{-- Tabelle --}}
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Bestellung</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">Käufer</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider hidden lg:table-cell">Land</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-stone-500 uppercase tracking-wider">Gesamt</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-stone-500 uppercase tracking-wider hidden lg:table-cell">Zahlung</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">Datum</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($orders as $order)
            <tr class="hover:bg-stone-50 transition-colors">
                <td class="px-4 py-3">
                    <div>
                        <p class="font-mono font-medium text-stone-800 text-xs">{{ $order->order_number }}</p>
                        @if($order->source === 'ebay')
                        <span class="text-xs text-amber-600 font-medium">eBay</span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <p class="font-medium text-stone-700">{{ $order->buyer_name }}</p>
                    <p class="text-xs text-stone-400">{{ $order->buyer_email }}</p>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-stone-600">
                    {{ $order->shipping_country }}
                    @php $hints = \App\Models\Order::getComplianceHints($order->shipping_country); @endphp
                    @if(count($hints) > 0)
                    <span class="text-amber-500 ml-1" title="Versandhinweise vorhanden">⚠️</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right font-semibold text-stone-800">
                    {{ number_format($order->total, 2, ',', '.') }} €
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ match($order->status) {
                            'pending' => 'bg-amber-100 text-amber-700',
                            'confirmed', 'processing' => 'bg-blue-100 text-blue-700',
                            'shipped' => 'bg-purple-100 text-purple-700',
                            'delivered' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-600',
                            default => 'bg-stone-100 text-stone-500'
                        } }}">
                        {{ match($order->status) {
                            'pending' => 'Ausstehend',
                            'confirmed' => 'Bestätigt',
                            'processing' => 'In Bearbeitung',
                            'shipped' => 'Versendet',
                            'delivered' => 'Zugestellt',
                            'cancelled' => 'Storniert',
                            default => ucfirst($order->status)
                        } }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center hidden lg:table-cell">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-500' }}">
                        {{ $order->payment_status === 'paid' ? 'Bezahlt' : 'Ausstehend' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-xs text-stone-400 hidden md:table-cell">
                    {{ $order->created_at->format('d.m.Y') }}<br>
                    {{ $order->created_at->format('H:i') }}
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.orders.show', $order) }}"
                       class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                        Öffnen
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-stone-400">
                    Keine Bestellungen gefunden.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($orders->hasPages())
    <div class="px-4 py-3 border-t border-stone-100">
        {{ $orders->links() }}
    </div>
    @endif
</div>

<p class="text-xs text-stone-400 mt-2">{{ $orders->total() }} Bestellungen gesamt</p>

@endsection
