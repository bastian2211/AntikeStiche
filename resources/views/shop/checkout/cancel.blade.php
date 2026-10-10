@extends('layouts.shop')

@section('title', 'Zahlung abgebrochen')

@section('content')
<div class="max-w-md mx-auto px-4 py-16 text-center">

    <div class="text-5xl mb-6">😕</div>

    <h1 class="text-2xl font-bold text-stone-800 mb-3">Zahlung abgebrochen</h1>
    <p class="text-stone-500 mb-8">
        Kein Problem – Ihre Bestellung wurde noch nicht abgeschlossen und dem Artikel ist nichts passiert.
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ url()->previous() }}"
           class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-900 font-bold rounded-xl transition-colors">
            Erneut versuchen
        </a>
        <a href="{{ route('artikel.index') }}"
           class="px-6 py-2.5 border border-stone-300 text-stone-700 hover:bg-stone-50 font-semibold rounded-xl transition-colors">
            Zurück zum Shop
        </a>
    </div>

</div>
@endsection
