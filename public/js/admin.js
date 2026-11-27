/**
 * admin.js - Form produk admin:
 *  - tambah/hapus baris pasangan foto sebelum-sesudah
 *  - pratinjau foto utama sebelum diunggah
 *  - cek ukuran file di sisi klien (maks. 2 MB; pemeriksaan sebenarnya tetap di server)
 */
document.addEventListener('DOMContentLoaded', () => {
  const MAX_BYTES = 2 * 1024 * 1024;

  /* ---- Baris galeri ---- */
  const list = document.getElementById('gallery-rows');
  const template = document.getElementById('gallery-row-template');
  const addButton = document.getElementById('gallery-add');
  let index = 0;

  if (list && template && addButton) {
    addButton.addEventListener('click', () => {
      const wrapper = document.createElement('div');
      wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(index++)).trim();
      list.appendChild(wrapper.firstElementChild);
    });

    list.addEventListener('click', (event) => {
      const button = event.target.closest('.js-remove-row');
      if (button) button.closest('.js-gallery-row').remove();
    });
  }

  /* ---- Cek ukuran file untuk semua input gambar ---- */
  document.addEventListener('change', (event) => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;

    const file = input.files[0];
    if (file && file.size > MAX_BYTES) {
      alert('Ukuran foto maksimal 2 MB. Kompres dulu lalu pilih ulang.');
      input.value = '';
      return;
    }

    // Pratinjau foto utama
    if (input.id === 'main_image' && file) {
      const preview = document.getElementById('main-image-preview');
      if (preview) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
      }
    }
  });
});
