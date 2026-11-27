{{-- Satu kartu preset. Dipakai oleh _grid (katalog) dan show (preset terkait). --}}
<article class="bg-white rounded-xl shadow-sm border overflow-hidden flex flex-col">
    <a href="{{ route('catalog.show', $product->id) }}" class="relative block">
        <img src="{{ $product->image }}" alt="Contoh hasil {{ $product->name }}" loading="lazy" width="600" height="400"
             class="w-full aspect-[3/2] object-cover bg-slate-200">
        <div class="absolute top-2 left-2 flex gap-1">
            @if ($product->is_new)
                <span class="bg-sky-500 text-white text-xs font-bold px-2 py-1 rounded">Baru</span>
            @endif
            @if ($product->is_featured)
                <span class="bg-amber-400 text-amber-950 text-xs font-bold px-2 py-1 rounded">Terlaris</span>
            @endif
        </div>
    </a>

    <div class="p-4 flex flex-col flex-1 gap-3">
        <div>
            <span class="text-xs text-emerald-700 font-medium">{{ $product->category }} · {{ $product->preset_count }} preset</span>
            <h3 class="font-semibold leading-snug mt-1">
                <a href="{{ route('catalog.show', $product->id) }}" class="hover:text-emerald-700">{{ $product->name }}</a>
            </h3>
            <p class="text-lg font-bold mt-2">{{ $product->formatted_price }}</p>
        </div>

        <div class="mt-auto space-y-2">
            <a href="{{ route('checkout.create', $product->id) }}"
               class="block text-center w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg">
                Beli sekarang
            </a>

            {{-- Pilih untuk dibandingkan (maks. 3). Data dibaca compare.js --}}
            <label class="flex items-center justify-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
                <input type="checkbox" class="js-compare-toggle h-4 w-4 accent-emerald-600"
                       data-id="{{ $product->id }}"
                       data-product="{{ json_encode($product->compareData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}">
                Bandingkan
            </label>
        </div>
    </div>
</article>
