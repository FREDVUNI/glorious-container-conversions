<?php
/** The blog index: the newest article featured, the rest listed, paginated. */
get_header();
$page = (int) get_option('gcc_page_blog');
get_template_part('template-parts/page-hero', null, ['title' => $page ? get_the_title($page) : 'Blog', 'lede' => gcc_setting('blog_lede'), 'crumbs' => ['Blog' => '']]);
$first = ! is_paged();
?>

<section class="wrap page-cover">
  <?php echo gcc_media('u-home-03', 'Container house with solar panels on the roof', 'ratio-21-9', '100vw', 'wipe', true); ?>
  <div class="cover-strip">
    <?php foreach ([['home-red-before', 'Rust-red 40ft container on a plinth before conversion'], ['insulation-01', 'Insulation between studs inside a container'], ['home-blue-04', 'Roof structure being fitted over a container'], ['office-int-02', 'Office interior looking towards the open door']] as [$n, $a]) { echo gcc_media($n, $a, 'ratio-4-3', '25vw', ''); } ?>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <?php if (! have_posts()) : ?><p class="muted">No articles yet.</p><?php endif; ?>
    <?php $n = 0; while (have_posts()) : the_post(); $n++; $cats = get_the_category(); $cover = gcc_cover(get_the_ID(), 'hero-call-centre');
        if ($first && $n === 1) : ?>
      <div class="post-featured">
        <a class="media ratio-16-9" href="<?php the_permalink(); ?>" data-reveal="wipe"><?php echo gcc_image($cover, get_the_title(), '(max-width: 960px) 100vw, 66vw', true); ?></a>
        <div class="body" data-reveal="up">
          <p class="eyebrow">Latest</p>
          <div class="meta"><span class="plate"><span class="dot"></span><b><?php echo esc_html(get_the_date('j M Y')); ?></b></span><span class="mono muted"><?php echo esc_html(gcc_read_time(get_the_ID())); ?> read</span></div>
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p><?php echo esc_html(get_the_excerpt()); ?></p>
          <a class="btn btn-sm" href="<?php the_permalink(); ?>">Read article <?php echo gcc_arrow(); ?></a>
        </div>
      </div>
      <div class="section-head" style="margin-top:clamp(56px,7vw,100px)">
        <div class="title"><p class="eyebrow" data-reveal="up">All articles</p><h2 class="split-words">More reading</h2></div>
        <div class="aside" data-reveal="up"><p class="muted">New articles are added by the team. Have a question we have not answered? <a class="text-link" href="<?php echo esc_url(gcc_page_url('contact')); ?>">Ask us</a>.</p></div>
      </div>
      <div class="post-list">
    <?php else : if ($n === 1) : ?><div class="post-list"><?php endif; ?>
      <article class="post" data-reveal="up">
        <a class="media ratio-4-3" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo gcc_image($cover, '', '(max-width: 960px) 40vw, 25vw'); ?></a>
        <div class="body">
          <div class="meta"><span class="mono"><?php echo esc_html(get_the_date('j M Y')); ?></span><span class="mono muted"><?php echo esc_html(gcc_read_time(get_the_ID())); ?> read</span><?php foreach ($cats as $c) : ?><a class="tag" href="<?php echo esc_url((string) get_category_link($c)); ?>"><?php echo esc_html($c->name); ?></a><?php endforeach; ?></div>
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p><?php echo esc_html(get_the_excerpt()); ?></p>
        </div>
        <div class="action"><a class="link" href="<?php the_permalink(); ?>">Read article <?php echo gcc_arrow(); ?></a></div>
      </article>
    <?php endif; endwhile; ?>
    <?php if ($n > 0) : ?></div><?php endif; ?>
    <?php the_posts_pagination(['mid_size' => 2, 'prev_text' => 'Newer', 'next_text' => 'Older', 'screen_reader_text' => ' ']); ?>
  </div>
</section>
<?php get_footer(); ?>
