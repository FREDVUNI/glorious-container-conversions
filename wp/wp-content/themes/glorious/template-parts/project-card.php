<?php
/** One card in the Work grid. $args: post (WP_Post), index (position in the JSON the lightbox reads). */
$p = $args['post'] ?? get_post();
if (! $p instanceof WP_Post) {
    return;
}
$index = (int) ($args['index'] ?? 0);
$images = gcc_project_images($p->ID);
$cover = $images[0] ?? null;
$terms = wp_get_post_terms($p->ID, 'gcc_project_category');
$term = $terms && ! is_wp_error($terms) ? $terms[0] : null;
$size = gcc_meta($p->ID, 'size', 'normal');
$n = count($images);
?>
<article class="project-card<?php echo $size === 'wide' ? ' is-wide' : ($size === 'tall' ? ' is-tall' : ''); ?>" data-cat="<?php echo esc_attr($term ? $term->slug : ''); ?>" data-index="<?php echo $index; ?>" data-reveal="up">
  <?php if (gcc_meta($p->ID, 'ref') !== '') : ?><span class="plate"><span class="dot"></span><b><?php echo esc_html(gcc_meta($p->ID, 'ref')); ?></b></span><?php endif; ?>
  <a class="project-card-link" href="<?php echo esc_url((string) get_permalink($p)); ?>" data-project-open aria-label="Open project: <?php echo esc_attr($p->post_title); ?>">
    <span class="media"><?php echo $cover ? gcc_image($cover['src'], $cover['alt'] ?: $p->post_title, '(max-width: 640px) 100vw, (max-width: 960px) 50vw, ' . ($size === 'wide' ? '66vw' : '33vw')) : ''; ?></span>
    <span class="meta"><h3><?php echo esc_html($p->post_title); ?></h3><span class="cat"><?php echo $term ? esc_html($term->name) : ''; ?></span><span class="count"><?php echo $n; ?> photo<?php echo $n === 1 ? '' : 's'; ?></span></span>
  </a>
</article>
