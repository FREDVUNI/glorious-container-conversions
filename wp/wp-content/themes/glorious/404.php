<?php get_header();
get_template_part('template-parts/page-hero', null, ['title' => 'Nothing at this address', 'lede' => 'The page may have moved, or the link had a typo in it.', 'crumbs' => ['404' => '']]);
?>
<section class="section">
  <div class="wrap">
    <div class="empty-state" data-reveal="up">
      <div class="box" aria-hidden="true"><i></i><i></i><i></i></div>
      <p class="h3">Try one of these instead</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="btn btn-sm" href="<?php echo esc_url(home_url('/')); ?>">Home</a>
        <a class="btn btn-sm btn-ghost" href="<?php echo esc_url(gcc_work_url()); ?>">Our work</a>
        <a class="btn btn-sm btn-ghost" href="<?php echo esc_url(gcc_page_url('contact')); ?>">Contact</a>
      </div>
    </div>
  </div>
</section>
<?php get_footer(); ?>
