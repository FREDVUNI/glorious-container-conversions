/* forms.js — client-side validation and feedback for the quote request form.
   PROTOTYPE BEHAVIOUR: nothing is sent anywhere. On a valid submit we show a
   clearly labelled "not yet connected" state that summarises the entry and
   points to phone/email. The real submission endpoint is a later phase. */
(function () {
  'use strict';
  const form = document.querySelector('[data-quote-form]');
  if (!form) return;

  const status = document.getElementById('form-status');
  const submitBtn = form.querySelector('[type="submit"]');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const rules = {
    name: (v) => v.trim().length >= 2 || 'Please enter your name.',
    email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || 'Enter a valid email address, e.g. name@example.com.',
    phone: (v) => v.trim() === '' || /^[+\d][\d\s()-]{6,}$/.test(v.trim()) || 'Enter a phone number with digits only, e.g. +256 7xx xxx xxx.',
    project_type: (v) => v !== '' || 'Choose the type of project.',
    location: (v) => v.trim().length >= 2 || 'Tell us where the unit will be installed.',
    message: (v) => v.trim().length >= 20 || 'A few sentences help us quote accurately (20 characters minimum).',
  };

  function fieldWrap(input) { return input.closest('.field'); }
  function errorEl(input) { return fieldWrap(input).querySelector('.error'); }
  function value(name) {
    const el = form.elements[name];
    if (!el) return '';
    if (el instanceof RadioNodeList) return el.value;
    return el.value;
  }
  function validateField(name) {
    const rule = rules[name]; if (!rule) return true;
    const el = form.elements[name];
    const input = el instanceof RadioNodeList ? el[0] : el;
    const wrap = fieldWrap(input); const err = errorEl(input);
    const res = rule(value(name));
    if (res === true) {
      wrap.classList.remove('is-invalid'); wrap.classList.add('is-valid');
      err.textContent = ''; input.removeAttribute('aria-invalid');
      return true;
    }
    wrap.classList.add('is-invalid'); wrap.classList.remove('is-valid');
    err.textContent = res; input.setAttribute('aria-invalid', 'true');
    return false;
  }

  // Validate on blur, re-validate on input once a field has been marked invalid.
  Object.keys(rules).forEach((name) => {
    const el = form.elements[name]; if (!el) return;
    const inputs = el instanceof RadioNodeList ? Array.from(el) : [el];
    inputs.forEach((input) => {
      input.addEventListener('blur', () => validateField(name));
      input.addEventListener('input', () => { if (fieldWrap(input).classList.contains('is-invalid')) validateField(name); });
      input.addEventListener('change', () => validateField(name));
    });
  });

  function icon(kind) {
    if (kind === 'error') return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 8v5M12 16.5v.5M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>';
  }
  function esc(s) { return String(s).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])); }

  function showStatus(html) {
    status.innerHTML = html; status.hidden = false;
    status.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'nearest' });
    const h = status.querySelector('h3'); if (h) { h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true }); }
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const invalid = Object.keys(rules).filter((n) => !validateField(n));
    if (invalid.length) {
      const first = form.elements[invalid[0]];
      const input = first instanceof RadioNodeList ? first[0] : first;
      showStatus(
        '<div class="status-card is-error" role="alert"><div class="icon">' + icon('error') + '</div><div>' +
        '<h3>Please check ' + invalid.length + ' field' + (invalid.length > 1 ? 's' : '') + '</h3>' +
        '<p>Some details are missing or look incorrect. The highlighted fields explain what to fix.</p></div></div>'
      );
      input.focus({ preventScroll: true });
      input.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
      return;
    }
    // Simulate a short "working" state so the button feedback can be reviewed.
    submitBtn.classList.add('is-loading'); submitBtn.setAttribute('aria-busy', 'true');
    status.hidden = true;
    setTimeout(() => {
      submitBtn.classList.remove('is-loading'); submitBtn.removeAttribute('aria-busy');
      const size = value('size') || 'Not specified';
      const timeline = value('timeline') || 'Not specified';
      showStatus(
        '<div class="status-card is-success" role="status"><div class="icon">' + icon('ok') + '</div><div>' +
        '<h3>Details validated — not yet sent</h3>' +
        '<p>This is a design prototype. Your request was checked in the browser but <b>has not been delivered to Glorious Container Conversions</b>. Email delivery and CRM hand-off are connected in the next (backend) phase. To talk to the team now, use the phone or email below.</p>' +
        '<dl><dt>Name</dt><dd>' + esc(value('name')) + '</dd>' +
        '<dt>Email</dt><dd>' + esc(value('email')) + '</dd>' +
        (value('phone') ? '<dt>Phone</dt><dd>' + esc(value('phone')) + '</dd>' : '') +
        '<dt>Project</dt><dd>' + esc(form.elements.project_type.options[form.elements.project_type.selectedIndex].text) + '</dd>' +
        '<dt>Size</dt><dd>' + esc(size) + '</dd>' +
        '<dt>Location</dt><dd>' + esc(value('location')) + '</dd>' +
        '<dt>Timeline</dt><dd>' + esc(timeline) + '</dd></dl>' +
        '<div class="alt"><a class="btn btn-sm" href="tel:+256783063789">Call +256 783 063 789</a>' +
        '<a class="btn btn-sm btn-ghost" href="mailto:gloriouscontainerconversions@gmail.com">Email the team</a></div>' +
        '</div></div>'
      );
    }, 900);
  });

  form.addEventListener('reset', () => {
    form.querySelectorAll('.field').forEach((f) => { f.classList.remove('is-invalid', 'is-valid'); const e = f.querySelector('.error'); if (e) e.textContent = ''; });
    status.hidden = true;
  });
})();
