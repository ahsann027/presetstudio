@extends('layouts.app')

@section('title', config('shop.name') . ' - Katalog Preset Foto')

@section('content')
    <section class="mb-6">
        <h1 class="text-2xl font-bold">{{ config('shop.name') }}</h1>
        <p class="text-slate-600">{{ config('shop.tagline') }}</p>
    </section>

    {{-- Form tetap berfungsi tanpa JavaScript (GET). Dengan JS, hasil dimuat real-time. --}}
    <form id="filter-form" action="{{ route('catalog.index') }}" method="GET" data-category="{{ $category }}" class="mb-4">
        <input type="hidden" name="category" value="{{ $category }}">
        <label for="search-input" class="sr-only">Cari preset</label>
        <input id="search-input" type="search" name="q" value="{{ $keyword }}" autocomplete="off"
               placeholder="Cari preset, misalnya: moody"
               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </form>

    {{-- Chip gaya preset --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-4">
        @php
            $chipBase = 'js-category whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-medium border';
            $on  = 'bg-emerald-600 text-white border-emerald-600';
            $off = 'bg-white text-slate-700 border-slate-300';
        @endphp

        <a href="{{ route('catalog.index', array_filter(['q' => $keyword])) }}" data-category=""
           class="{{ $chipBase }} {{ $category === '' ? $on : $off }}">Semua</a>

        @foreach ($categories as $cat)
            <a href="{{ route('catalog.index', array_filter(['category' => $cat, 'q' => $keyword])) }}" data-category="{{ $cat }}"
               class="{{ $chipBase }} {{ $category === $cat ? $on : $off }}">{{ $cat }}</a>
        @endforeach
    </div>

    <p id="result-count" class="text-sm text-slate-500 mb-3" aria-live="polite">{{ $products->count() }} preset</p>

    <div id="product-grid" class="grid grid-cols-1 min-[480px]:grid-cols-2 md:grid-cols-3 gap-4 transition-opacity">
        @include('catalog._grid')
    </div>

    {{-- Testimoni --}}
    @if ($testimonials->isNotEmpty())
        <section class="mt-12">
            <h2 class="font-bold text-lg mb-3">Kata pembeli</h2>
            <div class="grid md:grid-cols-3 gap-4">
                @foreach ($testimonials as $t)
                    @include('catalog._testimonial', ['t' => $t])
                @endforeach
            </div>
        </section>
    @endif
@endsection
