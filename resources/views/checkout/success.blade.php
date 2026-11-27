@extends('layouts.app')

@section('title', 'Pesanan ' . $order->order_number)

@push('scripts')
    @if ($order->status === 'pending')
        {{-- Menunggu notifikasi gateway: cek ulang status tiap 5 detik --}}
        <script>setTimeout(() => window.location.reload(), 5000);</script>
    @endif
@endpush

@section('content')
    @if ($order->isPaid())
        <div class="text-center mb-6">
            <div class="mx-auto w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl">✓</div>
            <h1 class="text-2xl font-bold mt-3">Pembayaran berhasil</h1>
            <p class="text-slate-600">Terima kasih, {{ $order->buyer_name }}! Link unduhan juga dikirim ke {{ $order->buyer_email }}.</p>
        </div>

        <section class="bg-white border rounded-xl p-5 mb-5">
            <h2 class="font-bold text-lg mb-3">Ringkasan pembelian</h2>
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-slate-500">Nomor pesanan</dt><dd class="font-semibold text-right">{{ $order->order_number }}</dd>
                <dt class="text-slate-500">Preset</dt><dd class="font-semibold text-right">{{ $order->product_name }}</dd>
                <dt class="text-slate-500">Total dibayar</dt><dd class="font-semibold text-right">{{ $order->formatted_amount }}</dd>
                <dt class="text-slate-500">Tanggal bayar</dt><dd class="font-semibold text-right">{{ $order->paid_at->format('d M Y, H:i') }}</dd>
            </dl>
        </section>

        <section class="bg-white border rounded-xl p-5 mb-5 text-center">
            @if ($order->isDownloadActive())
                <a href="{{ $order->downloadUrl() }}"
                   class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-lg">
                    Unduh preset
                </a>
                <p class="text-sm text-slate-500 mt-2">Link berlaku sampai {{ $order->download_expires_at->format('d M Y, H:i') }}.</p>
            @else
                <p class="text-red-600 font-medium">Link unduhan sudah kedaluwarsa.</p>
                <p class="text-sm text-slate-500">Hubungi penjual di +{{ config('shop.whatsapp') }} dengan menyebut nomor pesanan Anda.</p>
                <a href="{{ \App\Support\Whatsapp::link($order->whatsappConfirmMessage()) }}" target="_blank" rel="noopener"
                   class="inline-block mt-3 border border-emerald-600 text-emerald-700 font-semibold px-4 py-2 rounded-lg text-sm">Hubungi via WhatsApp</a>
            @endif
        </section>

        <section class="bg-white border rounded-xl p-5">
            <h2 class="font-bold text-lg mb-3">Panduan pemasangan</h2>
            @if ($order->product && $order->product->install_guide)
                <p class="text-slate-700 leading-relaxed">{!! nl2br(e($order->product->install_guide)) !!}</p>
            @else
                @include('catalog._install')
            @endif
        </section>

    @elseif ($order->status === 'failed')
        <div class="text-center py-10">
            <h1 class="text-2xl font-bold text-red-600">Pembayaran gagal atau kedaluwarsa</h1>
            <p class="text-slate-600 mt-2">Pesanan {{ $order->order_number }} tidak dapat dilanjutkan.</p>
            <a href="{{ route('catalog.index') }}" class="inline-block mt-5 bg-emerald-600 text-white font-semibold px-6 py-3 rounded-lg">Kembali ke katalog</a>
        </div>

    @else
        <div class="text-center py-10">
            <h1 class="text-2xl font-bold">Menunggu konfirmasi pembayaran…</h1>
            <p class="text-slate-600 mt-2">Halaman ini akan diperbarui otomatis setelah pembayaran terkonfirmasi.</p>
            <a href="{{ route('checkout.pay', $order->access_token) }}" class="inline-block mt-5 border border-slate-300 font-semibold px-6 py-3 rounded-lg">Kembali ke pembayaran</a>
        </div>
    @endif
@endsection
