<?php
/** The home page. Every line of copy comes from GCC → Settings; the records from their own screens. */
get_header();

$slides = gcc_hero_slides();
$stats = gcc_stats();
?>
<section class="wrap hero" aria-labelledby="hero-title">
  <div class="hero-copy">
    <p class="eyebrow"><?php echo esc_html(gcc_setting('hero_eyebrow')); ?></p>
    <h1 id="hero-title"><span class="line"><?php echo esc_html(gcc_setting('hero_line_1')); ?></span><span class="line thin"><?php echo esc_html(gcc_setting('hero_line_2')); ?></span><span class="line"><?php echo esc_html(gcc_setting('hero_line_3')); ?></span></h1>
    <p class="lede"><?php echo esc_html(gcc_setting('hero_lede')); ?></p>
    <div class="hero-actions">
      <?php if (gcc_setting('hero_cta') !== '') : ?><a class="btn btn-yellow" href="<?php echo esc_url(gcc_link('hero_cta')); ?>"><?php echo esc_html(gcc_setting('hero_cta')); ?> <?php echo gcc_arrow(); ?></a><?php endif; ?>
      <?php if (gcc_setting('hero_cta_2') !== '') : ?><a class="btn btn-ghost" href="<?php echo esc_url(gcc_link('hero_cta_2')); ?>"><?php echo esc_html(gcc_setting('hero_cta_2')); ?></a><?php endif; ?>
    </div>
  </div>
  <?php if ($slides) : ?>
  <div class="hero-media" data-hero-slider data-autoplay="<?php echo gcc_setting('hero_autoplay') === 'no' ? 'no' : 'yes'; ?>" data-interval="<?php echo esc_attr((string) ((float) gcc_setting('hero_interval', '5.5') * 1000)); ?>">
    <div class="hero-slides">
      <?php foreach ($slides as $i => $s) : ?>
        <div class="media hero-slide<?php echo $i === 0 ? ' is-active' : ''; ?>" data-caption="<?php echo esc_attr($s['plate']); ?>" data-label="<?php echo esc_attr($s['label']); ?>" data-link="<?php echo esc_attr($s['link']); ?>">
          <?php $tag = $s['src'] !== '' ? gcc_image($s['src'], $s['label'], '(max-width: 960px) 100vw, 55vw', $i === 0) : ''; echo $tag !== '' ? $tag : '<img src="' . esc_url($s['url']) . '" alt="' . esc_attr($s['label']) . '">'; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <a class="plate hero-plate" aria-live="polite" href="<?php echo esc_url($slides[0]['link'] ?: '#'); ?>" data-slide-link<?php echo $slides[0]['link'] === '' ? ' style="pointer-events:none"' : ''; ?>><span class="dot"></span><b data-slide-caption><?php echo esc_html($slides[0]['plate']); ?></b> <span data-slide-label><?php echo esc_html($slides[0]['label']); ?></span></a>
    <?php if (count($slides) > 1) : ?>
    <div class="hero-nav">
      <button type="button" class="icon-btn" data-slide-prev aria-label="Previous photo"><?php echo gcc_icon('arrow-l'); ?></button>
      <div class="hero-dots" role="tablist" aria-label="Hero photos"></div>
      <button type="button" class="icon-btn" data-slide-next aria-label="Next photo"><?php echo gcc_arrow(); ?></button>
    </div>
    <?php endif; ?>
    <span class="corner" aria-hidden="true"></span>
  </div>
  <?php endif; ?>
  <?php if ($stats) : ?>
  <div class="hero-specs">
    <?php foreach ($stats as $st) : ?>
      <div class="spec"><b><?php echo is_numeric($st['value']) ? '<span data-count="' . esc_attr($st['value']) . '">0</span>' : esc_html($st['value']); ?><?php if ($st['suffix'] !== '') : ?><small><?php echo esc_html($st['suffix']); ?></small><?php endif; ?></b><span><?php echo esc_html($st['label']); ?></span></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php $marquee = gcc_lines('marquee'); if ($marquee) : ?>
<div class="marquee" aria-hidden="true">
  <div class="marquee-track"><?php foreach ($marquee as $item) : ?><span><?php echo esc_html($item); ?></span><?php endforeach; ?></div>
</div>
<?php endif; ?>

<section class="section">
  <div class="wrap statement">
    <p class="eyebrow" data-reveal="up">What we do</p>
    <p class="text split-words"><?php echo gcc_rich(gcc_setting('statement')); ?></p>
    <div class="statement-media">
      <?php echo gcc_media('u-port-02', 'Looking up at stacked shipping containers against a cloudy sky', 'ratio-3-4', '(max-width: 960px) 50vw, 20vw'); ?>
      <?php echo gcc_caption('caption_statement_1'); ?>
    </div>
    <div class="statement-media statement-media-2">
      <?php echo gcc_media('branding-01', 'Worker applying Glorious Container branding to a painted blue unit', 'ratio-4-3', '(max-width: 960px) 50vw, 24vw', 'wipe', false, 'data-delay="150"'); ?>
      <?php echo gcc_caption('caption_statement_2'); ?>
    </div>
  </div>
</section>

<?php
$uses = array_values(array_filter(gcc_services(), static fn (WP_Post $s): bool => gcc_meta($s->ID, 'home') === 'yes'));
$uses = array_slice($uses, 0, 3);
if ($uses) : ?>
<section class="section" style="padding-top:0" aria-labelledby="uses-title">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">What we build</p><h2 id="uses-title" class="split-words"><?php echo count($uses) === 3 ? 'Three ways to use a box' : 'Ways to use a box'; ?></h2></div>
      <div class="aside" data-reveal="up"><p class="muted"><?php echo esc_html(gcc_setting('uses_intro')); ?></p></div>
    </div>
    <div class="uses">
      <?php foreach ($uses as $i => $s) : $tags = array_filter(array_map('trim', explode(',', gcc_meta($s->ID, 'tags')))); ?>
      <article class="use">
        <a class="use-media" href="<?php echo esc_url((string) gcc_service_url($s)); ?>" aria-label="<?php echo esc_attr($s->post_title); ?>">
          <?php echo gcc_media(gcc_cover($s->ID), gcc_meta($s->ID, 'alt') ?: $s->post_title, 'ratio-3-2', '(max-width: 960px) 100vw, 58vw'); ?>
          <p class="caption"><span><?php echo esc_html(gcc_meta($s->ID, 'caption')); ?></span><span><?php echo esc_html(gcc_meta($s->ID, 'caption_right')); ?></span></p>
        </a>
        <div class="use-body" data-reveal="up">
          <span class="index"><?php echo str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) . ' / ' . str_pad((string) count($uses), 2, '0', STR_PAD_LEFT); ?></span>
          <h3><?php echo esc_html($s->post_title); ?></h3>
          <p><?php echo esc_html($s->post_excerpt); ?></p>
          <?php if ($tags) : ?><div class="tags"><?php foreach ($tags as $t) : ?><span class="tag"><?php echo esc_html($t); ?></span><?php endforeach; ?></div><?php endif; ?>
          <a class="link" href="<?php echo esc_url((string) gcc_service_url($s)); ?>"><?php echo esc_html($s->post_title); ?> <?php echo gcc_arrow(); ?></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php $featured = gcc_featured_projects(); if ($featured) : ?>
<section class="section dark corrugated" aria-labelledby="work-title">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">Selected work</p><h2 id="work-title" class="split-words">Recent builds</h2></div>
      <div class="aside" data-reveal="up"><a class="link" href="<?php echo esc_url(gcc_work_url()); ?>">All projects <?php echo gcc_arrow(); ?></a></div>
    </div>
    <div class="featured">
      <?php $ratios = ['ratio-4-3', 'ratio-3-4', 'ratio-21-9']; $sizes = ['(max-width: 960px) 100vw, 58vw', '(max-width: 960px) 100vw, 40vw', '(max-width: 960px) 100vw, 66vw'];
      foreach ($featured as $i => $p) :
          $images = gcc_project_images($p->ID);
          $img = $i === 1 && isset($images[3]) ? $images[3] : ($images[0] ?? null);
          $terms = wp_get_post_terms($p->ID, 'gcc_project_category'); $term = $terms && ! is_wp_error($terms) ? $terms[0]->name : '';
          $specs = gcc_meta_pairs($p->ID, 'specs'); ?>
      <a class="feat feat-<?php echo $i + 1; ?>" href="<?php echo esc_url((string) get_permalink($p)); ?>">
        <?php if (gcc_meta($p->ID, 'ref') !== '') : ?><span class="plate"><span class="dot"></span><b><?php echo esc_html(gcc_meta($p->ID, 'ref')); ?></b> <?php echo esc_html($term); ?></span><?php endif; ?>
        <?php echo $img ? gcc_media($img['src'], $img['alt'] ?: $p->post_title, $ratios[$i] ?? 'ratio-4-3', $sizes[$i] ?? '50vw', 'wipe', false, $i === 1 ? 'data-delay="150"' : '') : ''; ?>
        <div class="meta"><h3><?php echo esc_html($p->post_title); ?></h3><span class="index"><?php echo esc_html((string) (reset($specs) ?: $term)); ?></span></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" aria-labelledby="process-title">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">How it works</p><h2 id="process-title" class="split-words">From enquiry to keys</h2></div>
      <div class="aside" data-reveal="up"><p class="muted"><?php echo esc_html(gcc_setting('process_intro')); ?></p></div>
    </div>
    <ol class="process">
      <?php for ($i = 1; $i <= 4; $i++) : if (gcc_setting("step_{$i}_title") === '') { continue; } ?>
      <li class="step" data-reveal="up"<?php echo $i > 1 ? ' data-delay="' . (($i - 1) * 100) . '"' : ''; ?>>
        <div class="media ratio-4-3 step-media"><?php echo gcc_setting_image_tag("step_{$i}_image", gcc_setting("step_{$i}_title"), '(max-width: 960px) 50vw, 25vw'); ?></div>
        <span class="index">Step <?php echo str_pad((string) $i, 2, '0', STR_PAD_LEFT); ?></span>
        <h3><?php echo esc_html(gcc_setting("step_{$i}_title")); ?></h3>
        <p><?php echo esc_html(gcc_setting("step_{$i}_text")); ?></p>
        <?php if (gcc_setting("step_{$i}_time") !== '') : ?><span class="time"><?php echo esc_html(gcc_setting("step_{$i}_time")); ?></span><?php endif; ?>
      </li>
      <?php endfor; ?>
    </ol>
  </div>
</section>

<?php $why = gcc_pairs('why_items'); if ($why) : ?>
<section class="section teal-bg" aria-labelledby="why-title">
  <div class="wrap why">
    <div class="why-copy">
      <p class="eyebrow" data-reveal="up">Why containers</p>
      <h2 id="why-title" class="split-words"><?php echo esc_html(gcc_setting('why_title')); ?></h2>
      <p class="lede" data-reveal="up"><?php echo esc_html(gcc_setting('why_lede')); ?></p>
      <div class="why-media mt-lg">
        <?php echo gcc_media('stack-01', 'Two 40ft containers stacked on top of a yellow unit during a build', 'ratio-4-3', '(max-width: 960px) 100vw, 40vw'); ?>
        <?php echo gcc_caption('caption_why'); ?>
      </div>
    </div>
    <ul class="why-list">
      <?php foreach ($why as $i => [$title, $text]) : ?>
      <li class="why-item" data-reveal="up"<?php echo $i ? ' data-delay="' . ($i * 80) . '"' : ''; ?>><span class="index"><?php echo str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); ?></span><div><h3><?php echo esc_html($title); ?></h3><p><?php echo esc_html($text); ?></p></div></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php $countries = gcc_pairs('countries'); if ($countries) : ?>
<section class="section" aria-labelledby="coverage-title">
  <div class="wrap coverage">
    <div class="coverage-copy">
      <p class="eyebrow" data-reveal="up">Where we deliver</p>
      <h2 id="coverage-title" class="split-words"><?php echo esc_html(gcc_setting('coverage_title')); ?></h2>
      <p class="muted mt-md" data-reveal="up"><?php echo esc_html(gcc_setting('coverage_text')); ?></p>
      <div class="coverage-media mt-lg">
        <?php echo gcc_media('u-truck-01', 'Truck carrying a 40ft container along a dirt road', 'ratio-3-4', '(max-width: 960px) 100vw, 30vw'); ?>
        <?php echo gcc_caption('caption_coverage'); ?>
      </div>
    </div>
    <ul class="coverage-list" data-reveal="up">
      <?php foreach ($countries as $i => [$name, $mode]) : ?>
      <li><span class="index"><?php echo str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); ?></span><?php echo esc_html($name); ?><span class="mode"><?php echo esc_html($mode); ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php $testimonials = gcc_testimonials(); if ($testimonials) : ?>
<section class="section" style="padding-top:0" aria-labelledby="testimonials-title">
  <div class="wrap">
    <div class="section-head"><div class="title"><p class="eyebrow" data-reveal="up">Clients</p><h2 id="testimonials-title" class="split-words">In their words</h2></div></div>
    <div class="testimonials">
      <?php foreach (array_slice($testimonials, 0, 3) as $i => $t) : $proj = (int) gcc_meta($t->ID, 'project'); ?>
      <div class="testimonial" data-reveal="up"<?php echo $i ? ' data-delay="' . ($i * 100) . '"' : ''; ?>>
        <blockquote><?php echo esc_html(wp_strip_all_tags($t->post_content)); ?></blockquote>
        <p class="who"><b><?php echo esc_html($t->post_title); ?></b><?php echo esc_html(gcc_meta($t->ID, 'role')); ?><?php if ($proj) : ?> · <a href="<?php echo esc_url((string) get_permalink($proj)); ?>"><?php echo esc_html(get_the_title($proj)); ?></a><?php endif; ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php $latest = get_posts(['numberposts' => 3, 'post_status' => 'publish']); if ($latest) : ?>
<section class="section" style="padding-top:0" aria-labelledby="blog-title">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">Blog</p><h2 id="blog-title" class="split-words">Notes from the yard</h2></div>
      <div class="aside" data-reveal="up"><a class="link" href="<?php echo esc_url(gcc_page_url('blog')); ?>">All articles <?php echo gcc_arrow(); ?></a></div>
    </div>
    <div class="articles">
      <?php foreach ($latest as $i => $p) { get_template_part('template-parts/post-card', null, ['post' => $p, 'delay' => $i * 100]); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
