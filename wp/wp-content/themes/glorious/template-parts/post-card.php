<?php
/** An article card, for the home page and "related articles". $args: post (WP_Post), delay (ms). */
$p = $args['post'] ?? get_post();
if (! $p instanceof WP_Post) {
    return;
}
$delay = (int) ($args['delay'] ?? 0);
$cats = get_the_category($p->ID);
$cover = gcc_cover($p->ID, 'hero-call-centre');
?>
<article class="article" data-reveal="up"<?php echo $delay ? ' data-delay="' . $delay . '"' : ''; ?>>
  <a class="media ratio-3-2" href="<?php echo esc_url((string) get_permalink($p)); ?>" tabindex="-1" aria-hidden="true"><?php echo gcc_image($cover, '', '(max-width: 960px) 100vw, 33vw'); ?></a>
  <div class="meta mono"><span><?php echo esc_html(get_the_date('j M Y', $p)); ?></span><span><?php echo $cats ? esc_html($cats[0]->name) : ''; ?></span></div>
  <h3><a href="<?php echo esc_url((string) get_permalink($p)); ?>"><?php echo esc_html($p->post_title); ?></a></h3>
  <p><?php echo esc_html($p->post_excerpt !== '' ? $p->post_excerpt : wp_trim_words(wp_strip_all_tags($p->post_content), 24)); ?></p>
  <a class="link" href="<?php echo esc_url((string) get_permalink($p)); ?>">Read</a>
</article>
