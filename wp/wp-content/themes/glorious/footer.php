</main>

<?php $phone = gcc_setting('phone'); $phone2 = gcc_setting('phone_2'); $email = gcc_setting('email'); ?>
<footer class="site-footer corrugated">
  <div class="wrap">
    <div class="footer-top">
      <div class="footer-cta">
        <p class="eyebrow" style="--line:#2f353b">Start a project</p>
        <h2 class="h2" style="margin-top:18px"><?php echo wp_kses_post(nl2br(esc_html(str_replace('. ', ".\n", gcc_setting('cta_title'))))); ?></h2>
        <a class="btn btn-yellow" href="<?php echo esc_url(gcc_quote_url()); ?>">Request a quote <?php echo gcc_arrow(); ?></a>
      </div>
      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="<?php echo esc_url(gcc_page_url('about')); ?>">About</a></li>
          <li><a href="<?php echo esc_url(gcc_page_url('services')); ?>">Services</a></li>
          <li><a href="<?php echo esc_url(gcc_work_url()); ?>">Work</a></li>
          <li><a href="<?php echo esc_url(gcc_page_url('blog')); ?>">Blog</a></li>
          <li><a href="<?php echo esc_url(gcc_page_url('contact')); ?>">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <ul>
          <?php if ($phone !== '') : ?><li><a href="tel:<?php echo esc_attr(gcc_digits($phone)); ?>"><?php echo esc_html($phone); ?></a></li><?php endif; ?>
          <?php if ($phone2 !== '') : ?><li><a href="tel:<?php echo esc_attr(gcc_digits($phone2)); ?>"><?php echo esc_html($phone2); ?></a></li><?php endif; ?>
          <?php if ($email !== '') : ?><li><a href="mailto:<?php echo esc_attr($email); ?>" style="word-break:break-all"><?php echo esc_html($email); ?></a></li><?php endif; ?>
        </ul>
      </div>
      <?php $socials = gcc_socials(); if ($socials) : ?>
      <div class="footer-col footer-social">
        <h4>Follow</h4>
        <ul class="social-list">
          <?php foreach ($socials as $s) : ?>
            <li><a href="<?php echo esc_url($s['url']); ?>"<?php echo str_starts_with($s['url'], 'http') ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo $s['svg']; ?><?php echo esc_html($s['label']); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <div class="footer-col">
        <h4>Visit</h4>
        <ul>
          <li><?php echo esc_html(gcc_setting('address')); ?></li>
          <?php if (gcc_setting('po_box') !== '') : ?><li><?php echo esc_html(gcc_setting('po_box')); ?></li><?php endif; ?>
          <li>Mon–Fri <?php echo esc_html(gcc_setting('hours_week')); ?><br>Sat–Sun <?php echo esc_html(gcc_setting('hours_weekend')); ?></li>
        </ul>
      </div>
    </div>
    <div class="footer-wordmark" aria-hidden="true"><?php echo esc_html(explode(' ', gcc_setting('short_name'))[0]); ?></div>
    <div class="footer-bottom">
      <span>&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php echo esc_html(gcc_setting('company_name')); ?></span>
      <span>Container housing &middot; Container conversions &middot; Containers</span>
    </div>
  </div>
</footer>

<a class="to-top" href="#main" aria-label="Back to top"><span class="icon-btn"><?php echo gcc_icon('up'); ?></span></a>

<?php wp_footer(); ?>
</body>
</html>
