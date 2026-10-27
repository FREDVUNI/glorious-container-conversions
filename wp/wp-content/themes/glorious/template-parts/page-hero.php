<?php
/**
 * The inner-page title block. $args: title, lede, crumbs (label => url, '' for the current page), extra (HTML after the lede).
 */
$title = (string) ($args['title'] ?? get_the_title());
$lede = (string) ($args['lede'] ?? '');
$crumbs = (array) ($args['crumbs'] ?? []);
?>
<section class="wrap page-hero<?php echo ! empty($args['class']) ? ' ' . esc_attr((string) $args['class']) : ''; ?>">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
    <?php foreach ($crumbs as $label => $url) : ?>
      <span><?php if ($url !== '') : ?><a href="<?php echo esc_url((string) $url); ?>"><?php echo esc_html((string) $label); ?></a><?php else : ?><?php echo esc_html((string) $label); ?><?php endif; ?></span>
    <?php endforeach; ?>
  </nav>
  <h1 class="split-words"><?php echo esc_html($title); ?></h1>
  <?php if ($lede !== '') : ?><div class="lede" data-reveal="up"><p><?php echo esc_html($lede); ?></p></div><?php endif; ?>
  <?php echo (string) ($args['extra'] ?? ''); ?>
</section>
