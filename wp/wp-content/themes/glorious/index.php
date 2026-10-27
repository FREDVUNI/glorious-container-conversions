<?php
/** The fallback for anything without a more specific template: categories, authors, dates. */
get_header();
get_template_part('template-parts/page-hero', null, ['title' => wp_strip_all_tags(get_the_archive_title()), 'lede' => wp_strip_all_tags(get_the_archive_description()), 'crumbs' => ['Blog' => gcc_page_url('blog'), wp_strip_all_tags(get_the_archive_title()) => '']]);
?>
<section class="section">
  <div class="wrap">
    <?php if (! have_posts()) : ?><p class="muted">Nothing here yet.</p><?php endif; ?>
    <div class="post-list">
      <?php while (have_posts()) : the_post(); $cats = get_the_category(); ?>
        <article class="post" data-reveal="up">
          <a class="media ratio-4-3" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo gcc_image(gcc_cover(get_the_ID(), 'hero-call-centre'), '', '25vw'); ?></a>
          <div class="body">
            <div class="meta"><span class="mono"><?php echo esc_html(get_the_date('j M Y')); ?></span><span class="mono muted"><?php echo esc_html(gcc_read_time(get_the_ID())); ?> read</span><?php foreach ($cats as $c) : ?><a class="tag" href="<?php echo esc_url((string) get_category_link($c)); ?>"><?php echo esc_html($c->name); ?></a><?php endforeach; ?></div>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p><?php echo esc_html(get_the_excerpt()); ?></p>
          </div>
          <div class="action"><a class="link" href="<?php the_permalink(); ?>">Read article <?php echo gcc_arrow(); ?></a></div>
        </article>
      <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(['mid_size' => 2, 'prev_text' => 'Newer', 'next_text' => 'Older', 'screen_reader_text' => ' ']); ?>
  </div>
</section>
<?php get_footer(); ?>
