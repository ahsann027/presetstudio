@extends('layouts.app')

@section('title', 'Pesanan - Admin')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Pesanan Masuk</h1>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        <div class="bg-white border rounded-xl p-4"><p class="text-sm text-slate-500">Pendapatan (lunas)</p><p class="text-xl font-bold">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p></div>
        <div class="bg-white border rounded-xl p-4"><p class="text-sm text-slate-500">Lunas</p><p class="text-xl font-bold text-emerald-700">{{ $stats['paid'] }}</p></div>
        <div class="bg-white border rounded-xl p-4"><p class="text-sm text-slate-500">Menunggu</p><p class="text-xl font-bold text-amber-600">{{ $stats['pending'] }}</p></div>
        <div class="bg-white border rounded-xl p-4"><p class="text-sm text-slate-500">Gagal / kedaluwarsa</p><p class="text-xl font-bold text-red-600">{{ $stats['failed'] }}</p></div>
    </div>

    {{-- Filter status + pencarian --}}
    @php
        $tabs = ['' => 'Semua', 'paid' => 'Lunas', 'pending' => 'Menunggu', 'failed' => 'Gagal'];
        $badge = ['paid' => 'bg-emerald-100 text-emerald-800', 'pending' => 'bg-amber-100 text-amber-800', 'failed' => 'bg-red-100 text-red-800'];
        $label = ['paid' => 'Lunas', 'pending' => 'Menunggu', 'failed' => 'Gagal'];
    @endphp

    <div class="flex gap-2 overflow-x-auto pb-2 mb-3">
        @foreach ($tabs as $value => $text)
            <a href="{{ route('admin.orders.index', array_filter(['status' => $value, 'q' => $keyword])) }}"
               class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-medium border {{ $status === $value ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-700 border-slate-300' }}">{{ $text }}</a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4 flex gap-2">
        <input type="hidden" name="status" value="{{ $status }}">
        <label for="q" class="sr-only">Cari pesanan</label>
        <input id="q" type="search" name="q" value="{{ $keyword }}" placeholder="Cari nomor pesanan, nama, atau email"
               class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <button class="px-4 rounded-lg border border-slate-300 bg-white hover:bg-slate-100">Cari</button>
    </form>

    {{-- Tabel (bisa digeser ke samping di ponsel) --}}
    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm min-w-[56rem]">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="p-3 font-medium">No. pesanan</th>
                    <th class="p-3 font-medium">Tanggal</th>
                    <th class="p-3 font-medium">Pembeli</th>
                    <th class="p-3 font-medium">Preset</th>
                    <th class="p-3 font-medium text-right">Total</th>
                    <th class="p-3 font-medium">Metode</th>
                    <th class="p-3 font-medium">Status</th>
                    <th class="p-3 font-medium text-center">Unduhan</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($orders as $order)
                    <tr>
                        <td class="p-3 font-mono text-xs">{{ $order->order_number }}</td>
                        <td class="p-3 whitespace-nowrap">{{ $order->created_at->format('d M Y, H:i') }}</td>
                        <td class="p-3"><p class="font-medium">{{ $order->buyer_name }}</p><p class="text-slate-500">{{ $order->buyer_email }}</p></td>
                        <td class="p-3">{{ $order->product_name }}</td>
                        <td class="p-3 text-right whitespace-nowrap">{{ $order->formatted_amount }}</td>
                        <td class="p-3">{{ $order->payment_method ?: '-' }}</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $badge[$order->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $label[$order->status] ?? $order->status }}</span></td>
                        <td class="p-3 text-center whitespace-nowrap">{{ $order->isPaid() ? $order->download_count . '/' . config('shop.download_limit') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-8 text-center text-slate-500">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
