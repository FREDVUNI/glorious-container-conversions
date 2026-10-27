<?php
/**
 * The Work page: every published project, filterable by category, with the
 * lightbox. Also serves /work/category/<slug>/ (see taxonomy-gcc_project_category.php).
 * Cards are rendered here so the page reads without JavaScript; projects.js
 * adds the filters, search and lightbox on top.
 */
get_header();
$current = is_tax('gcc_project_category') ? get_queried_object() : null;
$projects = gcc_projects();
$terms = get_terms(['taxonomy' => 'gcc_project_category', 'hide_empty' => true]);
get_template_part('template-parts/page-hero', null, ['title' => 'Built, delivered, in use', 'lede' => gcc_setting('work_lede'), 'crumbs' => $current ? ['Work' => gcc_work_url(), $current->name => ''] : ['Work' => '']]);
?>

<section class="wrap page-cover">
  <div class="cover-strip cover-strip-5">
    <?php foreach ([['hospital-04', 'Corridor of doors along the container clinic'], ['callcentre-05', 'Cladding and canopy detail on the call centre unit'], ['coldroom-02', 'Cold-room unit with single access door'], ['home-blue-03', 'Blue container with open doors during finishing'], ['kiosk-03', 'Rear of the timber-clad kiosk']] as [$n, $a]) { echo gcc_media($n, $a, 'ratio-4-3', '20vw', ''); } ?>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="filter-bar">
      <div class="filters" role="group" aria-label="Filter projects by category">
        <a class="filter" href="<?php echo esc_url(gcc_work_url()); ?>" data-filter="all" aria-pressed="<?php echo $current ? 'false' : 'true'; ?>">All <small><?php echo count($projects); ?></small></a>
        <?php foreach ($terms as $t) : ?>
          <a class="filter" href="<?php echo esc_url((string) get_term_link($t)); ?>" data-filter="<?php echo esc_attr($t->slug); ?>" aria-pressed="<?php echo $current && $current->term_id === $t->term_id ? 'true' : 'false'; ?>"><?php echo esc_html($t->name); ?> <small><?php echo (int) $t->count; ?></small></a>
        <?php endforeach; ?>
      </div>
      <label class="search">
        <?php echo gcc_icon('search'); ?>
        <span class="visually-hidden">Search projects</span>
        <input type="search" id="project-search" placeholder="Search: roof, 40ft, solar…" autocomplete="off">
      </label>
      <p class="result-count" id="result-count" aria-live="polite"></p>
    </div>

    <div class="projects-grid" id="projects-grid" data-initial="<?php echo esc_attr($current ? $current->slug : 'all'); ?>">
      <?php foreach ($projects as $i => $p) { get_template_part('template-parts/project-card', null, ['post' => $p, 'index' => $i]); } ?>
      <div class="empty-state" id="projects-empty" hidden>
        <div class="box" aria-hidden="true"><i></i><i></i><i></i></div>
        <p class="h3">No projects match that yet</p>
        <p class="muted">Try another word, or clear the filters. If you are planning something we have not built before, we would still like to hear about it.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap"><button class="btn btn-sm" type="button" id="projects-empty-reset">Clear filters</button><a class="btn btn-sm btn-ghost" href="<?php echo esc_url(gcc_quote_url()); ?>">Describe your project</a></div>
      </div>
      <?php if (! $projects) : ?><p class="muted" style="grid-column:1/13">No projects published yet. Add them under GCC → Projects.</p><?php endif; ?>
    </div>
  </div>
</section>

<div class="modal" id="project-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="modal-inner">
    <div class="modal-stage">
      <div class="stage-media"></div>
      <div class="modal-controls">
        <div style="display:flex;gap:8px;align-items:center">
          <button class="icon-btn" type="button" data-prev-img aria-label="Previous photo"><?php echo gcc_icon('arrow-l'); ?></button>
          <button class="icon-btn" type="button" data-next-img aria-label="Next photo"><?php echo gcc_arrow(); ?></button>
          <span class="counter" aria-live="polite">01 / 01</span>
        </div>
        <div class="thumbs" aria-label="Photo thumbnails"></div>
      </div>
    </div>
    <aside class="modal-side">
      <div class="modal-side-head">
        <span class="plate"><span class="dot"></span><b data-m-ref>GCC</b></span>
        <button class="icon-btn" type="button" data-close aria-label="Close project"><?php echo gcc_icon('close'); ?></button>
      </div>
      <div class="modal-side-body">
        <span class="eyebrow cat" data-m-cat></span>
        <h2 id="modal-title" data-m-title></h2>
        <p data-m-summary></p>
        <dl data-m-specs></dl>
        <ul class="features" data-m-features></ul>
        <p class="note" data-m-note hidden>Client review: project details for this entry are taken from photographs and need confirmation before launch.</p>
        <div class="modal-actions" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:26px"><a class="btn btn-yellow btn-sm" data-m-quote href="#">Quote a build like this</a><a class="btn btn-ghost btn-sm" data-m-link href="#">Project page</a></div>
        <div class="nav-projects">
          <button type="button" data-prev-project><?php echo gcc_icon('arrow-l'); ?><span></span></button>
          <button type="button" data-next-project><span></span><?php echo gcc_arrow(); ?></button>
        </div>
      </div>
    </aside>
  </div>
</div>
<script id="gcc-projects-data" type="application/json"><?php echo gcc_project_json($projects); ?></script>
<?php get_footer(); ?>
