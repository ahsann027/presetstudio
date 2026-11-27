<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('shop.name'))</title>
    <meta name="description" content="@yield('description', config('shop.tagline'))">

    {{-- Tailwind via CDN: cukup untuk tugas kuliah. Untuk produksi, pakai Vite + Tailwind build. --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    @php $isAdmin = auth()->check() && request()->routeIs('admin.*'); @endphp

    @if ($isAdmin)
        {{-- ===== Header ADMIN ===== --}}
        <header class="bg-slate-900 text-white sticky top-0 z-10">
            <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between gap-3">
                <span class="font-bold">Admin</span>
                <nav class="flex items-center gap-4 text-sm font-medium overflow-x-auto">
                    <a href="{{ route('admin.products.index') }}" class="whitespace-nowrap hover:text-emerald-300 {{ request()->routeIs('admin.products.*') ? 'text-emerald-300' : '' }}">Preset</a>
                    <a href="{{ route('admin.orders.index') }}" class="whitespace-nowrap hover:text-emerald-300 {{ request()->routeIs('admin.orders.*') ? 'text-emerald-300' : '' }}">Pesanan</a>
                    <a href="{{ route('catalog.index') }}" target="_blank" class="whitespace-nowrap hover:text-emerald-300">Lihat toko ↗</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="whitespace-nowrap text-slate-300 hover:text-white">Keluar</button>
                    </form>
                </nav>
            </div>
        </header>
    @else
        {{-- ===== Header PUBLIK ===== --}}
        <header class="bg-white border-b sticky top-0 z-10">
            <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
                <a href="{{ route('catalog.index') }}" class="font-bold text-emerald-700 text-lg">{{ config('shop.name') }}</a>
                <nav class="flex gap-4 text-sm font-medium">
                    <a href="{{ route('catalog.index') }}" class="hover:text-emerald-700">Katalog</a>
                    <a href="{{ route('catalog.info') }}" class="hover:text-emerald-700">Cara Beli</a>
                </nav>
            </div>
        </header>
    @endif

    <main class="{{ $isAdmin ? 'max-w-6xl' : 'max-w-5xl' }} mx-auto px-4 py-6">
        {{-- Pesan sukses / gagal --}}
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3" role="alert">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    @unless ($isAdmin)
        <footer class="border-t bg-white mt-10">
            <div class="max-w-5xl mx-auto px-4 py-6 text-sm text-slate-500 text-center">
                &copy; {{ date('Y') }} {{ config('shop.name') }}. Pembayaran aman diproses payment gateway.
            </div>
        </footer>

        {{-- Bar & modal bandingkan produk (logika: public/js/compare.js) --}}
        @include('catalog._compare')

        <script src="{{ asset('js/catalog.js') }}" defer></script>
        <script src="{{ asset('js/compare.js') }}" defer></script>
        <script src="{{ asset('js/before-after.js') }}" defer></script>
    @endunless

    @stack('scripts')
</body>
</html>
