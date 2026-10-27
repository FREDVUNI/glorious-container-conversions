/* projects.js — project data, filterable gallery grid, and lightbox modal.
   The data below is the content model for the later CMS phase: one record per
   project, with category, images (base names in assets/images/), summary,
   specs and features. Nothing here is fetched; it renders from this array. */
(function () {
  'use strict';
  const grid = document.getElementById('projects-grid');
  if (!grid) return;

  // Image path prefix is computed from the page so the same script works from
  // /index.html and /pages/*.html.
  const IMG = grid.getAttribute('data-img-base') || '../assets/images/';
  const img = (name, w) => IMG + name + '-' + w + '.webp';
  const srcset = (name, hasLarge) => hasLarge ? img(name, 800) + ' 800w, ' + img(name, 1600) + ' 1600w' : img(name, 800) + ' 800w';

  const CATEGORIES = {
    healthcare: 'Healthcare',
    workspaces: 'Offices & workspaces',
    homes: 'Homes',
    'cold-storage': 'Cold storage',
    ablution: 'Ablution',
    shops: 'Shops & kiosks',
    fabrication: 'Fabrication & delivery',
  };

  /* Each image: [baseName, hasLargeVariant, altText]. */
  const PROJECTS = [
    {
      id: 'container-hospital', ref: 'GCC-24-05-H',
      title: 'Shipping Container Hospital', cat: 'healthcare', size: 'wide',
      summary: 'A two-storey "hospitainer" block built from stacked containers: self-contained clinical rooms with their own ventilation, lighting and power connection, designed to be set up quickly and relocated when a community’s needs change.',
      specs: { 'Type': 'Two-storey stacked block', 'Use': 'Medical practice, outreach clinics', 'Power': 'Grid, mobile generator, or both', 'Published': 'May 2024' },
      features: ['Stackable and scalable in many configurations', 'Dedicated AC, ventilation and lighting per unit', 'Roof, insulation, external wall facing, floors, windows and partitions included', 'Walkway and balcony access to upper units', 'Stored and reused for future deployment'],
      images: [
        ['hospital-01', false, 'Two-storey blue container hospital block with upper walkway and green trim'],
        ['hospital-02', false, 'Front elevation of the stacked container hospital, Glorious Container banner on the facade'],
        ['hospital-03', false, 'Ground-floor walkway along the container units'],
        ['hospital-04', false, 'Corridor of doors along the container clinic'],
        ['hospital-05', false, 'Open door into a fitted consultation room'],
        ['hospital-06', true, 'Wide view of the two-storey container block'],
      ],
    },
    {
      id: 'call-centre-studio', ref: 'GCC-24-05-C',
      title: '40ft Call Centre & Recording Studio', cat: 'workspaces', size: 'tall',
      summary: 'A single 40ft high-cube unit delivered ready for immediate use: a manager’s office at one end, open-plan desks in the middle, a kitchenette, and a soundproofed recording and editing studio at the far end.',
      specs: { 'Container': '1 × 40ft high-cube', 'Use': 'Call centre, sound studio', 'Exterior': 'Half timber cladding, modified paint', 'Flooring': 'Interlocking wood and SPC' },
      features: ['Wall and ceiling insulation throughout', 'Windows with mosquito nets, personnel doors', 'Air conditioning and off-grid ventilation', 'Network and electrical fit-out, MDF desktops', 'Soundproofed studio room'],
      images: [
        ['hero-call-centre', true, 'White and green 40ft container office with timber panels and a pitched canopy roof'],
        ['callcentre-02', false, 'Front of the call centre unit set in a garden'],
        ['callcentre-06', true, 'Side elevation showing half timber cladding and windows'],
        ['callcentre-03', false, 'Entrance door of the call centre container'],
        ['callcentre-04', false, 'External air-conditioning unit mounted on the container wall'],
        ['callcentre-05', false, 'Close-up of the cladding and canopy detail'],
      ],
    },
    {
      id: 'cold-storage', ref: 'GCC-24-05-R',
      title: 'Cool Room & Refrigerated Cold Storage', cat: 'cold-storage', size: 'normal',
      summary: 'Refrigerated container units with thermostatic control, inspected before delivery and commissioned on site by a qualified refrigeration mechanic. Suitable for food, flowers, drinks and pharmaceutical storage.',
      specs: { 'Type': 'Insulated refrigerated unit', 'Control': 'Thermostatic, optional digital display and alarms', 'Support': '24/7 breakdown service within ~50 km of Kampala', 'Published': 'May 2024' },
      features: ['Pre-delivery inspection of every unit', 'Single-door access so the cargo doors stay sealed', 'Optional online management app', 'Installation by a qualified refrigeration mechanic'],
      images: [
        ['coldroom-01', true, 'White insulated cold-room container in a yard'],
        ['coldroom-02', false, 'Cold-room unit with single access door, front view'],
        ['coldroom-03', false, 'Cold-room unit seen from the side'],
        ['coldroom-04', false, 'Technician fitting the refrigeration unit inside'],
        ['coldroom-05', false, 'Cold-room container beside a rust-red storage unit'],
      ],
    },
    {
      id: 'sliding-doors', ref: 'GCC-24-05-D',
      title: 'Internal Glass Sliding Doors', cat: 'healthcare', size: 'normal',
      summary: 'Fit-out detail from a hospitainer build: an internal glass sliding door and large windows bring daylight deep into the unit, cut electricity use and make the most of a container’s 8ft structural width.',
      specs: { 'Type': 'Interior fit-out detail', 'Width': '8ft structural width', 'Use': 'Portable clinic', 'Published': 'May 2024' },
      features: ['Internal glass sliding door', 'Large windows for natural daylight', 'Space-efficient layout', 'Portable and easily moved'],
      images: [
        ['sliding-01', true, 'Plywood-lined container interior with a steel-framed sliding door track'],
        ['sliding-02', false, 'Sliding door frame during fit-out, portrait view'],
        ['sliding-03', false, 'Detail of the sliding door runner and ply lining'],
      ],
    },
    {
      id: 'home-red-40ft', ref: 'GCC-RES-01',
      title: '40ft Home with Pitched Roof', cat: 'homes', size: 'wide',
      summary: 'Before and after: a used 40ft container set on a masonry plinth, then cut for windows and a door and finished with an iron-sheet pitched roof for shade and rain protection.',
      specs: { 'Container': '1 × 40ft', 'Roof': 'Pitched iron-sheet roof', 'Base': 'Masonry plinth', 'Status': 'Client to confirm details' },
      features: ['Window and door openings cut and framed', 'Pitched roof reduces heat gain and sheds rain', 'Raised plinth keeps the floor dry', 'Retains original corten-red finish'],
      images: [
        ['home-red-before', true, 'Before: a rust-red 40ft container placed on a stone plinth, no openings yet'],
        ['home-red-after', true, 'After: the same container with windows, a door and a new pitched roof'],
      ],
    },
    {
      id: 'home-blue', ref: 'GCC-RES-02',
      title: 'Blue Container Residence', cat: 'homes', size: 'normal',
      summary: 'A single-storey container home in bright blue, with white-framed windows, a planted garden and a pitched roof added over the unit during the build.',
      specs: { 'Type': 'Single-storey home', 'Finish': 'Blue exterior, white joinery', 'Roof': 'Added pitched roof', 'Status': 'Client to confirm details' },
      features: ['Multiple windows and personnel doors', 'Roof structure fitted on site', 'Garden landscaping around the unit'],
      images: [
        ['home-blue-01', true, 'Blue container home with white windows and a garden hedge'],
        ['home-blue-02', false, 'Blue container home on open ground'],
        ['home-blue-03', false, 'Blue container with open doors during finishing'],
        ['home-blue-04', false, 'Roof structure being fitted over the blue container'],
      ],
    },
    {
      id: 'home-grey', ref: 'GCC-RES-03',
      title: 'Grey Home with Verandah Roof', cat: 'homes', size: 'normal',
      summary: 'A compact grey container home with a full pitched roof extending into a small verandah, security-grilled windows and an external AC unit.',
      specs: { 'Type': 'Single-unit home', 'Roof': 'Pitched roof with verandah', 'Finish': 'Grey exterior', 'Status': 'Client to confirm details' },
      features: ['Pitched roof with verandah overhang', 'Security grilles on windows', 'Air conditioning fitted'],
      images: [['home-grey-01', false, 'Grey container home with a pitched roof and small verandah']],
    },
    {
      id: 'home-green', ref: 'GCC-RES-04',
      title: 'Green Residence with Canopy', cat: 'homes', size: 'normal',
      summary: 'A green container residence under a steel canopy roof, with a timber garden store and planted surroundings.',
      specs: { 'Type': 'Residential unit', 'Roof': 'Free-standing steel canopy', 'Finish': 'Green exterior', 'Status': 'Client to confirm details' },
      features: ['Steel canopy roof over the unit', 'Timber outbuilding', 'Water storage tank'],
      images: [
        ['home-green-01', false, 'Green container residence under a steel canopy, viewed across the garden'],
        ['home-green-02', false, 'Green container residence with timber garden store in the foreground'],
      ],
    },
    {
      id: 'office-interiors', ref: 'GCC-OFF-01',
      title: 'Fitted Office Interiors', cat: 'workspaces', size: 'normal',
      summary: 'Inside finished container offices: lined and painted walls, timber floors, cove lighting, air conditioning, and partitioned workstations ready for desks and equipment.',
      specs: { 'Type': 'Office fit-out', 'Lining': 'Ply or melamine, insulated', 'Services': 'Electrical, AC, lighting' },
      features: ['Insulated and lined interiors', 'Timber-effect flooring', 'Partitioned workstations', 'Cove lighting and AC'],
      images: [
        ['office-int-01', false, 'Finished office interior with desks, AC and cove lighting'],
        ['office-int-02', false, 'Office interior looking towards the open door'],
        ['office-int-03', false, 'Boardroom table and chairs inside a container office'],
        ['office-int-04', false, 'Yellow-painted interior with partitioned workstations'],
      ],
    },
    {
      id: 'ablution-unit', ref: 'GCC-ABL-01',
      title: 'Ablution Unit', cat: 'ablution', size: 'normal',
      summary: 'Portable toilet and shower cabins built from containers, from single WCs to fully fitted welfare blocks with water heater, hand basin and timber-framed doors.',
      specs: { 'Type': 'Ablution block', 'Fittings': 'WC, basin, shower, water heater', 'Use': 'Sites, events, visitor attractions' },
      features: ['Water-supply connection and heater', 'Shower cubicle option', 'Disabled-access option', 'Hygienic wipe-down finishes'],
      images: [
        ['ablution-03', false, 'Two toilet cubicles with timber-framed doors inside a container'],
        ['ablution-01', false, 'Toilet cubicle with wall-mounted water heater'],
        ['ablution-02', false, 'Corner hand basin inside the ablution unit'],
      ],
    },
    {
      id: 'fabrication', ref: 'GCC-YARD',
      title: 'Stacking, Insulation & Delivery', cat: 'fabrication', size: 'tall',
      summary: 'Behind the scenes at the Bugolobi yard and on site: units craned into position, 40ft boxes stacked two high, insulation fitted between studs, and exteriors prepared for branding.',
      specs: { 'Where': 'Bugolobi Industrial Area, Kampala', 'Transport': 'In-house crane and haulage', 'Insulation': 'Fibre batts between timber studs' },
      features: ['Crane placement on prepared bases', 'Two-high stacking with corner locks', 'Insulation and first-fix electrics', 'Exterior branding and paint'],
      images: [
        ['stack-01', true, 'Two containers stacked on top of a yellow container, crane cable visible'],
        ['crane-01', false, 'Mobile crane lifting a container off a truck on site'],
        ['insulation-01', false, 'Insulation batts fitted between timber studs inside a container'],
        ['branding-01', true, 'Worker applying vinyl branding to a blue container wall'],
        ['storage-01', false, 'Row of beige storage containers with open doors'],
      ],
    },
    {
      id: 'timber-kiosk', ref: 'GCC-SHP-01',
      title: 'Timber-Clad Kiosk', cat: 'shops', size: 'normal',
      summary: 'A 20ft unit wrapped in horizontal timber cladding with a lift-up serving hatch and counter, suited to coffee, food and drink service at events and roadside pitches.',
      specs: { 'Container': '20ft', 'Exterior': 'Timber cladding', 'Opening': 'Lift-up hatch with counter', 'Status': 'Client to confirm details' },
      features: ['Full-width serving hatch', 'External counter', 'Lattice screen for shade', 'Sliding window on the rear elevation'],
      images: [
        ['kiosk-01', false, 'Timber-clad container kiosk with lift-up hatch and serving counter'],
        ['kiosk-02', false, 'Kiosk with a timber lattice screen to one side'],
        ['kiosk-03', false, 'Rear of the timber-clad kiosk with sliding window'],
      ],
    },
    {
      id: 'red-20ft', ref: 'GCC-SHP-02',
      title: 'Compact 20ft Unit', cat: 'shops', size: 'normal',
      summary: 'A 20ft container in high-gloss red with a cut window and a side door, delivered as a compact shop or site office.',
      specs: { 'Container': '20ft', 'Finish': 'Gloss red', 'Openings': 'Window and side door', 'Status': 'Client to confirm details' },
      features: ['Gloss exterior paint', 'Framed window and door', 'Set on blocks for drainage'],
      images: [['unit-red-01', false, 'Gloss red 20ft container with a window and side door']],
    },
  ];

  /* ---------- Render grid ------------------------------------------- */
  const cards = [];
  PROJECTS.forEach((p, i) => {
    const cover = p.images[0];
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'project-card' + (p.size === 'wide' ? ' is-wide' : p.size === 'tall' ? ' is-tall' : '');
    btn.setAttribute('data-cat', p.cat);
    btn.setAttribute('data-index', i);
    btn.setAttribute('data-reveal', 'up');
    btn.setAttribute('aria-haspopup', 'dialog');
    btn.setAttribute('aria-label', 'Open project: ' + p.title);
    btn.innerHTML =
      '<span class="plate"><span class="dot"></span><b>' + p.ref + '</b></span>' +
      '<span class="media"><img src="' + img(cover[0], 800) + '" srcset="' + srcset(cover[0], cover[1]) + '" sizes="(max-width: 640px) 100vw, (max-width: 960px) 50vw, ' + (p.size === 'wide' ? '66vw' : '33vw') + '" alt="' + cover[2] + '" loading="lazy" decoding="async"></span>' +
      '<span class="meta"><h3>' + p.title + '</h3><span class="cat">' + CATEGORIES[p.cat] + '</span><span class="count">' + p.images.length + ' photo' + (p.images.length > 1 ? 's' : '') + '</span></span>';
    btn.addEventListener('click', () => openModal(i));
    grid.appendChild(btn);
    cards.push(btn);
  });

  // Image fade-in for generated cards (main.js ran before these existed).
  grid.querySelectorAll('.media img').forEach((im) => {
    const wrap = im.closest('.media'); const done = () => wrap.classList.add('is-loaded');
    if (im.complete && im.naturalWidth) done(); else { im.addEventListener('load', done, { once: true }); im.addEventListener('error', done, { once: true }); }
  });
  // Reveal observer for generated cards.
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if ('IntersectionObserver' in window && !reduceMotion) {
    const io = new IntersectionObserver((es) => es.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } }), { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
    cards.forEach((c) => io.observe(c));
  } else cards.forEach((c) => c.classList.add('is-in'));

  /* ---------- Filters + search --------------------------------------- */
  const filterBtns = document.querySelectorAll('[data-filter]');
  const search = document.getElementById('project-search');
  const count = document.getElementById('result-count');
  const empty = document.getElementById('projects-empty');
  const emptyReset = document.getElementById('projects-empty-reset');
  let activeCat = 'all', query = '';

  // Populate counts in filter buttons
  filterBtns.forEach((b) => {
    const c = b.getAttribute('data-filter');
    const n = c === 'all' ? PROJECTS.length : PROJECTS.filter((p) => p.cat === c).length;
    const small = b.querySelector('small'); if (small) small.textContent = n;
  });

  function applyFilters() {
    const q = query.trim().toLowerCase();
    let shown = 0;
    cards.forEach((card, i) => {
      const p = PROJECTS[i];
      const matchCat = activeCat === 'all' || p.cat === activeCat;
      const hay = (p.title + ' ' + p.summary + ' ' + CATEGORIES[p.cat] + ' ' + Object.values(p.specs).join(' ') + ' ' + p.features.join(' ')).toLowerCase();
      const matchQ = !q || hay.includes(q);
      const show = matchCat && matchQ;
      card.classList.toggle('is-hidden', !show);
      if (show) { shown++; card.classList.add('is-in'); }
    });
    if (count) count.textContent = shown + ' of ' + PROJECTS.length + ' projects' + (activeCat !== 'all' ? ' · ' + CATEGORIES[activeCat] : '') + (q ? ' · “' + query.trim() + '”' : '');
    if (empty) empty.hidden = shown !== 0;
  }
  filterBtns.forEach((b) => b.addEventListener('click', () => {
    filterBtns.forEach((x) => x.setAttribute('aria-pressed', 'false'));
    b.setAttribute('aria-pressed', 'true');
    activeCat = b.getAttribute('data-filter');
    history.replaceState(null, '', activeCat === 'all' ? location.pathname : '#' + activeCat);
    applyFilters();
  }));
  if (search) search.addEventListener('input', () => { query = search.value; applyFilters(); });
  if (emptyReset) emptyReset.addEventListener('click', () => {
    query = ''; if (search) search.value = '';
    activeCat = 'all'; filterBtns.forEach((x) => x.setAttribute('aria-pressed', x.getAttribute('data-filter') === 'all' ? 'true' : 'false'));
    applyFilters();
  });
  // Deep link: /projects.html#homes
  const hash = location.hash.replace('#', '');
  if (hash && CATEGORIES[hash]) {
    const b = document.querySelector('[data-filter="' + hash + '"]'); if (b) b.click();
  } else applyFilters();

  /* ---------- Modal / lightbox --------------------------------------- */
  const modal = document.getElementById('project-modal');
  if (!modal) return;
  const stage = modal.querySelector('.stage-media');
  const thumbs = modal.querySelector('.thumbs');
  const counter = modal.querySelector('.counter');
  const title = modal.querySelector('[data-m-title]');
  const cat = modal.querySelector('[data-m-cat]');
  const ref = modal.querySelector('[data-m-ref]');
  const summary = modal.querySelector('[data-m-summary]');
  const specs = modal.querySelector('[data-m-specs]');
  const features = modal.querySelector('[data-m-features]');
  const note = modal.querySelector('[data-m-note]');
  const prevImg = modal.querySelector('[data-prev-img]');
  const nextImg = modal.querySelector('[data-next-img]');
  const prevProj = modal.querySelector('[data-prev-project]');
  const nextProj = modal.querySelector('[data-next-project]');
  const closeBtns = modal.querySelectorAll('[data-close]');
  let current = 0, slide = 0, lastFocus = null;

  function renderProject(i) {
    current = (i + PROJECTS.length) % PROJECTS.length; slide = 0;
    const p = PROJECTS[current];
    title.textContent = p.title; cat.textContent = CATEGORIES[p.cat]; ref.textContent = p.ref;
    summary.textContent = p.summary;
    specs.innerHTML = Object.keys(p.specs).map((k) => '<dt>' + k + '</dt><dd>' + p.specs[k] + '</dd>').join('');
    features.innerHTML = p.features.map((f) => '<li>' + f + '</li>').join('');
    const needsConfirm = Object.values(p.specs).some((v) => /confirm/i.test(v));
    note.hidden = !needsConfirm;
    stage.innerHTML = p.images.map((im, n) =>
      '<figure' + (n ? ' hidden' : '') + '><span class="media"><img src="' + img(im[0], im[1] ? 1600 : 800) + '" alt="' + im[2] + '" loading="' + (n ? 'lazy' : 'eager') + '" decoding="async"></span></figure>'
    ).join('');
    stage.querySelectorAll('.media img').forEach((im) => {
      const wrap = im.closest('.media'); const done = () => wrap.classList.add('is-loaded');
      if (im.complete && im.naturalWidth) done(); else { im.addEventListener('load', done, { once: true }); im.addEventListener('error', done, { once: true }); }
    });
    thumbs.innerHTML = p.images.map((im, n) =>
      '<button type="button" aria-label="Show photo ' + (n + 1) + '" aria-current="' + (n === 0) + '"><img src="' + img(im[0], 800) + '" alt="" loading="lazy"></button>'
    ).join('');
    thumbs.querySelectorAll('button').forEach((b, n) => b.addEventListener('click', () => showSlide(n)));
    prevProj.querySelector('span').textContent = PROJECTS[(current - 1 + PROJECTS.length) % PROJECTS.length].title;
    nextProj.querySelector('span').textContent = PROJECTS[(current + 1) % PROJECTS.length].title;
    updateCounter();
    modal.querySelector('.modal-side-body').scrollTop = 0;
  }
  function updateCounter() {
    const p = PROJECTS[current];
    counter.textContent = String(slide + 1).padStart(2, '0') + ' / ' + String(p.images.length).padStart(2, '0');
    prevImg.disabled = nextImg.disabled = p.images.length < 2;
  }
  function showSlide(n) {
    const p = PROJECTS[current];
    slide = (n + p.images.length) % p.images.length;
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
  prevImg.addEventListener('click', () => showSlide(slide - 1));
  nextImg.addEventListener('click', () => showSlide(slide + 1));
  prevProj.addEventListener('click', () => renderProject(current - 1));
  nextProj.addEventListener('click', () => renderProject(current + 1));
  closeBtns.forEach((b) => b.addEventListener('click', closeModal));
  // Click on the dark stage backdrop (not the image) closes.
  stage.addEventListener('click', (e) => { if (e.target === stage || e.target.tagName === 'FIGURE' || e.target.classList.contains('media')) closeModal(); });
  // Basic swipe on the stage for touch devices.
  let tx = null;
  stage.addEventListener('touchstart', (e) => { tx = e.touches[0].clientX; }, { passive: true });
  stage.addEventListener('touchend', (e) => {
    if (tx === null) return; const dx = e.changedTouches[0].clientX - tx; tx = null;
    if (Math.abs(dx) > 50) showSlide(slide + (dx < 0 ? 1 : -1));
  });
  modal.setAttribute('aria-hidden', 'true');

  // Expose for deep links like projects.html#project=container-hospital
  const m = location.hash.match(/project=([\w-]+)/);
  if (m) { const idx = PROJECTS.findIndex((p) => p.id === m[1]); if (idx > -1) openModal(idx); }
})();
