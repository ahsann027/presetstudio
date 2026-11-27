@extends('layouts.app')

@section('title', $product->name . ' - ' . config('shop.name'))
@section('description', \Illuminate\Support\Str::limit($product->description, 150))

@section('content')
    @php $url = route('catalog.show', $product->id); @endphp

    <a href="{{ route('catalog.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Kembali ke katalog</a>

    <div class="mt-4 grid md:grid-cols-2 gap-6">
        {{-- Galeri sebelum-sesudah (geser untuk membandingkan) --}}
        <div class="space-y-4">
            <img src="{{ $product->image }}" alt="Contoh hasil {{ $product->name }}" width="800" height="600"
                 class="w-full rounded-xl border bg-slate-200 object-cover">
            @foreach ($product->images as $image)
                @include('catalog._before_after', ['image' => $image, 'name' => $product->name])
            @endforeach
        </div>

        {{-- Info utama --}}
        <div>
            <div class="flex gap-1 mb-1">
                @if ($product->is_new)<span class="bg-sky-500 text-white text-xs font-bold px-2 py-1 rounded">Baru</span>@endif
                @if ($product->is_featured)<span class="bg-amber-400 text-amber-950 text-xs font-bold px-2 py-1 rounded">Terlaris</span>@endif
            </div>
            <span class="text-sm text-emerald-700 font-medium">{{ $product->category }}</span>
            <h1 class="text-2xl font-bold mt-1">{{ $product->name }}</h1>
            <p class="text-3xl font-bold mt-3">{{ $product->formatted_price }}</p>

            <a href="{{ route('checkout.create', $product->id) }}"
               class="mt-5 block text-center w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg">
                Beli sekarang
            </a>

            <a href="{{ \App\Support\Whatsapp::link($product->whatsappOrderMessage()) }}" target="_blank" rel="noopener"
               class="mt-2 block text-center w-full border border-emerald-600 text-emerald-700 hover:bg-emerald-50 font-semibold py-3 rounded-lg">
                Pesan via WhatsApp
            </a>

            {{-- Tombol bagikan --}}
            <div class="mt-3 flex gap-2 text-sm">
                <button type="button" class="js-copy flex-1 border border-slate-300 rounded-lg py-2 hover:bg-slate-100"
                        data-url="{{ $url }}">Salin link</button>
                <a href="https://wa.me/?text={{ urlencode($product->name . ' - ' . $url) }}" target="_blank" rel="noopener"
                   class="flex-1 text-center border border-slate-300 rounded-lg py-2 hover:bg-slate-100">Bagikan ke WhatsApp</a>
            </div>

            <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                <div class="bg-white border rounded-lg p-3"><dt class="text-slate-500">Jumlah preset</dt><dd class="font-semibold">{{ $product->preset_count }} preset</dd></div>
                <div class="bg-white border rounded-lg p-3"><dt class="text-slate-500">Aplikasi</dt><dd class="font-semibold">{{ $product->supported_apps ?: '-' }}</dd></div>
                <div class="bg-white border rounded-lg p-3"><dt class="text-slate-500">Format file</dt><dd class="font-semibold">{{ $product->file_format ?: '-' }}</dd></div>
                <div class="bg-white border rounded-lg p-3"><dt class="text-slate-500">Ukuran file</dt><dd class="font-semibold">{{ $product->file_size ?: '-' }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- Deskripsi --}}
    <section class="mt-8">
        <h2 class="font-bold text-lg mb-2">Deskripsi</h2>
        <p class="text-slate-700 leading-relaxed">{!! nl2br(e($product->description ?: 'Belum ada deskripsi.')) !!}</p>
    </section>

    {{-- Panduan pemasangan --}}
    <section class="mt-8 bg-white border rounded-xl p-5">
        <h2 class="font-bold text-lg mb-3">Panduan pemasangan</h2>
        @if ($product->install_guide)
            <p class="text-slate-700 leading-relaxed">{!! nl2br(e($product->install_guide)) !!}</p>
        @else
            @include('catalog._install')
        @endif
    </section>

    {{-- Testimoni --}}
    @if ($testimonials->isNotEmpty())
        <section class="mt-8">
            <h2 class="font-bold text-lg mb-3">Kata pembeli</h2>
            <div class="grid md:grid-cols-3 gap-4">
                @foreach ($testimonials as $t)
                    @include('catalog._testimonial', ['t' => $t])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Preset terkait --}}
    @if ($related->isNotEmpty())
        <section class="mt-10">
            <h2 class="font-bold text-lg mb-3">Preset lain bergaya {{ $product->category }}</h2>
            <div class="grid grid-cols-1 min-[480px]:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ($related as $item)
                    @include('catalog._card', ['product' => $item])
                @endforeach
            </div>
        </section>
    @endif
@endsection
