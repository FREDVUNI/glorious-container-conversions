/* projects.js — the Work page's filters, search and lightbox.
   The cards are rendered by WordPress (archive-gcc_project.php) so the page
   reads without JavaScript; the project data for the lightbox is printed into
   #gcc-projects-data by the same template. Filters are links to the category
   pages, so they work without JS too; with JS they filter in place. */
(function () {
  'use strict';
  const grid = document.getElementById('projects-grid');
  const dataEl = document.getElementById('gcc-projects-data');
  if (!grid || !dataEl) return;
  let PROJECTS = [];
  try { PROJECTS = JSON.parse(dataEl.textContent || '[]'); } catch (e) { return; }
  const cards = Array.from(grid.querySelectorAll('.project-card[data-index]'));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Filters + search --------------------------------------- */
  const filterBtns = document.querySelectorAll('[data-filter]');
  const search = document.getElementById('project-search');
  const count = document.getElementById('result-count');
  const empty = document.getElementById('projects-empty');
  const emptyReset = document.getElementById('projects-empty-reset');
  let activeCat = grid.getAttribute('data-initial') || 'all', query = '';
  const catName = (slug) => { const p = PROJECTS.find((x) => x.cat === slug); return p ? p.catName : slug; };

  function applyFilters() {
    const q = query.trim().toLowerCase();
    let shown = 0;
    cards.forEach((card) => {
      const p = PROJECTS[parseInt(card.getAttribute('data-index'), 10)];
      if (!p) return;
      const matchCat = activeCat === 'all' || p.cat === activeCat;
      const specs = Object.keys(p.specs).map((k) => k + ' ' + p.specs[k]).join(' ');
      const hay = (p.title + ' ' + p.summary + ' ' + p.catName + ' ' + specs + ' ' + p.features.join(' ')).toLowerCase();
      const show = matchCat && (!q || hay.includes(q));
      card.classList.toggle('is-hidden', !show);
      if (show) { shown++; card.classList.add('is-in'); }
    });
    if (count) count.textContent = shown + ' of ' + cards.length + ' projects' + (activeCat !== 'all' ? ' · ' + catName(activeCat) : '') + (q ? ' · “' + query.trim() + '”' : '');
    if (empty) empty.hidden = shown !== 0;
  }
  filterBtns.forEach((b) => b.addEventListener('click', (e) => {
    e.preventDefault();
    filterBtns.forEach((x) => x.setAttribute('aria-pressed', 'false'));
    b.setAttribute('aria-pressed', 'true');
    activeCat = b.getAttribute('data-filter');
    history.replaceState(null, '', b.getAttribute('href'));
    applyFilters();
  }));
  if (search) search.addEventListener('input', () => { query = search.value; applyFilters(); });
  if (emptyReset) emptyReset.addEventListener('click', () => {
    query = ''; if (search) search.value = '';
    activeCat = 'all'; filterBtns.forEach((x) => x.setAttribute('aria-pressed', x.getAttribute('data-filter') === 'all' ? 'true' : 'false'));
    applyFilters();
  });
  applyFilters();

  /* ---------- Modal / lightbox --------------------------------------- */
  const modal = document.getElementById('project-modal');
  if (!modal) return;
  const stage = modal.querySelector('.stage-media');
  const thumbs = modal.querySelector('.thumbs');
  const counter = modal.querySelector('.counter');
  const q = (s) => modal.querySelector(s);
  const title = q('[data-m-title]'), cat = q('[data-m-cat]'), ref = q('[data-m-ref]'), summary = q('[data-m-summary]');
  const specs = q('[data-m-specs]'), features = q('[data-m-features]'), note = q('[data-m-note]');
  const quote = q('[data-m-quote]'), link = q('[data-m-link]');
  const prevImg = q('[data-prev-img]'), nextImg = q('[data-next-img]'), prevProj = q('[data-prev-project]'), nextProj = q('[data-next-project]');
  let current = 0, slide = 0, lastFocus = null;
  const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

  function loaded(scope) {
    scope.querySelectorAll('.media img').forEach((im) => {
      const wrap = im.closest('.media'); const done = () => wrap.classList.add('is-loaded');
      if (im.complete && im.naturalWidth) done(); else { im.addEventListener('load', done, { once: true }); im.addEventListener('error', done, { once: true }); }
    });
  }
  function renderProject(i) {
    current = (i + PROJECTS.length) % PROJECTS.length; slide = 0;
    const p = PROJECTS[current];
    title.textContent = p.title; cat.textContent = p.catName; ref.textContent = p.ref || 'GCC';
    summary.textContent = p.summary;
    specs.innerHTML = Object.keys(p.specs).map((k) => '<dt>' + esc(k) + '</dt><dd>' + esc(p.specs[k]) + '</dd>').join('');
    features.innerHTML = p.features.map((f) => '<li>' + esc(f) + '</li>').join('');
    note.hidden = !p.confirm;
    quote.href = p.quote; link.href = p.url;
    stage.innerHTML = p.images.map((im, n) => '<figure' + (n ? ' hidden' : '') + '><span class="media"><img src="' + esc(im.src) + '" alt="' + esc(im.alt) + '" loading="' + (n ? 'lazy' : 'eager') + '" decoding="async"></span></figure>').join('');
    loaded(stage);
    thumbs.innerHTML = p.images.map((im, n) => '<button type="button" aria-label="Show photo ' + (n + 1) + '" aria-current="' + (n === 0) + '"><img src="' + esc(im.thumb) + '" alt="" loading="lazy"></button>').join('');
    thumbs.querySelectorAll('button').forEach((b, n) => b.addEventListener('click', () => showSlide(n)));
    prevProj.querySelector('span').textContent = PROJECTS[(current - 1 + PROJECTS.length) % PROJECTS.length].title;
    nextProj.querySelector('span').textContent = PROJECTS[(current + 1) % PROJECTS.length].title;
    updateCounter();
    modal.querySelector('.modal-side-body').scrollTop = 0;
  }
  function updateCounter() {
    const n = PROJECTS[current].images.length;
    counter.textContent = String(slide + 1).padStart(2, '0') + ' / ' + String(n).padStart(2, '0');
    prevImg.disabled = nextImg.disabled = n < 2;
  }
  function showSlide(n) {
    const len = PROJECTS[current].images.length;
    slide = (n + len) % len;
    stage.querySelectorAll('figure').forEach((f, i) => { f.hidden = i !== slide; });
    thumbs.querySelectorAll('button').forEach((b, i) => b.setAttribute('aria-current', String(i === slide)));
    updateCounter();
  }
  function openModal(i) {
    lastFocus = document.activeElement;
    renderProject(i);
    modal.classList.add('is-open'); modal.removeAttribute('aria-hidden');
    document.body.classList.add('modal-open');
    document.addEventListener('keydown', onKey);
    setTimeout(() => modal.querySelector('[data-close]').focus(), 50);
  }
  function closeModal() {
    modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    document.removeEventListener('keydown', onKey);
    if (lastFocus) lastFocus.focus();
  }
  function onKey(e) {
    if (e.key === 'Escape') closeModal();
    else if (e.key === 'ArrowRight') showSlide(slide + 1);
    else if (e.key === 'ArrowLeft') showSlide(slide - 1);
    else if (e.key === 'Tab') {
      const f = modal.querySelectorAll('button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])');
      const first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  }
  // A card click opens the lightbox; the link still goes to the project page without JS (and for middle-clicks).
  cards.forEach((card) => {
    const a = card.querySelector('[data-project-open]');
    if (!a) return;
    a.addEventListener('click', (e) => {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
      e.preventDefault(); openModal(parseInt(card.getAttribute('data-index'), 10));
    });
  });
  prevImg.addEventListener('click', () => showSlide(slide - 1));
  nextImg.addEventListener('click', () => showSlide(slide + 1));
  prevProj.addEventListener('click', () => renderProject(current - 1));
  nextProj.addEventListener('click', () => renderProject(current + 1));
  modal.querySelectorAll('[data-close]').forEach((b) => b.addEventListener('click', closeModal));
  stage.addEventListener('click', (e) => { if (e.target === stage || e.target.tagName === 'FIGURE' || e.target.classList.contains('media')) closeModal(); });
  let tx = null;
  stage.addEventListener('touchstart', (e) => { tx = e.touches[0].clientX; }, { passive: true });
  stage.addEventListener('touchend', (e) => { if (tx === null) return; const dx = e.changedTouches[0].clientX - tx; tx = null; if (Math.abs(dx) > 50) showSlide(slide + (dx < 0 ? 1 : -1)); });
  modal.setAttribute('aria-hidden', 'true');

  const m = location.hash.match(/project=([\w-]+)/);
  if (m) { const idx = PROJECTS.findIndex((p) => p.id === m[1]); if (idx > -1) openModal(idx); }
  if (reduceMotion) cards.forEach((c) => c.classList.add('is-in'));
})();
