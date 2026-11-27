<blockquote class="bg-white border rounded-xl p-4">
    @if ($t->image_url)
        <img src="{{ $t->image_url }}" alt="Hasil foto {{ $t->name }}" loading="lazy" class="w-full rounded-lg mb-3 aspect-[4/3] object-cover">
    @endif
    <p class="text-slate-700">&ldquo;{{ $t->quote }}&rdquo;</p>
    <footer class="text-sm text-slate-500 mt-2">- {{ $t->name }}</footer>
</blockquote>
