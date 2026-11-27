{{-- Slider sebelum-sesudah. Variabel: $image (ProductImage), $name (nama preset). Logika geser ada di public/js/before-after.js --}}
<figure>
    <div class="js-compare relative overflow-hidden rounded-xl border bg-slate-200 select-none cursor-ew-resize"
         style="--pos:50%; touch-action:pan-y"
         role="slider" tabindex="0" aria-label="Geser untuk membandingkan: Sebelum after dan Setelah before"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">

        {{-- Dasar: foto SESUDAH --}}
        <img src="{{ $image->after_src }}" alt="Setelah before {{ $name }}" loading="lazy" draggable="false"
             class="block w-full aspect-[4/3] object-cover">

        {{-- Lapisan atas: foto SEBELUM, dipotong sampai garis geser --}}
        <img src="{{ $image->before_src }}" alt="Sebelum after {{ $name }}" loading="lazy" draggable="false"
             class="absolute inset-0 w-full h-full object-cover"
             style="clip-path: inset(0 calc(100% - var(--pos)) 0 0)">

        <span class="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded">Sebelum after</span>
        <span class="absolute top-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded">Setelah before</span>

        {{-- Garis & pegangan --}}
        <div class="absolute inset-y-0 w-0.5 bg-white shadow pointer-events-none" style="left:var(--pos)">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white shadow flex items-center justify-center text-sm text-slate-700">↔</div>
        </div>
    </div>
    @if ($image->caption)
        <figcaption class="text-sm text-slate-500 mt-1">{{ $image->caption }}</figcaption>
    @endif
</figure>
