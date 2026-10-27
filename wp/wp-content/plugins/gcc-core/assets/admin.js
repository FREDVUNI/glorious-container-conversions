/* Settings tabs. The chosen tab is kept in the URL hash so a save lands
   back on the same panel, and in localStorage for next time. */
(function () {
  var links = document.querySelectorAll('.gcc-nav a[data-panel]');
  if (!links.length) return;

  function show(id, push) {
    links.forEach(function (a) { a.classList.toggle('is-active', a.dataset.panel === id); });
    document.querySelectorAll('.gcc-panel').forEach(function (p) { p.classList.toggle('is-active', p.id === id); });
    try { localStorage.setItem('gcc.settings.tab', id); } catch (e) {}
    if (push && history.replaceState) history.replaceState(null, '', '#' + id);
    var ref = document.querySelector('input[name="_wp_http_referer"]');
    if (ref) ref.value = ref.value.split('#')[0] + '#' + id;
  }

  links.forEach(function (a) {
    a.addEventListener('click', function (e) { e.preventDefault(); show(a.dataset.panel, true); });
  });

  var wanted = location.hash.replace('#', '');
  if (!wanted) { try { wanted = localStorage.getItem('gcc.settings.tab') || ''; } catch (e) {} }
  var exists = wanted && document.getElementById(wanted);
  show(exists ? wanted : links[0].dataset.panel, false);
})();

/* Image pickers: the Media Library, one frame per field. */
(function () {
  if (!window.wp || !wp.media) return;
  document.querySelectorAll('[data-image-field]').forEach(function (field) {
    var input = field.querySelector('[data-image-input]');
    var preview = field.querySelector('[data-image-preview]');
    var name = field.querySelector('[data-image-name]');
    var choose = field.querySelector('[data-image-choose]');
    var remove = field.querySelector('[data-image-remove]');
    var frame;

    function set(url, label) {
      input.value = url || '';
      field.classList.toggle('has-image', !!url);
      preview.innerHTML = url ? '<img src="' + url + '" alt="">' : '<span class="dashicons dashicons-format-image"></span>';
      name.textContent = url ? (label || url.split('/').pop()) : 'Nothing chosen — the bundled default is used.';
      choose.innerHTML = '<span class="dashicons dashicons-upload"></span> ' + (url ? 'Change image' : 'Choose image');
      remove.hidden = !url;
    }

    choose.addEventListener('click', function () {
      if (!frame) {
        frame = wp.media({ title: 'Choose an image', button: { text: 'Use this image' }, library: { type: 'image' }, multiple: false });
        frame.on('select', function () {
          var a = frame.state().get('selection').first().toJSON();
          var sized = a.sizes && (a.sizes.large || a.sizes.full);
          set(sized ? sized.url : a.url, a.filename || a.title);
        });
      }
      frame.open();
    });
    remove.addEventListener('click', function () { set('', ''); });
  });
})();

/* Gallery pickers: many images from the Media Library, drag to reorder, × to remove.
   Stored as comma-separated attachment IDs in the hidden input. */
(function () {
  if (!window.wp || !wp.media) return;
  document.querySelectorAll('[data-gallery-field]').forEach(function (field) {
    var input = field.querySelector('[data-gallery-input]');
    var list = field.querySelector('[data-gallery-list]');
    var add = field.querySelector('[data-gallery-add]');
    var frame;

    function sync() {
      input.value = Array.prototype.map.call(list.querySelectorAll('li'), function (li) { return li.dataset.id; }).join(',');
    }
    function item(id, url) {
      var li = document.createElement('li');
      li.dataset.id = id; li.draggable = true;
      li.innerHTML = '<img src="' + url + '" alt=""><button type="button" class="gcc-gallery__remove" data-gallery-remove aria-label="Remove">&times;</button>';
      return li;
    }
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-gallery-remove]');
      if (btn) { btn.closest('li').remove(); sync(); }
    });
    var dragging = null;
    list.addEventListener('dragstart', function (e) { dragging = e.target.closest('li'); if (dragging) dragging.classList.add('is-dragging'); });
    list.addEventListener('dragend', function () { if (dragging) dragging.classList.remove('is-dragging'); dragging = null; sync(); });
    list.addEventListener('dragover', function (e) {
      e.preventDefault();
      var over = e.target.closest('li');
      if (!dragging || !over || over === dragging) return;
      var r = over.getBoundingClientRect();
      list.insertBefore(dragging, (e.clientX - r.left) > r.width / 2 ? over.nextSibling : over);
    });
    list.querySelectorAll('li').forEach(function (li) { li.draggable = true; });
    add.addEventListener('click', function () {
      if (!frame) {
        frame = wp.media({ title: 'Choose photos', button: { text: 'Add to project' }, library: { type: 'image' }, multiple: 'add' });
        frame.on('select', function () {
          frame.state().get('selection').toJSON().forEach(function (a) {
            if (list.querySelector('li[data-id="' + a.id + '"]')) return;
            var sized = a.sizes && (a.sizes.thumbnail || a.sizes.medium || a.sizes.full);
            list.appendChild(item(a.id, sized ? sized.url : a.url));
          });
          sync();
        });
      }
      frame.open();
    });
  });
})();
