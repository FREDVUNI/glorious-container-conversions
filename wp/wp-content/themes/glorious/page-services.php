<?php /* Template Name: Services */ get_header(); the_post();
$services = gcc_services();
get_template_part('template-parts/page-hero', null, ['title' => get_the_title(), 'lede' => has_excerpt() ? get_the_excerpt() : gcc_setting('services_lede'), 'crumbs' => ['Services' => '']]);
?>

<section class="wrap page-cover">
  <?php echo has_post_thumbnail() ? gcc_media((int) get_post_thumbnail_id(), get_the_title(), 'ratio-21-9', '100vw', 'wipe', true) : gcc_media('u-weld-01', 'Fabricator measuring a steel frame in a workshop', 'ratio-21-9', '100vw', 'wipe', true, 'style="--pos:center 40%"'); ?>
  <div class="cover-strip">
    <?php foreach ([['coldroom-04', 'Technician fitting a refrigeration unit inside a container'], ['callcentre-03', 'Entrance door of the call centre container'], ['sliding-02', 'Sliding door frame during fit-out'], ['u-cafe-01', 'Blue container coffee kiosk with a serving window']] as [$n, $a]) { echo gcc_media($n, $a, 'ratio-4-3', '25vw', ''); } ?>
  </div>
</section>

<section class="section">
  <div class="wrap services-layout">
    <aside class="services-nav">
      <h2>Index</h2>
      <ul data-spy>
        <?php foreach ($services as $i => $s) : ?>
          <li><a href="#<?php echo esc_attr($s->post_name); ?>"><span class="index"><?php echo str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); ?></span><?php echo esc_html($s->post_title); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </aside>
    <div class="services-list">
      <?php if (! $services) : ?><p class="muted">No services yet. Add them under GCC → Services.</p><?php endif; ?>
      <?php foreach ($services as $i => $s) : $features = gcc_meta_lines($s->ID, 'features'); $type = gcc_meta($s->ID, 'quote_type'); ?>
      <article class="service" id="<?php echo esc_attr($s->post_name); ?>">
        <div class="s-head"><h3><?php echo esc_html($s->post_title); ?></h3><span class="index"><?php echo str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) . ' / ' . str_pad((string) count($services), 2, '0', STR_PAD_LEFT); ?></span></div>
        <div class="s-media">
          <?php echo gcc_media(gcc_cover($s->ID), gcc_meta($s->ID, 'alt') ?: $s->post_title, $i % 4 === 3 ? 'ratio-3-4' : 'ratio-4-3', '(max-width: 960px) 100vw, 40vw'); ?>
          <?php if (gcc_meta($s->ID, 'caption') !== '') : ?><p class="caption"><span><?php echo esc_html(gcc_meta($s->ID, 'caption')); ?></span><span><?php echo esc_html(gcc_meta($s->ID, 'caption_right')); ?></span></p><?php endif; ?>
        </div>
        <div class="s-body" data-reveal="up">
          <?php echo wp_kses_post(apply_filters('the_content', $s->post_content ?: '<p>' . $s->post_excerpt . '</p>')); ?>
          <?php if (gcc_meta($s->ID, 'note') !== '') : ?><span class="note"><?php echo esc_html(gcc_meta($s->ID, 'note')); ?></span><?php endif; ?>
          <?php if ($features) : ?><ul><?php foreach ($features as $f) : ?><li><?php echo esc_html($f); ?></li><?php endforeach; ?></ul><?php endif; ?>
          <a class="link" href="<?php echo esc_url(gcc_quote_url($type)); ?>"><?php echo esc_html(gcc_meta($s->ID, 'cta') ?: 'Request a quote'); ?></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php $sizes = []; for ($i = 1; $i <= 3; $i++) { if (gcc_setting("size_{$i}_name") !== '') { $sizes[] = $i; } } if ($sizes) : ?>
<section class="section dark corrugated" aria-labelledby="sizes-title">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">Reference</p><h2 id="sizes-title" class="split-words">Standard unit sizes</h2></div>
      <div class="aside" data-reveal="up"><p class="muted"><?php echo esc_html(gcc_setting('sizes_intro')); ?></p></div>
    </div>
    <div class="sizes">
      <?php foreach ($sizes as $k => $i) : ?>
      <div class="size-card" data-reveal="up"<?php echo $k ? ' data-delay="' . ($k * 100) . '"' : ''; ?>>
        <div class="media ratio-16-9 size-media"><?php echo gcc_setting_image_tag("size_{$i}_image", gcc_setting("size_{$i}_name") . ' container', '(max-width: 960px) 100vw, 33vw'); ?></div>
        <span class="plate"><span class="dot"></span><b><?php echo esc_html(gcc_setting("size_{$i}_name")); ?></b> <?php echo esc_html(gcc_setting("size_{$i}_tag")); ?></span>
        <div class="size-box" style="width:<?php echo $i === 1 ? '50%' : '100%'; ?><?php echo $i === 3 ? ';height:14px' : ''; ?>"></div>
        <p class="h4"><?php echo esc_html(gcc_setting("size_{$i}_title")); ?></p>
        <p><?php echo esc_html(gcc_setting("size_{$i}_use")); ?></p>
        <dl><dt>External</dt><dd><?php echo esc_html(gcc_setting("size_{$i}_external")); ?></dd><dt>Internal</dt><dd><?php echo esc_html(gcc_setting("size_{$i}_internal")); ?></dd><dt>Floor area</dt><dd><?php echo esc_html(gcc_setting("size_{$i}_floor")); ?></dd></dl>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php get_footer(); ?>
