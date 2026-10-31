<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#15181b">
  <link rel="icon" href="<?php echo esc_url(get_theme_file_uri('assets/images/favicon.svg')); ?>" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php wp_head(); ?>
  <script>document.documentElement.classList.remove('no-js');</script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>
<noscript><div class="noscript-banner">JavaScript is off: the site still reads fully. Menus, the gallery lightbox and form checks need JavaScript.</div></noscript>

<div class="loader" aria-hidden="true">
  <div class="loader-inner">
    <div class="word">Glorious <em>Container</em></div>
    <div class="bar"><i></i></div>
    <div class="mono"><?php echo esc_html(gcc_setting('address')); ?></div>
  </div>
</div>

<?php
$phone = gcc_setting('phone');
$logo = set_url_scheme(gcc_setting('logo') !== '' ? gcc_setting('logo') : get_theme_file_uri('assets/images/logo.png'), is_ssl() ? 'https' : 'http');
$menu_fallback = static function (string $class): void {
    echo '<ul class="' . esc_attr($class) . '">';
    $i = 0;
    foreach ([home_url('/') => 'Home', gcc_page_url('about') => 'About', gcc_page_url('services') => 'Services', gcc_work_url() => 'Work', gcc_page_url('blog') => 'Blog', gcc_page_url('contact') => 'Contact'] as $url => $label) {
        $i++;
        echo '<li><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';
};
?>
<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(gcc_setting('short_name')); ?>, home">
      <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr(gcc_setting('company_name')); ?>" width="338" height="116">
    </a>
    <nav class="nav-primary" aria-label="Primary">
      <?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1, 'fallback_cb' => static fn () => $menu_fallback('nav-flat')]); ?>
    </nav>
    <div class="header-cta">
      <?php if ($phone !== '') : ?>
        <a class="header-contact" href="tel:<?php echo esc_attr(gcc_digits($phone)); ?>"><?php echo gcc_icon('phone'); ?><span><?php echo esc_html($phone); ?></span></a>
      <?php endif; ?>
      <a class="btn btn-yellow btn-sm" href="<?php echo esc_url(gcc_quote_url()); ?>">Get a quote</a>
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-drawer" aria-label="Open menu"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<div class="nav-drawer" id="nav-drawer">
  <div class="corrugated"></div>
  <?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'items_wrap' => '<ul class="nav-drawer-list">%3$s</ul>', 'depth' => 1, 'fallback_cb' => static fn () => $menu_fallback('')]); ?>
  <div class="nav-drawer-foot">
    <?php if ($phone !== '') : ?><a href="tel:<?php echo esc_attr(gcc_digits($phone)); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?>
    <a href="mailto:<?php echo esc_attr(gcc_setting('email')); ?>"><?php echo esc_html(gcc_setting('email')); ?></a>
    <span><?php echo esc_html(gcc_setting('address')); ?></span>
    <a class="btn btn-yellow" href="<?php echo esc_url(gcc_quote_url()); ?>">Request a quote</a>
  </div>
</div>

<main id="main">
