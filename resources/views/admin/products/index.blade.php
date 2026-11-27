@extends('layouts.app')

@section('title', 'Kelola Preset - Admin')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-2xl font-bold">Kelola Preset</h1>
        <a href="{{ route('admin.products.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-lg">+ Tambah preset</a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-4 flex gap-2">
        <label for="q" class="sr-only">Cari preset</label>
        <input id="q" type="search" name="q" value="{{ $keyword }}" placeholder="Cari nama, deskripsi, atau gaya"
               class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <button class="px-4 rounded-lg border border-slate-300 bg-white hover:bg-slate-100">Cari</button>
    </form>

    <ul class="bg-white border rounded-xl divide-y">
        @forelse ($products as $product)
            <li class="p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <img src="{{ $product->image }}" alt="" class="w-20 h-14 rounded-lg object-cover bg-slate-200 shrink-0">
                    <div class="min-w-0">
                        <p class="font-semibold truncate">{{ $product->name }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $product->category }} · {{ $product->formatted_price }} · {{ $product->orders_count }} pesanan
                        </p>
                        <p class="mt-1 flex flex-wrap gap-1 text-xs">
                            <span class="px-2 py-0.5 rounded {{ $product->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            @if ($product->is_new)<span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800">Baru</span>@endif
                            @if ($product->is_featured)<span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800">Terlaris</span>@endif
                        </p>
                    </div>
                </div>

                <div class="flex gap-2 sm:shrink-0">
                    <a href="{{ route('admin.products.edit', $product) }}" class="flex-1 sm:flex-none text-center border border-slate-300 rounded-lg px-4 py-2 text-sm hover:bg-slate-100">Edit</a>
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="flex-1 sm:flex-none"
                          onsubmit="return confirm('Hapus preset &quot;{{ addslashes($product->name) }}&quot;? Tindakan ini tidak bisa dibatalkan.')">
                        @csrf @method('DELETE')
                        <button class="w-full border border-red-300 text-red-700 rounded-lg px-4 py-2 text-sm hover:bg-red-50">Hapus</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="p-8 text-center text-slate-500">Belum ada preset. Klik "Tambah preset" untuk memulai.</li>
        @endforelse
    </ul>

    <div class="mt-4">{{ $products->links() }}</div>
@endsection
