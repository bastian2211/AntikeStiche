@extends('layouts.shop')

@section('title', $page->meta_title ?: $page->title_localized)

@section('meta_description', $page->meta_description)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    <h1 class="text-3xl font-bold text-stone-800 mb-8">{{ $page->title_localized }}</h1>

    <div class="prose prose-stone max-w-none
                prose-headings:font-bold prose-headings:text-stone-800
                prose-p:text-stone-600 prose-p:leading-relaxed
                prose-a:text-amber-600 prose-a:no-underline hover:prose-a:underline
                prose-ul:text-stone-600 prose-ol:text-stone-600
                prose-strong:text-stone-800">
        {!! $page->content_localized !!}
    </div>

</div>
@endsection
