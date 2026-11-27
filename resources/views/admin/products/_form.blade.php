{{-- Form bersama untuk create & edit. Variabel: $product, $categories, $submitLabel --}}
@php
    $input = 'w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500';
@endphp

@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm" role="alert">
        <p class="font-semibold mb-1">Periksa kembali isian Anda:</p>
        <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    {{-- ===== Kolom kiri: data teks ===== --}}
    <div class="lg:col-span-2 space-y-4 bg-white border rounded-xl p-5">
        <h2 class="font-bold text-lg">Informasi preset</h2>

        <div>
            <label for="name" class="block text-sm font-medium mb-1">Nama preset *</label>
            <input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="150" class="{{ $input }}">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="category" class="block text-sm font-medium mb-1">Gaya / kategori *</label>
                <input id="category" name="category" value="{{ old('category', $product->category) }}" required maxlength="50" list="category-list" class="{{ $input }}" placeholder="Cerah, Moody, Vintage…">
                <datalist id="category-list">@foreach ($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
            </div>
            <div>
                <label for="price" class="block text-sm font-medium mb-1">Harga (Rp) *</label>
                <input id="price" type="number" name="price" value="{{ old('price', $product->price !== null ? (int) $product->price : '') }}" required min="0" max="99999999" step="1" inputmode="numeric" class="{{ $input }}">
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea id="description" name="description" rows="4" maxlength="5000" class="{{ $input }}">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="preset_count" class="block text-sm font-medium mb-1">Jumlah preset dalam paket *</label>
                <input id="preset_count" type="number" name="preset_count" value="{{ old('preset_count', $product->preset_count ?? 1) }}" required min="1" max="1000" class="{{ $input }}">
            </div>
            <div>
                <label for="supported_apps" class="block text-sm font-medium mb-1">Aplikasi yang didukung</label>
                <input id="supported_apps" name="supported_apps" value="{{ old('supported_apps', $product->supported_apps) }}" maxlength="150" class="{{ $input }}" placeholder="Lightroom Mobile & Desktop">
            </div>
            <div>
                <label for="file_format" class="block text-sm font-medium mb-1">Format file</label>
                <input id="file_format" name="file_format" value="{{ old('file_format', $product->file_format) }}" maxlength="50" class="{{ $input }}" placeholder="DNG, XMP">
            </div>
            <div>
                <label for="file_size" class="block text-sm font-medium mb-1">Ukuran file</label>
                <input id="file_size" name="file_size" value="{{ old('file_size', $product->file_size) }}" maxlength="50" class="{{ $input }}" placeholder="18 MB">
            </div>
        </div>

        <div>
            <label for="download_url" class="block text-sm font-medium mb-1">File untuk diunduh pembeli</label>
            <input id="download_url" name="download_url" value="{{ old('download_url', $product->download_url) }}" maxlength="500" class="{{ $input }}" placeholder="https://drive.google.com/… atau presets/warm.zip">
            <p class="text-xs text-slate-500 mt-1">Link eksternal, atau path file di <code>storage/app/private</code> (lebih aman: file tidak punya alamat publik).</p>
        </div>

        <div>
            <label for="install_guide" class="block text-sm font-medium mb-1">Panduan pemasangan khusus (opsional)</label>
            <textarea id="install_guide" name="install_guide" rows="3" maxlength="5000" class="{{ $input }}" placeholder="Kosongkan untuk memakai panduan umum Lightroom.">{{ old('install_guide', $product->install_guide) }}</textarea>
        </div>
    </div>

    {{-- ===== Kolom kanan: status & foto utama ===== --}}
    <div class="space-y-4">
        <div class="bg-white border rounded-xl p-5 space-y-2">
            <h2 class="font-bold text-lg">Status</h2>
            @foreach (['is_active' => 'Aktif (tampil di katalog)', 'is_featured' => 'Label Terlaris', 'is_new' => 'Label Baru'] as $flag => $label)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="{{ $flag }}" value="1" class="accent-emerald-600 h-4 w-4"
                           @checked($errors->any() ? old($flag) : $product->{$flag})>
                    {{ $label }}
                </label>
            @endforeach
        </div>

        <div class="bg-white border rounded-xl p-5">
            <h2 class="font-bold text-lg mb-2">Foto utama</h2>
            <img id="main-image-preview" src="{{ $product->image_url ? $product->image : '' }}" alt="Pratinjau foto utama"
                 class="w-full aspect-[3/2] object-cover rounded-lg bg-slate-200 mb-2 {{ $product->image_url ? '' : 'hidden' }}">
            <input id="main_image" type="file" name="main_image" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
            <p class="text-xs text-slate-500 mt-1">JPG / PNG / WEBP, maks. 2 MB. Kosongkan jika tidak ingin mengganti.</p>
        </div>
    </div>
</div>

{{-- ===== Galeri sebelum-sesudah ===== --}}
<section class="bg-white border rounded-xl p-5 mt-6">
    <h2 class="font-bold text-lg mb-1">Galeri sebelum-sesudah</h2>
    <p class="text-xs text-slate-500 mb-4">Satu baris = sepasang foto. Maks. 2 MB per foto. Jika ada error, pilih ulang file foto (browser tidak menyimpan file).</p>

    {{-- Pasangan yang sudah ada (halaman edit) --}}
    @if ($product->exists && $product->images->isNotEmpty())
        <div class="grid sm:grid-cols-2 gap-3 mb-4">
            @foreach ($product->images as $image)
                <div class="border rounded-lg p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <img src="{{ $image->before_src }}" alt="Sebelum after" class="w-full aspect-[4/3] object-cover rounded bg-slate-200">
                        <img src="{{ $image->after_src }}" alt="Setelah before" class="w-full aspect-[4/3] object-cover rounded bg-slate-200">
                    </div>
                    <p class="text-sm mt-2">{{ $image->caption ?: 'Tanpa keterangan' }}</p>
                    <label class="flex items-center gap-2 text-sm text-red-700 mt-1">
                        <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="accent-red-600"> Hapus pasangan ini
                    </label>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Baris baru (ditambah oleh admin.js) --}}
    <div id="gallery-rows" class="space-y-3"></div>

    <button type="button" id="gallery-add" class="mt-3 border border-slate-300 rounded-lg px-4 py-2 text-sm hover:bg-slate-100">+ Tambah pasangan foto</button>

    <template id="gallery-row-template">
        <div class="js-gallery-row border rounded-lg p-3 grid sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium mb-1">Sebelum after</label>
                <input type="file" name="gallery[__INDEX__][before]" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Setelah before</label>
                <input type="file" name="gallery[__INDEX__][after]" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
            </div>
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium mb-1">Keterangan</label>
                    <input type="text" name="gallery[__INDEX__][caption]" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="button" class="js-remove-row text-red-600 text-sm px-2 py-2" aria-label="Hapus baris">✕</button>
            </div>
        </div>
    </template>
</section>

<div class="mt-6 flex gap-3">
    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-lg">{{ $submitLabel }}</button>
    <a href="{{ route('admin.products.index') }}" class="border border-slate-300 px-6 py-3 rounded-lg hover:bg-slate-100">Batal</a>
</div>

@push('scripts')
    <script src="{{ asset('js/admin.js') }}" defer></script>
@endpush
