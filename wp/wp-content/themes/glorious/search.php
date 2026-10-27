<?php get_header();
get_template_part('template-parts/page-hero', null, ['title' => 'Search', 'lede' => have_posts() ? 'Results for “' . get_search_query() . '”.' : 'Nothing found for “' . get_search_query() . '”.', 'crumbs' => ['Search' => '']]);
?>
<section class="section">
  <div class="wrap">
    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search" style="max-width:480px;margin-bottom:40px">
      <?php echo gcc_icon('search'); ?><label class="visually-hidden" for="s">Search</label><input type="search" id="s" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Search…">
    </form>
    <div class="post-list">
      <?php while (have_posts()) : the_post(); ?>
        <article class="post" data-reveal="up">
          <a class="media ratio-4-3" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo gcc_image(gcc_cover(get_the_ID(), 'hero-call-centre'), '', '25vw'); ?></a>
          <div class="body"><div class="meta"><span class="mono"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name ?? ''); ?></span></div><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(get_the_excerpt()); ?></p></div>
          <div class="action"><a class="link" href="<?php the_permalink(); ?>">Open <?php echo gcc_arrow(); ?></a></div>
        </article>
      <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(['mid_size' => 2, 'prev_text' => 'Previous', 'next_text' => 'Next', 'screen_reader_text' => ' ']); ?>
  </div>
</section>
<?php get_footer(); ?>
