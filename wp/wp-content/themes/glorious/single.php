<?php
/** One article, in the prototype's layout: sticky contents, share, the text, the author, related reading. */
get_header(); the_post();
$id = get_the_ID();
$cats = get_the_category($id);
$toc = gcc_toc($id);
$cover = gcc_cover($id, 'hero-call-centre');
$share = rawurlencode(get_the_title() . ' — ' . gcc_setting('short_name') . ' ' . get_permalink());
$meta = '<div class="article-meta" data-reveal="up"><span class="plate"><span class="dot"></span><b>' . esc_html(get_the_date('j M Y')) . '</b></span><span class="mono">By ' . esc_html(get_the_author()) . '</span><span class="mono">' . esc_html(gcc_read_time($id)) . ' read</span><span class="tags">';
foreach ($cats as $c) {
    $meta .= '<a class="tag" href="' . esc_url((string) get_category_link($c)) . '">' . esc_html($c->name) . '</a>';
}
$meta .= '</span></div>';
get_template_part('template-parts/page-hero', null, ['title' => get_the_title(), 'lede' => get_the_excerpt(), 'crumbs' => ['Blog' => gcc_page_url('blog'), 'Article' => ''], 'extra' => $meta, 'class' => 'article-hero']);
?>

<section class="wrap article-cover">
  <figure>
    <?php echo gcc_media($cover, gcc_meta($id, 'cover_alt') ?: get_the_title(), 'ratio-21-9', '(max-width: 960px) 100vw, 90vw', 'wipe', true); ?>
    <?php $cap = gcc_meta($id, 'cover_caption') ?: (has_post_thumbnail() ? wp_get_attachment_caption((int) get_post_thumbnail_id()) : ''); if ($cap) : ?><figcaption class="caption"><span><?php echo esc_html((string) $cap); ?></span></figcaption><?php endif; ?>
  </figure>
</section>

<section class="section-tight">
  <div class="wrap article-layout">
    <aside class="article-side">
      <?php if ($toc) : ?>
      <div class="side-block">
        <h2 class="mono">In this article</h2>
        <ol class="toc" data-spy><?php foreach ($toc as [$sid, $text]) : ?><li><a href="#<?php echo esc_attr($sid); ?>"><?php echo esc_html($text); ?></a></li><?php endforeach; ?></ol>
      </div>
      <?php endif; ?>
      <div class="side-block">
        <h2 class="mono">Share</h2>
        <ul class="share">
          <li><a href="https://wa.me/?text=<?php echo $share; ?>" target="_blank" rel="noopener">WhatsApp</a></li>
          <li><a href="https://x.com/intent/tweet?text=<?php echo $share; ?>" target="_blank" rel="noopener">X</a></li>
          <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode((string) get_permalink()); ?>" target="_blank" rel="noopener">Facebook</a></li>
        </ul>
      </div>
      <div class="side-block side-cta">
        <p class="h4">Planning a build?</p>
        <p class="muted">CAD drawings within 48 hours of your enquiry.</p>
        <a class="btn btn-yellow btn-sm" href="<?php echo esc_url(gcc_quote_url()); ?>">Request a quote</a>
      </div>
    </aside>
    <article class="prose">
      <?php the_content(); ?>
      <div class="author-card">
        <?php $avatar = get_avatar_url(get_the_author_meta('ID'), ['size' => 200]); ?>
        <div class="media"><img src="<?php echo esc_url($avatar); ?>" alt="" width="200" height="200" loading="lazy"></div>
        <div><p class="h4"><?php the_author(); ?></p><p class="muted"><?php echo esc_html(get_the_author_meta('description') ?: gcc_setting('short_name')); ?></p></div>
      </div>
    </article>
  </div>
</section>

<?php $related = get_posts(['numberposts' => 3, 'exclude' => [$id], 'category__in' => array_map(static fn ($c) => $c->term_id, $cats)]);
if (count($related) < 3) { $related = array_merge($related, get_posts(['numberposts' => 3 - count($related), 'exclude' => array_merge([$id], array_map(static fn ($p) => $p->ID, $related))])); }
if ($related) : ?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="section-head">
      <div class="title"><p class="eyebrow" data-reveal="up">Keep reading</p><h2 class="split-words">Related articles</h2></div>
      <div class="aside" data-reveal="up"><a class="link" href="<?php echo esc_url(gcc_page_url('blog')); ?>">All articles <?php echo gcc_arrow(); ?></a></div>
    </div>
    <div class="articles"><?php foreach ($related as $i => $p) { get_template_part('template-parts/post-card', null, ['post' => $p, 'delay' => $i * 120]); } ?></div>
  </div>
</section>
<?php endif; ?>
<?php get_footer(); ?>
