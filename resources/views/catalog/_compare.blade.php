{{-- Bar bawah + modal untuk membandingkan 2-3 preset. Isi tabel dibuat oleh public/js/compare.js --}}
<div id="compare-bar" class="hidden fixed bottom-0 inset-x-0 z-20 bg-white border-t shadow-[0_-2px_10px_rgba(0,0,0,.08)]">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center gap-3">
        <div id="compare-thumbs" class="flex -space-x-2 shrink-0" aria-hidden="true"></div>
        <div class="flex-1 min-w-0 text-sm">
            <p><strong><span id="compare-count">0</span>/3</strong> preset dipilih</p>
            <p id="compare-msg" class="text-amber-700 text-xs truncate" role="status" aria-live="polite"></p>
        </div>
        <button type="button" id="compare-clear" class="text-sm text-slate-600 hover:text-slate-900 px-2 py-2">Hapus</button>
        <button type="button" id="compare-open"
                class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed">
            Bandingkan
        </button>
    </div>
</div>

<dialog id="compare-dialog" class="m-auto w-[calc(100%-1rem)] max-w-4xl max-h-[90vh] rounded-xl p-0 backdrop:bg-black/50">
    <div class="flex items-center justify-between px-4 py-3 border-b sticky top-0 bg-white z-10">
        <h2 class="font-bold text-lg">Perbandingan preset</h2>
        <button type="button" id="compare-close" class="text-2xl leading-none px-2" aria-label="Tutup">&times;</button>
    </div>
    <div id="compare-table" class="overflow-x-auto p-4"></div>
</dialog>
