<?php /* Template Name: Contact */ get_header(); the_post();
$old = static fn (string $k, string $d = ''): string => class_exists(GCC\Forms::class) ? GCC\Forms::old($k, $d) : $d;
$err = static fn (string $k): string => class_exists(GCC\Forms::class) ? GCC\Forms::error($k) : '';
$preset = class_exists(GCC\Forms::class) ? GCC\Forms::preset() : ['type' => '', 'project' => 0, 'about' => ''];
$types = class_exists(GCC\Settings::class) ? GCC\Settings::PROJECT_TYPES : [];
$sizes = class_exists(GCC\Settings::class) ? GCC\Settings::SIZES : [];
$timelines = class_exists(GCC\Settings::class) ? GCC\Settings::TIMELINES : [];
$phone = gcc_setting('phone'); $phone2 = gcc_setting('phone_2'); $email = gcc_setting('email');
$inv = static fn (string $k): string => $err($k) ? ' aria-invalid="true"' : '';
$cls = static fn (string $k): string => $err($k) ? ' is-invalid' : '';
get_template_part('template-parts/page-hero', null, ['title' => get_the_title(), 'lede' => has_excerpt() ? get_the_excerpt() : gcc_setting('contact_lede'), 'crumbs' => ['Contact' => '']]);
?>

<section class="section">
  <div class="wrap contact-layout">
    <div class="contact-details">
      <div class="block" data-reveal="up"><h2>Call</h2><?php if ($phone) : ?><a href="tel:<?php echo esc_attr(gcc_digits($phone)); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?><?php if ($phone2) : ?><a href="tel:<?php echo esc_attr(gcc_digits($phone2)); ?>"><?php echo esc_html($phone2); ?></a><?php endif; ?></div>
      <div class="block" data-reveal="up"><h2>Email</h2><a href="mailto:<?php echo esc_attr($email); ?>" style="font-size:1.1rem;word-break:break-all"><?php echo esc_html($email); ?></a></div>
      <div class="block" data-reveal="up"><h2>Visit</h2><p><?php echo esc_html(explode(',', gcc_setting('address'))[0]); ?></p><p class="small"><?php echo esc_html(trim((string) strstr(gcc_setting('address'), ','), ', ')); ?><br><?php echo esc_html(gcc_setting('po_box')); ?></p><a class="link mt-md" style="font-size:.9rem" href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode(gcc_setting('map_query')); ?>" target="_blank" rel="noopener">Open in maps</a></div>
      <div class="block" data-reveal="up"><h2>Hours</h2><dl class="hours"><dt>Mon–Fri</dt><dd><?php echo esc_html(gcc_setting('hours_week')); ?></dd><dt>Sat–Sun</dt><dd><?php echo esc_html(gcc_setting('hours_weekend')); ?></dd></dl></div>
    </div>

    <div class="contact-form-wrap" id="quote">
      <div class="form-intro">
        <p class="eyebrow" data-reveal="up">Quote request</p>
        <h2 class="h3 split-words">Four questions and a message</h2>
        <?php if ($preset['about'] !== '') : ?><p class="muted" data-reveal="up">Asking about <strong><?php echo esc_html($preset['about']); ?></strong>. Tell us what you would change.</p><?php endif; ?>
      </div>
      <?php get_template_part('template-parts/flash'); ?>

      <form class="form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-quote-form novalidate>
        <input type="hidden" name="action" value="gcc_quote">
        <input type="hidden" name="_back" value="<?php echo esc_url((string) get_permalink()); ?>">
        <input type="hidden" name="project" value="<?php echo esc_attr($old('project', (string) ($preset['project'] ?: ''))); ?>">
        <?php wp_nonce_field('gcc_quote', '_gcc_nonce'); ?>
        <div class="hp" aria-hidden="true" style="position:absolute;left:-9999px"><label for="f-website">Website</label><input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>

        <div class="field<?php echo $cls('name'); ?>">
          <label for="f-name">Name <span class="req">*</span></label>
          <input id="f-name" name="name" type="text" autocomplete="name" value="<?php echo esc_attr($old('name')); ?>"<?php echo $inv('name'); ?>>
          <?php get_template_part('template-parts/field-error', null, ['field' => 'name']); ?>
        </div>
        <div class="field<?php echo $cls('email'); ?>">
          <label for="f-email">Email <span class="req">*</span></label>
          <input id="f-email" name="email" type="email" autocomplete="email" value="<?php echo esc_attr($old('email')); ?>"<?php echo $inv('email'); ?>>
          <?php get_template_part('template-parts/field-error', null, ['field' => 'email']); ?>
        </div>
        <div class="field<?php echo $cls('phone'); ?>">
          <label for="f-phone">Phone / WhatsApp <span class="opt">optional</span></label>
          <input id="f-phone" name="phone" type="tel" autocomplete="tel" placeholder="<?php echo esc_attr(gcc_setting('phone_hint')); ?>" value="<?php echo esc_attr($old('phone')); ?>"<?php echo $inv('phone'); ?>>
          <?php get_template_part('template-parts/field-error', null, ['field' => 'phone']); ?>
        </div>
        <div class="field<?php echo $cls('project_type'); ?>">
          <label for="f-type">Project type <span class="req">*</span></label>
          <select id="f-type" name="project_type"<?php echo $inv('project_type'); ?>>
            <option value="">Choose…</option>
            <?php foreach ($types as $v => $l) : ?><option value="<?php echo esc_attr($v); ?>"<?php selected($old('project_type', $preset['type']), $v); ?>><?php echo esc_html($l); ?></option><?php endforeach; ?>
          </select>
          <?php get_template_part('template-parts/field-error', null, ['field' => 'project_type']); ?>
        </div>
        <fieldset class="field is-full<?php echo $cls('size'); ?>" style="border:0;padding:0;margin:0;min-width:0">
          <legend style="padding:0" class="mono">Container size <span class="opt" style="color:var(--mist)">optional</span></legend>
          <div class="choice-group">
            <?php foreach ($sizes as $v => $l) : ?><label class="choice"><input type="radio" name="size" value="<?php echo esc_attr($v); ?>"<?php checked($old('size'), $v); ?>><span><?php echo esc_html($l); ?></span></label><?php endforeach; ?>
          </div>
          <?php get_template_part('template-parts/field-error', null, ['field' => 'size']); ?>
        </fieldset>
        <div class="field<?php echo $cls('location'); ?>">
          <label for="f-location">Site location <span class="req">*</span></label>
          <input id="f-location" name="location" type="text" placeholder="Town or district, country" value="<?php echo esc_attr($old('location')); ?>"<?php echo $inv('location'); ?>>
          <?php get_template_part('template-parts/field-error', null, ['field' => 'location']); ?>
        </div>
        <div class="field<?php echo $cls('timeline'); ?>">
          <label for="f-timeline">Timeline <span class="opt">optional</span></label>
          <select id="f-timeline" name="timeline">
            <option value="">Choose…</option>
            <?php foreach ($timelines as $t) : ?><option<?php selected($old('timeline'), $t); ?>><?php echo esc_html($t); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field is-full<?php echo $cls('message'); ?>">
          <label for="f-message">Your project <span class="req">*</span></label>
          <textarea id="f-message" name="message" placeholder="What will the unit be used for, how many people, any must-have features…"<?php echo $inv('message'); ?>><?php echo esc_textarea($old('message')); ?></textarea>
          <p class="hint">A few sentences help us quote accurately.</p>
          <?php get_template_part('template-parts/field-error', null, ['field' => 'message']); ?>
        </div>
        <?php if (class_exists(GCC\Recaptcha::class)) { echo GCC\Recaptcha::widget(); } get_template_part('template-parts/field-error', null, ['field' => 'recaptcha']); ?>
        <div class="form-actions">
          <button class="btn btn-yellow" type="submit"><span class="label">Send request</span><span class="spinner" aria-hidden="true"><i></i></span></button>
          <button class="btn btn-ghost btn-sm" type="reset">Clear</button>
          <p class="fine">Fields marked * are required. <?php echo esc_html(gcc_setting('reply_within')); ?></p>
        </div>
        <div class="form-status" id="form-status-client" hidden></div>
      </form>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0" aria-labelledby="map-title">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">Find us</p><h2 id="map-title" class="split-words"><?php echo esc_html(explode(',', gcc_setting('address'))[0]); ?></h2></div>
      <div class="aside" data-reveal="up"><p class="muted"><?php echo esc_html(trim((string) strstr(gcc_setting('address'), ','), ', ')); ?>. Visit the yard to see units in progress.</p></div>
    </div>
    <div class="map-wrap" data-reveal="up">
      <iframe class="map" src="https://www.google.com/maps?q=<?php echo rawurlencode(gcc_setting('map_query')); ?>&amp;z=16&amp;output=embed" title="Map showing <?php echo esc_attr(gcc_setting('short_name')); ?>" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
      <div class="map-card">
        <span class="plate"><span class="dot"></span><b>The yard</b></span>
        <p class="h4" style="margin-top:14px"><?php echo esc_html(gcc_setting('short_name')); ?></p>
        <p class="muted"><?php echo esc_html(gcc_setting('address')); ?></p>
        <a class="btn btn-sm" href="https://www.google.com/maps/dir/?api=1&destination=<?php echo rawurlencode(gcc_setting('map_query')); ?>" target="_blank" rel="noopener">Get directions</a>
      </div>
    </div>
  </div>
</section>
<?php get_footer(); ?>
