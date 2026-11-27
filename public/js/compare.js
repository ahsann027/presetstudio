/**
 * compare.js - Bandingkan 2-3 preset secara real-time.
 *
 * Alur:
 *  - Tiap kartu punya checkbox .js-compare-toggle dengan data-product (JSON dari Product::compareData()).
 *  - Pilihan disimpan di sessionStorage, jadi tetap ada saat pindah halaman / saat kartu dimuat ulang
 *    oleh pencarian AJAX (event "catalog:updated").
 *  - Bar bawah (#compare-bar) menampilkan jumlah pilihan; tombol "Bandingkan" membuka <dialog>
 *    berisi tabel perbandingan yang dibuat dengan DOM (textContent) sehingga aman dari XSS.
 */
(function () {
  const MAX = 3;
  const MIN = 2;
  const STORAGE_KEY = 'compare:v1';

  const bar = document.getElementById('compare-bar');
  const dialog = document.getElementById('compare-dialog');
  if (!bar || !dialog) return; // halaman tanpa fitur bandingkan

  const countEl = document.getElementById('compare-count');
  const thumbsEl = document.getElementById('compare-thumbs');
  const msgEl = document.getElementById('compare-msg');
  const openBtn = document.getElementById('compare-open');
  const clearBtn = document.getElementById('compare-clear');
  const closeBtn = document.getElementById('compare-close');
  const tableEl = document.getElementById('compare-table');

  /* ---------- state ---------- */

  let selected = load();
  let msgTimer = null;

  function load() {
    try {
      const data = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
      return Array.isArray(data) ? data.filter((p) => p && Number.isFinite(Number(p.id))).slice(0, MAX) : [];
    } catch (e) {
      return [];
    }
  }

  function save() {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(selected));
    } catch (e) {
      /* penyimpanan tidak tersedia: fitur tetap jalan tanpa persistensi */
    }
  }

  const isSelected = (id) => selected.some((p) => Number(p.id) === Number(id));

  function notify(text) {
    msgEl.textContent = text;
    clearTimeout(msgTimer);
    msgTimer = setTimeout(() => (msgEl.textContent = ''), 3000);
  }

  function remove(id) {
    selected = selected.filter((p) => Number(p.id) !== Number(id));
    save();
    render();
  }

  /* ---------- helper DOM aman ---------- */

  function h(tag, props = {}, ...children) {
    const el = document.createElement(tag);
    for (const [key, value] of Object.entries(props)) {
      if (key === 'class') el.className = value;
      else if (key === 'text') el.textContent = value;
      else el.setAttribute(key, value);
    }
    children.flat().forEach((child) => child != null && el.append(child));
    return el;
  }

  // Hanya izinkan URL satu domain dengan situs ini.
  function safeUrl(url) {
    try {
      const u = new URL(url, window.location.origin);
      return u.origin === window.location.origin ? u.href : '#';
    } catch (e) {
      return '#';
    }
  }

  /* ---------- render bar + checkbox ---------- */

  function render() {
    // Sinkronkan semua checkbox di halaman dengan pilihan saat ini.
    document.querySelectorAll('.js-compare-toggle').forEach((box) => {
      box.checked = isSelected(box.dataset.id);
    });

    countEl.textContent = selected.length;
    bar.classList.toggle('hidden', selected.length === 0);
    document.body.classList.toggle('pb-24', selected.length > 0); // agar konten tidak tertutup bar
    openBtn.disabled = selected.length < MIN;
    openBtn.title = selected.length < MIN ? `Pilih minimal ${MIN} preset` : '';

    thumbsEl.replaceChildren(
      ...selected.map((p) =>
        h('img', {
          src: safeUrl(p.image),
          alt: '',
          class: 'w-9 h-9 rounded-full object-cover border-2 border-white bg-slate-200',
        })
      )
    );

    if (dialog.open) {
      selected.length < MIN ? dialog.close() : buildTable();
    }
  }

  /* ---------- tabel perbandingan ---------- */

  function buildTable() {
    const minPrice = Math.min(...selected.map((p) => Number(p.price_raw)));
    const maxCount = Math.max(...selected.map((p) => Number(p.preset_count)));
    const priceDiffers = selected.some((p) => Number(p.price_raw) !== minPrice);
    const countDiffers = selected.some((p) => Number(p.preset_count) !== maxCount);

    const labelCell = (text) => h('th', { scope: 'row', class: 'text-left text-sm font-medium text-slate-500 p-3 align-top bg-slate-50 sticky left-0 min-w-[7rem]', text });
    const cell = (...content) => h('td', { class: 'p-3 align-top text-sm border-l' }, ...content);

    const rows = [];

    // Baris judul: foto + nama + tombol hapus
    rows.push(
      h('tr', {},
        h('th', { scope: 'row', class: 'bg-slate-50 sticky left-0' }),
        ...selected.map((p) =>
          cell(
            h('img', { src: safeUrl(p.image), alt: `Contoh hasil ${p.name}`, class: 'w-full aspect-[3/2] object-cover rounded-lg bg-slate-200 mb-2' }),
            h('a', { href: safeUrl(p.url), class: 'font-semibold hover:text-emerald-700 block', text: p.name }),
            h('button', { type: 'button', class: 'text-xs text-red-600 hover:underline mt-1', 'data-remove': p.id, text: 'Hapus dari perbandingan' })
          )
        )
      )
    );

    const addRow = (label, getter, best) => {
      rows.push(
        h('tr', { class: 'border-t' },
          labelCell(label),
          ...selected.map((p) => {
            const isBest = best && best(p);
            return cell(
              h('span', { class: isBest ? 'font-bold text-emerald-700' : '', text: getter(p) }),
              isBest ? h('span', { class: 'ml-1 text-xs bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded', text: best.label }) : null
            );
          })
        )
      );
    };

    const cheapest = (p) => priceDiffers && Number(p.price_raw) === minPrice;
    cheapest.label = 'Termurah';
    const mostPresets = (p) => countDiffers && Number(p.preset_count) === maxCount;
    mostPresets.label = 'Terbanyak';

    addRow('Harga', (p) => p.price, cheapest);
    addRow('Gaya', (p) => p.category);
    addRow('Jumlah preset', (p) => `${p.preset_count} preset`, mostPresets);
    addRow('Aplikasi', (p) => p.apps);
    addRow('Format file', (p) => p.format);
    addRow('Ukuran file', (p) => p.size);

    // Baris tombol beli
    rows.push(
      h('tr', { class: 'border-t' },
        h('th', { scope: 'row', class: 'bg-slate-50 sticky left-0' }),
        ...selected.map((p) =>
          cell(h('a', { href: safeUrl(p.buy_url), class: 'block text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg', text: 'Beli sekarang' }))
        )
      )
    );

    tableEl.replaceChildren(
      h('table', { class: 'w-full border-collapse', style: `min-width:${selected.length * 11 + 7}rem` }, h('tbody', {}, rows))
    );
  }

  function openDialog() {
    if (selected.length < MIN) return;
    buildTable();
    if (typeof dialog.showModal === 'function') dialog.showModal();
    else dialog.setAttribute('open', '');
  }

  /* ---------- event ---------- */

  // Centang / hapus centang pada kartu (event delegation: berlaku juga untuk kartu hasil AJAX).
  document.addEventListener('change', (event) => {
    const box = event.target.closest && event.target.closest('.js-compare-toggle');
    if (!box) return;

    let product;
    try {
      product = JSON.parse(box.dataset.product);
    } catch (e) {
      return;
    }

    if (box.checked) {
      if (!isSelected(product.id)) {
        if (selected.length >= MAX) {
          box.checked = false;
          notify(`Maksimal ${MAX} preset. Hapus salah satu dulu.`);
          return;
        }
        selected.push(product);
      }
    } else {
      selected = selected.filter((p) => Number(p.id) !== Number(product.id));
    }

    save();
    render();
  });

  // Tombol hapus di dalam tabel
  tableEl.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-remove]');
    if (btn) remove(btn.dataset.remove);
  });

  openBtn.addEventListener('click', openDialog);
  closeBtn.addEventListener('click', () => dialog.close());
  clearBtn.addEventListener('click', () => {
    selected = [];
    save();
    render();
  });
  // Klik area gelap (backdrop) menutup modal.
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });

  // Kartu dimuat ulang oleh pencarian AJAX -> tandai ulang checkbox yang sudah dipilih.
  document.addEventListener('catalog:updated', render);

  render();
})();
