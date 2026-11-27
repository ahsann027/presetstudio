@extends('layouts.app')

@section('title', 'Checkout - ' . $product->name)

@section('content')
    <a href="{{ route('catalog.show', $product->id) }}" class="text-sm text-emerald-700 hover:underline">&larr; Kembali ke detail</a>
    <h1 class="text-2xl font-bold mt-3 mb-5">Checkout</h1>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Data pembeli --}}
        <form method="POST" action="{{ route('checkout.store', $product->id) }}" class="bg-white border rounded-xl p-5 space-y-4">
            @csrf
            <h2 class="font-bold text-lg">Data pembeli</h2>

            <div>
                <label for="buyer_name" class="block text-sm font-medium mb-1">Nama lengkap</label>
                <input id="buyer_name" name="buyer_name" value="{{ old('buyer_name') }}" required maxlength="100" autocomplete="name"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('buyer_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="buyer_email" class="block text-sm font-medium mb-1">Email</label>
                <input id="buyer_email" type="email" name="buyer_email" value="{{ old('buyer_email') }}" required maxlength="150" autocomplete="email"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-slate-500 mt-1">Link unduhan juga dikirim ke email ini.</p>
                @error('buyer_email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg">
                Lanjut ke pembayaran
            </button>

            <p class="text-center text-sm text-slate-500">
                Lebih nyaman lewat chat?
                <a href="{{ \App\Support\Whatsapp::link($product->whatsappOrderMessage()) }}" target="_blank" rel="noopener"
                   class="text-emerald-700 font-semibold underline">Pesan via WhatsApp</a>
            </p>
        </form>

        {{-- Ringkasan pesanan --}}
        <aside class="bg-white border rounded-xl p-5 h-fit">
            <h2 class="font-bold text-lg mb-3">Ringkasan pesanan</h2>
            <div class="flex gap-3">
                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-24 h-20 rounded-lg object-cover bg-slate-200">
                <div>
                    <p class="font-semibold">{{ $product->name }}</p>
                    <p class="text-sm text-slate-500">{{ $product->category }} · {{ $product->preset_count }} preset</p>
                </div>
            </div>
            <div class="border-t mt-4 pt-4 flex justify-between items-center">
                <span class="text-slate-600">Total</span>
                <span class="text-2xl font-bold">{{ $product->formatted_price }}</span>
            </div>
        </aside>
    </div>
@endsection
