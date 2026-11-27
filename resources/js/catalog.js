/**
 * catalog.js - JavaScript Vanilla untuk halaman katalog:
 *   1. Pencarian kata kunci + filter gaya/kategori secara AJAX (tanpa reload halaman)
 *   2. Tombol "Salin link"
 *
 * Cara kerja pencarian: setiap perubahan memanggil fetch() ke URL katalog yang sama dengan header
 * X-Requested-With. CatalogController mengenali permintaan AJAX dan hanya mengirim potongan
 * partial _grid.blade.php (yang memuat _card.blade.php), lalu hasilnya ditempel ke #product-grid.
 */

/* ============ 1. SALIN LINK ============ */

document.addEventListener('click', async (event) => {
  const button = event.target.closest('.js-copy');
  if (!button) return;

  try {
    await navigator.clipboard.writeText(button.dataset.url);
    const oldText = button.textContent;
    button.textContent = 'Link tersalin ✓';
    setTimeout(() => (button.textContent = oldText), 2000);
  } catch (e) {
    window.prompt('Salin link berikut:', button.dataset.url);
  }
});

/* ============ 2. PENCARIAN & FILTER AJAX ============ */

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('filter-form');
  if (!form) return; // bukan halaman katalog

  const searchInput = document.getElementById('search-input');
  const categoryInput = form.elements.category;
  const grid = document.getElementById('product-grid');
  const counter = document.getElementById('result-count');
  const chips = document.querySelectorAll('.js-category');

  const ACTIVE = ['bg-emerald-600', 'text-white', 'border-emerald-600'];
  const IDLE = ['bg-white', 'text-slate-700', 'border-slate-300'];

  let activeCategory = form.dataset.category || '';
  let timer = null;
  let controller = null;

  async function loadProducts() {
    const params = new URLSearchParams();
    const keyword = searchInput.value.trim();
    if (keyword) params.set('q', keyword);
    if (activeCategory) params.set('category', activeCategory);
    const query = params.toString();

    // Batalkan permintaan sebelumnya agar hasil lama tidak menimpa hasil terbaru.
    if (controller) controller.abort();
    controller = new AbortController();

    grid.classList.add('opacity-50');
    grid.setAttribute('aria-busy', 'true');

    try {
      const response = await fetch(query ? `${form.action}?${query}` : form.action, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: controller.signal,
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);

      grid.innerHTML = await response.text();
      if (counter) counter.textContent = `${response.headers.get('X-Total') ?? ''} preset`.trim();
      history.replaceState(null, '', query ? `?${query}` : form.action);

      // Beri tahu fitur lain (mis. compare.js) bahwa kartu baru telah dimuat.
      document.dispatchEvent(new CustomEvent('catalog:updated'));
    } catch (error) {
      if (error.name === 'AbortError') return; // dibatalkan sengaja, bukan error
      grid.innerHTML =
        '<p class="col-span-full text-center text-red-600 py-8">Gagal memuat preset. Periksa koneksi lalu coba lagi.</p>';
    } finally {
      grid.classList.remove('opacity-50');
      grid.removeAttribute('aria-busy');
    }
  }

  function highlightChips() {
    chips.forEach((chip) => {
      const isActive = chip.dataset.category === activeCategory;
      ACTIVE.forEach((c) => chip.classList.toggle(c, isActive));
      IDLE.forEach((c) => chip.classList.toggle(c, !isActive));
    });
  }

  // Ketik -> tunggu 300 ms setelah berhenti mengetik -> muat ulang kartu.
  searchInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(loadProducts, 300);
  });

  // Klik chip gaya -> ganti gaya aktif tanpa pindah halaman.
  chips.forEach((chip) => {
    chip.addEventListener('click', (event) => {
      event.preventDefault();
      activeCategory = chip.dataset.category;
      if (categoryInput) categoryInput.value = activeCategory;
      highlightChips();
      loadProducts();
    });
  });

  // Enter di kolom cari tidak memuat ulang halaman penuh.
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    clearTimeout(timer);
    loadProducts();
  });
});
