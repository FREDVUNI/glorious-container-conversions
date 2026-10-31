/* main.js — page-level behaviour shared by every page.
   Responsibilities: loader, scroll reveals, header state, counters,
   image load states, marquee, scroll-spy, back-to-top, footer year.
   No dependencies. Each feature is self-contained and null-safe so pages
   can omit any element. */
(function () {
  'use strict';

  const html = document.documentElement;
  html.classList.remove('no-js');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Loader -------------------------------------------------- */
  // Shown once per browser session (first page view). Later pages skip it
  // so navigating between pages feels instant.
  const loader = document.querySelector('.loader');
  const seen = (() => { try { return sessionStorage.getItem('gcc-loaded'); } catch (e) { return null; } })();
  function ready() {
    html.classList.add('is-ready');
    if (loader) loader.classList.add('is-done');
    try { sessionStorage.setItem('gcc-loaded', '1'); } catch (e) { /* storage unavailable */ }
  }
  if (!loader || seen || reduceMotion) {
    if (loader) loader.remove();
    requestAnimationFrame(() => requestAnimationFrame(ready));
  } else {
    const start = performance.now();
    window.addEventListener('load', () => {
      const wait = Math.max(0, 1100 - (performance.now() - start));
      setTimeout(ready, wait);
    });
    // Safety: never trap the user behind the loader.
    setTimeout(ready, 4000);
  }

  /* ---------- Scroll reveals ----------------------------------------- */
  const revealEls = document.querySelectorAll('[data-reveal], .split-words');
  if ('IntersectionObserver' in window && !reduceMotion) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const delay = el.getAttribute('data-delay');
        if (delay) el.style.setProperty('--d', delay + 'ms');
        el.classList.add('is-in');
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('is-in'));
  }

  // Split headings into masked words for the rise-in effect.
  document.querySelectorAll('.split-words').forEach((el) => {
    if (reduceMotion || el.dataset.split === 'done') return;
    const walk = (node) => {
      Array.from(node.childNodes).forEach((child) => {
        if (child.nodeType === 3) {
          const frag = document.createDocumentFragment();
          child.textContent.split(/(\s+)/).forEach((part) => {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(part)); return; }
            const w = document.createElement('span'); w.className = 'w';
            const i = document.createElement('i'); i.textContent = part; w.appendChild(i); frag.appendChild(w);
          });
          node.replaceChild(frag, child);
        } else if (child.nodeType === 1 && !child.classList.contains('w')) {
          walk(child);
        }
      });
    };
    walk(el);
    el.dataset.split = 'done';
    // Stagger words
    el.querySelectorAll('.w i').forEach((i, n) => { i.style.transitionDelay = (n * 45) + 'ms'; });
  });

  /* ---------- Header: stays pinned, compacts after scrolling ------------ */
  const header = document.querySelector('.site-header');
  let lastY = window.scrollY, ticking = false;
  function onScroll() {
    const y = window.scrollY;
    if (header) {
      header.classList.toggle('is-scrolled', y > 24);
    }
    if (toTop) toTop.classList.toggle('is-visible', y > 900);
    lastY = y; ticking = false;
  }
  const toTop = document.querySelector('.to-top');
  window.addEventListener('scroll', () => { if (!ticking) { requestAnimationFrame(onScroll); ticking = true; } }, { passive: true });
  onScroll();
  if (toTop) toTop.addEventListener('click', (e) => { e.preventDefault(); window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }); });

  /* ---------- Image load state (fades in, removes stripe placeholder) --- */
  document.querySelectorAll('.media img').forEach((img) => {
    const wrap = img.closest('.media');
    const done = () => wrap && wrap.classList.add('is-loaded');
    if (img.complete && img.naturalWidth) done();
    else { img.addEventListener('load', done, { once: true }); img.addEventListener('error', done, { once: true }); }
  });

  /* ---------- Counters ----------------------------------------------- */
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    const run = (el) => {
      const target = parseFloat(el.getAttribute('data-count'));
      const suffix = el.getAttribute('data-suffix') || '';
      if (reduceMotion) { el.textContent = target + suffix; return; }
      const dur = 1200, start = performance.now();
      const tick = (t) => {
        const p = Math.min(1, (t - start) / dur);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased) + suffix;
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };
    if ('IntersectionObserver' in window) {
      const cio = new IntersectionObserver((entries) => {
        entries.forEach((en) => { if (en.isIntersecting) { run(en.target); cio.unobserve(en.target); } });
      }, { threshold: 0.5 });
      counters.forEach((c) => cio.observe(c));
    } else counters.forEach(run);
  }

  /* ---------- Marquee: duplicate content so the loop is seamless ------ */
  document.querySelectorAll('.marquee-track').forEach((track) => {
    track.innerHTML += track.innerHTML;
  });

  /* ---------- Scroll-spy for in-page section nav (services page) ------ */
  const spyLinks = document.querySelectorAll('[data-spy] a[href^="#"]');
  if (spyLinks.length && 'IntersectionObserver' in window) {
    const map = new Map();
    spyLinks.forEach((a) => { const t = document.getElementById(a.getAttribute('href').slice(1)); if (t) map.set(t, a); });
    const sio = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) {
          spyLinks.forEach((a) => a.classList.remove('is-active'));
          map.get(en.target).classList.add('is-active');
        }
      });
    }, { rootMargin: '-30% 0px -60% 0px' });
    map.forEach((_, t) => sio.observe(t));
  }

  /* ---------- Smooth in-page anchors (respects header offset) --------- */
  document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach((a) => {
    a.addEventListener('click', (e) => {
      const id = a.getAttribute('href').slice(1);
      const target = document.getElementById(id);
      if (!target) return;
      e.preventDefault();
      const offset = (header ? header.offsetHeight : 0) + 16;
      const top = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: reduceMotion ? 'auto' : 'smooth' });
      target.setAttribute('tabindex', '-1'); target.focus({ preventScroll: true });
      history.replaceState(null, '', '#' + id);
    });
  });

  /* ---------- Current-page marking in nav ---------------------------- */
  const page = document.body.getAttribute('data-page');
  if (page) {
    document.querySelectorAll('[data-nav]').forEach((a) => {
      if (a.getAttribute('data-nav') === page) a.setAttribute('aria-current', 'page');
    });
  }

  /* ---------- Hero slideshow ----------------------------------------- */
  // Crossfade between hero photos. Autoplays unless reduced motion is set;
  // pauses on hover/focus; arrows, dots and keyboard (when focused) all work.
  const slider = document.querySelector('[data-hero-slider]');
  if (slider) {
    const slides = Array.from(slider.querySelectorAll('.hero-slide'));
    const dots = slider.querySelector('.hero-dots');
    const cap = slider.querySelector('[data-slide-caption]');
    const label = slider.querySelector('[data-slide-label]');
    const plateLink = slider.querySelector('[data-slide-link]');
    let idx = 0, timer = null;
    const INTERVAL = Math.max(2000, parseInt(slider.getAttribute('data-interval'), 10) || 5500);
    const AUTOPLAY = slider.getAttribute('data-autoplay') !== 'no';
    slides.forEach((s, n) => {
      const d = document.createElement('button');
      d.type = 'button'; d.setAttribute('role', 'tab'); d.setAttribute('aria-label', 'Photo ' + (n + 1));
      d.setAttribute('aria-selected', String(n === 0));
      d.addEventListener('click', () => { go(n); restart(); });
      dots.appendChild(d);
    });
    function go(n) {
      idx = (n + slides.length) % slides.length;
      slides.forEach((s, i) => s.classList.toggle('is-active', i === idx));
      dots.querySelectorAll('button').forEach((d, i) => d.setAttribute('aria-selected', String(i === idx)));
      if (cap) cap.textContent = slides[idx].getAttribute('data-caption');
      if (label) label.innerHTML = slides[idx].getAttribute('data-label');
      if (plateLink) { const l = slides[idx].getAttribute('data-link') || ''; plateLink.href = l || '#'; plateLink.style.pointerEvents = l ? '' : 'none'; }
    }
    function start() { if (!AUTOPLAY || reduceMotion || slides.length < 2) return; stop(); timer = setInterval(() => go(idx + 1), INTERVAL); }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function restart() { stop(); start(); }
    slider.querySelector('[data-slide-prev]').addEventListener('click', () => { go(idx - 1); restart(); });
    slider.querySelector('[data-slide-next]').addEventListener('click', () => { go(idx + 1); restart(); });
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);
    slider.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight') { go(idx + 1); restart(); }
      if (e.key === 'ArrowLeft') { go(idx - 1); restart(); }
    });
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
    // Mark slide wrappers loaded as their images arrive.
    slides.forEach((s) => { const im = s.querySelector('img'); const done = () => s.classList.add('is-loaded'); if (im.complete && im.naturalWidth) done(); else im.addEventListener('load', done, { once: true }); });
    start();
  }

  /* ---------- Chat widgets: keep back-to-top clear of them ----------- */
  const chatCheck = () => { if (document.querySelector('.chaty-widget, .chaty, #chaty-widget, .joinchat, .wa-chat-box')) document.body.classList.add('has-chat-widget'); };
  chatCheck(); window.addEventListener('load', chatCheck); setTimeout(chatCheck, 2500);

  /* ---------- Footer year -------------------------------------------- */
  document.querySelectorAll('[data-year]').forEach((el) => { el.textContent = new Date().getFullYear(); });
})();
