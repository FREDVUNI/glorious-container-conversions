<?php /* Template Name: About */ get_header(); the_post();
get_template_part('template-parts/page-hero', null, ['title' => get_the_title(), 'lede' => has_excerpt() ? get_the_excerpt() : gcc_setting('about_lede'), 'crumbs' => ['About' => '']]);
?>

<section class="wrap page-cover">
  <?php echo has_post_thumbnail() ? gcc_media((int) get_post_thumbnail_id(), get_the_title(), 'ratio-21-9', '100vw', 'wipe', true) : gcc_media('u-port-01', 'Container yard with a truck collecting a unit under a blue sky', 'ratio-21-9', '100vw', 'wipe', true); ?>
  <div class="cover-strip">
    <?php foreach ([['office-int-01', 'Finished container office interior'], ['home-green-02', 'Green container residence with timber store'], ['kiosk-02', 'Timber-clad kiosk with lattice screen'], ['ablution-01', 'Toilet cubicle inside an ablution unit']] as [$n, $a]) { echo gcc_media($n, $a, 'ratio-4-3', '25vw', ''); } ?>
  </div>
</section>

<section class="section">
  <div class="wrap about-intro">
    <div class="copy">
      <p class="eyebrow" data-reveal="up">The company</p>
      <h2 class="split-words" style="margin:18px 0 28px"><?php echo esc_html(gcc_setting('about_heading')); ?></h2>
      <div data-reveal="up" class="prose-page"><?php the_content(); ?></div>
    </div>
    <div class="media-stack">
      <?php echo gcc_media('branding-01', 'Worker applying Glorious Container branding to a freshly painted blue unit', 'ratio-4-3', '(max-width: 960px) 100vw, 40vw'); ?>
      <?php echo gcc_media('stack-01', 'Containers being stacked two high with a crane', 'ratio-3-4', '(max-width: 960px) 60vw, 26vw', 'wipe', false, 'data-delay="200"'); ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="mv-media" data-reveal="wipe"><?php echo gcc_media('hospital-06', 'Wide view of the two-storey blue container hospital block', 'ratio-21-9', '100vw', ''); ?><?php echo gcc_caption('caption_about_wide'); ?></div>
    <div class="mv">
      <div class="mv-card" data-reveal="up"><p class="eyebrow">Mission</p><p><?php echo esc_html(gcc_setting('mission')); ?></p></div>
      <div class="mv-card" data-reveal="up" data-delay="120"><p class="eyebrow">Vision</p><p><?php echo esc_html(gcc_setting('vision')); ?></p></div>
    </div>
  </div>
</section>

<?php $values = gcc_pairs('commitments'); if ($values) : ?>
<section class="section dark corrugated" aria-labelledby="values-title">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">How we work</p><h2 id="values-title" class="split-words"><?php echo count($values) === 5 ? 'Five commitments' : 'Our commitments'; ?></h2></div>
      <div class="aside" data-reveal="up"><p class="muted"><?php echo esc_html(gcc_setting('commitments_intro')); ?></p></div>
    </div>
    <div class="values-media">
      <?php echo gcc_media('u-weld-02', 'Welder in protective gear welding a steel drum, sparks flying', 'ratio-16-9', '(max-width: 960px) 100vw, 58vw'); ?>
      <?php echo gcc_media('hospital-03', 'Ground-floor walkway along finished container clinic units', 'ratio-4-3', '(max-width: 960px) 100vw, 40vw', 'wipe', false, 'data-delay="150"'); ?>
    </div>
    <ul class="values">
      <?php foreach ($values as $i => [$title, $text]) : ?>
      <li class="value" data-reveal="up"<?php echo $i ? ' data-delay="' . ($i * 80) . '"' : ''; ?>><span class="index"><?php echo str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); ?></span><h3><?php echo esc_html($title); ?></h3><p><?php echo esc_html($text); ?></p></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php $team = gcc_team(); if ($team) : ?>
<section class="section" aria-labelledby="team-title">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">People</p><h2 id="team-title" class="split-words">The team</h2></div>
      <div class="aside" data-reveal="up"><p class="muted"><?php echo esc_html(gcc_setting('team_intro')); ?></p></div>
    </div>
    <div class="team">
      <?php foreach ($team as $i => $m) : $src = has_post_thumbnail($m->ID) ? (int) get_post_thumbnail_id($m->ID) : gcc_meta($m->ID, 'bundled'); ?>
      <div class="member" data-reveal="up"<?php echo $i ? ' data-delay="' . ($i * 100) . '"' : ''; ?>>
        <div class="media"><?php echo is_int($src) ? gcc_image($src, 'Portrait of ' . $m->post_title, '(max-width: 960px) 50vw, 33vw') : '<img src="' . esc_url(get_theme_file_uri('assets/images/' . $src . '-400.webp')) . '" alt="Portrait of ' . esc_attr($m->post_title) . '" loading="lazy" width="400" height="400">'; ?></div>
        <div class="name"><h3><?php echo esc_html($m->post_title); ?></h3><span class="role"><?php echo esc_html(gcc_meta($m->ID, 'role')); ?></span></div>
        <?php if (gcc_meta($m->ID, 'quote') !== '') : ?><blockquote><?php echo esc_html(gcc_meta($m->ID, 'quote')); ?></blockquote><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" style="padding-top:0" aria-labelledby="workshop-title">
  <div class="wrap workshop">
    <?php echo gcc_media('crane-01', 'Mobile crane lifting a container from a truck onto a site', 'ratio-16-9', '(max-width: 960px) 100vw, 58vw'); ?>
    <div class="copy">
      <p class="eyebrow" data-reveal="up">The yard</p>
      <h2 id="workshop-title" class="split-words"><?php echo esc_html(gcc_setting('yard_heading')); ?></h2>
      <p class="muted mt-md" data-reveal="up"><?php echo esc_html(gcc_setting('yard_text')); ?></p>
      <address data-reveal="up"><?php echo esc_html(gcc_setting('address')); ?><br><?php echo esc_html(gcc_setting('po_box')); ?><br>Mon–Fri <?php echo esc_html(gcc_setting('hours_week')); ?> · Sat–Sun <?php echo esc_html(gcc_setting('hours_weekend')); ?></address>
      <a class="btn mt-md" href="<?php echo esc_url(gcc_page_url('contact')); ?>">Contact us</a>
    </div>
  </div>
</section>
<?php get_footer(); ?>
