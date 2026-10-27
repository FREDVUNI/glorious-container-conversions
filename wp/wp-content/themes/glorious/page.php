<?php /** Any other page: title, intro, the editor's text. */ get_header(); the_post();
get_template_part('template-parts/page-hero', null, ['title' => get_the_title(), 'lede' => has_excerpt() ? get_the_excerpt() : '', 'crumbs' => [get_the_title() => '']]);
?>
<section class="section">
  <div class="wrap article-layout" style="padding-top:0">
    <div class="prose" style="grid-column:3/11"><?php the_content(); ?></div>
  </div>
</section>
<?php get_footer(); ?>
