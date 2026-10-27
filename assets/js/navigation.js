/* navigation.js — mobile navigation drawer.
   Accessible disclosure: aria-expanded on the toggle, focus trapped inside
   the drawer while open, Escape closes, body scroll locked. */
(function () {
  'use strict';
  const toggle = document.querySelector('.nav-toggle');
  const drawer = document.getElementById('nav-drawer');
  if (!toggle || !drawer) return;

  const focusable = () => drawer.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])');
  let lastFocus = null;

  function open() {
    lastFocus = document.activeElement;
    document.body.classList.add('nav-open');
    toggle.setAttribute('aria-expanded', 'true');
    toggle.setAttribute('aria-label', 'Close menu');
    drawer.removeAttribute('aria-hidden');
    setTimeout(() => { const f = focusable(); if (f.length) f[0].focus(); }, 250);
    document.addEventListener('keydown', onKey);
  }
  function close() {
    document.body.classList.remove('nav-open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Open menu');
    drawer.setAttribute('aria-hidden', 'true');
    document.removeEventListener('keydown', onKey);
    if (lastFocus) lastFocus.focus();
  }
  function onKey(e) {
    if (e.key === 'Escape') { close(); return; }
    if (e.key !== 'Tab') return;
    const f = focusable(); if (!f.length) return;
    const first = f[0], last = f[f.length - 1];
    // Include the toggle button (which sits outside the drawer) in the loop.
    if (e.shiftKey && (document.activeElement === first || document.activeElement === toggle)) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); toggle.focus(); }
  }

  toggle.addEventListener('click', () => (document.body.classList.contains('nav-open') ? close() : open()));
  drawer.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => { if (document.body.classList.contains('nav-open')) close(); }));
  window.addEventListener('resize', () => { if (window.innerWidth > 960 && document.body.classList.contains('nav-open')) close(); });
  drawer.setAttribute('aria-hidden', 'true');
})();
