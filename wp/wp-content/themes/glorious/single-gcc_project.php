<?php
/** One project's own page: the one that gets shared on WhatsApp. */
get_header(); the_post();
$id = get_the_ID();
$images = gcc_project_images($id);
$specs = gcc_meta_pairs($id, 'specs');
$features = gcc_meta_lines($id, 'features');
$terms = wp_get_post_terms($id, 'gcc_project_category');
$term = $terms && ! is_wp_error($terms) ? $terms[0] : null;
$ref = gcc_meta($id, 'ref');
$plate = $ref !== '' ? '<div class="article-meta" data-reveal="up"><span class="plate"><span class="dot"></span><b>' . esc_html($ref) . '</b></span>' . ($term ? '<span class="mono">' . esc_html($term->name) . '</span>' : '') . '<span class="mono">' . count($images) . ' photo' . (count($images) === 1 ? '' : 's') . '</span></div>' : '';
get_template_part('template-parts/page-hero', null, ['title' => get_the_title(), 'lede' => get_the_excerpt(), 'crumbs' => ['Work' => gcc_work_url(), get_the_title() => ''], 'extra' => $plate]);
?>

<?php if ($images) : ?>
<section class="wrap page-cover">
  <div class="project-gallery">
    <?php foreach ($images as $i => $im) : ?>
      <figure><?php echo gcc_media($im['src'], $im['alt'] ?: get_the_title(), 'ratio-4-3', $i === 0 ? '100vw' : '(max-width: 640px) 100vw, 33vw', $i === 0 ? 'wipe' : 'up', $i === 0); ?></figure>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="wrap project-layout">
    <div class="project-body">
      <?php if (get_the_content() !== '') : ?><div class="prose" data-reveal="up"><?php the_content(); ?></div><?php endif; ?>
      <?php if ($features) : ?>
        <h2 class="h3 mt-lg" data-reveal="up">What went into it</h2>
        <ul class="project-side features mt-md" data-reveal="up"><?php foreach ($features as $f) : ?><li><?php echo esc_html($f); ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if (gcc_meta($id, 'confirm') === 'yes') : ?><p class="project-side note mt-lg">Details for this entry were written from photographs and are being confirmed.</p><?php endif; ?>
    </div>
    <aside class="project-side" data-reveal="up">
      <?php if ($specs) : ?><dl><?php foreach ($specs as $k => $v) : ?><dt><?php echo esc_html($k); ?></dt><dd><?php echo esc_html($v); ?></dd><?php endforeach; ?></dl><?php endif; ?>
      <div style="display:grid;gap:10px;justify-items:start">
        <a class="btn btn-yellow" href="<?php echo esc_url(gcc_quote_url('', $id)); ?>">Quote a build like this <?php echo gcc_arrow(); ?></a>
        <a class="link" href="<?php echo esc_url(gcc_work_url()); ?>">All projects</a>
      </div>
    </aside>
  </div>
</section>

<?php $related = $term ? get_posts(['post_type' => 'gcc_project', 'numberposts' => 3, 'exclude' => [$id], 'tax_query' => [['taxonomy' => 'gcc_project_category', 'field' => 'term_id', 'terms' => $term->term_id]]]) : [];
if (count($related) < 3) { $related = array_merge($related, array_slice(array_filter(gcc_projects(6), static fn ($p) => $p->ID !== $id && ! in_array($p, $related, true)), 0, 3 - count($related))); }
if ($related) : ?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="section-head"><div class="title"><p class="eyebrow" data-reveal="up">More work</p><h2 class="split-words">Related projects</h2></div></div>
    <div class="projects-grid" style="padding-top:0">
      <?php foreach ($related as $i => $p) { get_template_part('template-parts/project-card', null, ['post' => $p, 'index' => $i]); } ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php get_footer(); ?>
