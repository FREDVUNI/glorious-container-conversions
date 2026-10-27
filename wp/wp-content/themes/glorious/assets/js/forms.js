/* forms.js — inline validation on the quote form, before it posts to WordPress.
   The server (gcc-core's Forms class) checks everything again and is the
   authority; this only saves a round trip for the obvious slips. A valid form
   submits normally; a server rejection comes back with the fields marked. */
(function () {
  'use strict';
  const form = document.querySelector('[data-quote-form]');
  if (!form) return;
  const submitBtn = form.querySelector('[type="submit"]');
  const status = document.getElementById('form-status-client');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const rules = {
    name: (v) => v.trim().length >= 2 || 'Please enter your name.',
    email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || 'Enter a valid email address, e.g. name@example.com.',
    phone: (v) => v.trim() === '' || /^[+\d][\d\s()-]{6,}$/.test(v.trim()) || 'Enter a phone number with the country code, e.g. +256 7xx xxx xxx.',
    project_type: (v) => v !== '' || 'Choose the type of project.',
    location: (v) => v.trim().length >= 2 || 'Tell us where the unit will be installed.',
    message: (v) => v.trim().length >= 20 || 'A few sentences help us quote accurately (20 characters minimum).',
  };
  const fieldWrap = (input) => input.closest('.field');
  const value = (name) => { const el = form.elements[name]; return el ? el.value : ''; };
  function validateField(name) {
    const rule = rules[name]; if (!rule) return true;
    const el = form.elements[name]; if (!el) return true;
    const input = el instanceof RadioNodeList ? el[0] : el;
    const wrap = fieldWrap(input); const err = wrap.querySelector('.error');
    const res = rule(value(name));
    if (res === true) { wrap.classList.remove('is-invalid'); wrap.classList.add('is-valid'); if (err) err.textContent = ''; input.removeAttribute('aria-invalid'); return true; }
    wrap.classList.add('is-invalid'); wrap.classList.remove('is-valid'); if (err) err.textContent = res; input.setAttribute('aria-invalid', 'true');
    return false;
  }
  Object.keys(rules).forEach((name) => {
    const el = form.elements[name]; if (!el) return;
    (el instanceof RadioNodeList ? Array.from(el) : [el]).forEach((input) => {
      input.addEventListener('blur', () => validateField(name));
      input.addEventListener('input', () => { if (fieldWrap(input).classList.contains('is-invalid')) validateField(name); });
      input.addEventListener('change', () => validateField(name));
    });
  });

  form.addEventListener('submit', (e) => {
    const invalid = Object.keys(rules).filter((n) => !validateField(n));
    if (invalid.length) {
      e.preventDefault();
      const first = form.elements[invalid[0]];
      const input = first instanceof RadioNodeList ? first[0] : first;
      if (status) {
        status.innerHTML = '<div class="status-card is-error" role="alert"><div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 8v5M12 16.5v.5M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></div><div><h3>Please check ' + invalid.length + ' field' + (invalid.length > 1 ? 's' : '') + '</h3><p>Some details are missing or look incorrect. The highlighted fields explain what to fix.</p></div></div>';
        status.hidden = false;
      }
      input.focus({ preventScroll: true });
      input.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
      return;
    }
    if (status) status.hidden = true;
    submitBtn.classList.add('is-loading'); submitBtn.setAttribute('aria-busy', 'true');
  });

  form.addEventListener('reset', () => {
    form.querySelectorAll('.field').forEach((f) => { f.classList.remove('is-invalid', 'is-valid'); const e = f.querySelector('.error'); if (e) e.textContent = ''; });
    if (status) status.hidden = true;
  });

  // A server message in the page: put the eye on it.
  const flash = document.querySelector('.flash .status-card');
  if (flash) { flash.focus({ preventScroll: true }); flash.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' }); }
})();
