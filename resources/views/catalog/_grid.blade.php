{{-- Isi grid kartu. Dikirim penuh saat halaman dibuka, dan sendirian saat pencarian lewat fetch(). --}}
@forelse ($products as $product)
    @include('catalog._card', ['product' => $product])
@empty
    <div class="col-span-full text-center py-12 text-slate-500">
        <p class="font-medium">Preset tidak ditemukan.</p>
        <p class="text-sm">Coba kata kunci atau gaya lain.</p>
    </div>
@endforelse
