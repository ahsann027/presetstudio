/**
 * before-after.js - Geser untuk membandingkan foto sebelum-sesudah.
 * Bekerja dengan mouse, sentuhan (ponsel), dan keyboard (panah kiri/kanan).
 * Posisi disimpan di CSS variable --pos; foto "sebelum" dipotong dengan clip-path.
 */
document.querySelectorAll('.js-compare').forEach((box) => {
  let position = 50;
  let dragging = false;

  function setPosition(percent) {
    position = Math.max(0, Math.min(100, percent));
    box.style.setProperty('--pos', position + '%');
    box.setAttribute('aria-valuenow', Math.round(position));
  }

  function moveTo(event) {
    const rect = box.getBoundingClientRect();
    setPosition(((event.clientX - rect.left) / rect.width) * 100);
  }

  box.addEventListener('pointerdown', (event) => {
    dragging = true;
    box.setPointerCapture(event.pointerId);
    moveTo(event);
  });
  box.addEventListener('pointermove', (event) => dragging && moveTo(event));
  box.addEventListener('pointerup', () => (dragging = false));
  box.addEventListener('pointercancel', () => (dragging = false));

  box.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') { event.preventDefault(); setPosition(position - 5); }
    if (event.key === 'ArrowRight') { event.preventDefault(); setPosition(position + 5); }
  });
});
