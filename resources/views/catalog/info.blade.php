@extends('layouts.app')

@section('title', 'Cara Beli & Cara Pasang Preset - ' . config('shop.name'))

@section('content')
    <h1 class="text-2xl font-bold mb-6">Cara Beli & Cara Pasang Preset</h1>

    <section class="bg-white border rounded-xl p-5 mb-5">
        <h2 class="font-bold text-lg mb-3">Langkah pembelian</h2>
        <ol class="list-decimal pl-5 space-y-2">
            <li>Pilih preset di <a href="{{ route('catalog.index') }}" class="text-emerald-700 underline">katalog</a>, lihat contoh sebelum-sesudahnya, lalu tekan <strong>Beli sekarang</strong>.</li>
            <li>Isi nama dan email, lalu periksa ringkasan pesanan dan total harga.</li>
            <li>Pilih metode pembayaran (QRIS, virtual account, atau dompet digital) dan ikuti instruksinya.</li>
            <li>Setelah pembayaran terkonfirmasi, halaman pembayaran berhasil menampilkan nomor pesanan, link unduhan, dan panduan pemasangan.</li>
            <li>Link unduhan juga dikirim ke email Anda dan berlaku <strong>{{ config('shop.download_days') }} hari</strong>.</li>
        </ol>
    </section>

    <section class="bg-white border rounded-xl p-5 mb-5">
        <h2 class="font-bold text-lg mb-3">Metode pembayaran</h2>
        <div class="space-y-4">
            @foreach (config('shop.payment_methods') as $method)
                <div>
                    <h3 class="font-semibold">{{ $method['label'] }}</h3>
                    <ol class="list-decimal pl-5 text-sm space-y-0.5 mt-1">
                        @foreach ($method['steps'] as $step)<li>{{ $step }}</li>@endforeach
                    </ol>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-white border rounded-xl p-5 mb-5">
        <h2 class="font-bold text-lg mb-3">Cara memasang preset</h2>
        @include('catalog._install')
    </section>

    <section class="bg-white border rounded-xl p-5">
        <h2 class="font-bold text-lg mb-3">Kontak penjual</h2>
        <p>Email: <strong>{{ config('shop.seller_email') }}</strong></p>
        <p>WhatsApp: <strong>+{{ config('shop.whatsapp') }}</strong></p>
        <p class="mb-4">Jam balas: <strong>{{ config('shop.hours') }}</strong></p>
        <a href="https://wa.me/{{ config('shop.whatsapp') }}" target="_blank" rel="noopener"
           class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-lg">
            Hubungi penjual
        </a>
    </section>
@endsection
