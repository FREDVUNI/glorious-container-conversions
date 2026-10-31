<?php

declare(strict_types=1);

/**
 * Glorious theme. Presentation only: every record, setting and form handler
 * lives in the gcc-core plugin, so this file is short on purpose.
 */

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');
    register_nav_menus(['primary' => 'Primary navigation']);
    // The two widths the prototype serves, so uploaded photos get the same srcset as bundled ones.
    add_image_size('gcc-800', 800, 0, false);
    add_image_size('gcc-1600', 1600, 0, false);
});

add_action('wp_enqueue_scripts', static function (): void {
    $v = wp_get_theme()->get('Version');
    wp_enqueue_style('gcc-fonts', 'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700&family=Barlow:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap', [], null);
    wp_enqueue_style('gcc', get_theme_file_uri('assets/css/styles.css'), ['gcc-fonts'], $v);
    wp_enqueue_style('gcc-animations', get_theme_file_uri('assets/css/animations.css'), ['gcc'], $v);
    wp_enqueue_style('gcc-responsive', get_theme_file_uri('assets/css/responsive.css'), ['gcc'], $v);
    wp_enqueue_style('gcc-theme', get_theme_file_uri('assets/css/theme.css'), ['gcc'], $v);
    wp_enqueue_script('gcc-main', get_theme_file_uri('assets/js/main.js'), [], $v, ['in_footer' => true]);
    wp_enqueue_script('gcc-navigation', get_theme_file_uri('assets/js/navigation.js'), ['gcc-main'], $v, ['in_footer' => true]);
    if (is_post_type_archive('gcc_project') || is_tax('gcc_project_category')) {
        wp_enqueue_script('gcc-projects', get_theme_file_uri('assets/js/projects.js'), ['gcc-main'], $v, ['in_footer' => true]);
    }
    // By page role, not template: the template meta can be missing when the
    // plugin activated before the theme existed (see template_include below).
    if (is_page((int) get_option('gcc_page_contact')) || is_page_template('page-contact.php')) {
        wp_enqueue_script('gcc-forms', get_theme_file_uri('assets/js/forms.js'), ['gcc-main'], $v, ['in_footer' => true]);
        if (class_exists(GCC\Recaptcha::class)) {
            GCC\Recaptcha::enqueue();
        }
    }
});

/**
 * Avada's Fusion Builder plugin, if still active from the old site, prints
 * its own stylesheet on every page and fights the theme. Drop it; the
 * shortcodes it renders are on pages the installer has retired.
 */
add_action('wp_print_styles', static function (): void {
    global $wp_styles;
    foreach ($wp_styles->queue as $handle) {
        if (str_contains($handle, 'fusion') || str_contains($handle, 'avada')) {
            wp_dequeue_style($handle);
        }
    }
}, 100);
add_action('wp_print_scripts', static function (): void {
    global $wp_scripts;
    foreach ($wp_scripts->queue as $handle) {
        if (str_contains($handle, 'fusion') || str_contains($handle, 'avada')) {
            wp_dequeue_script($handle);
        }
    }
}, 100);

/** The no-js class the stylesheets key off, removed by main.js. */
add_filter('language_attributes', static fn (string $out): string => $out . ' class="no-js"');

/** The plugin is required; say so plainly rather than fatal on a missing class. */
add_action('admin_notices', static function (): void {
    if (! class_exists(GCC\Settings::class)) {
        echo '<div class="notice notice-error"><p><strong>Glorious theme:</strong> the GCC Core plugin must be active. Nothing on the site will render correctly without it.</p></div>';
    }
});

if (! function_exists('gcc_setting')) {
    // Fallbacks so a page still renders if the plugin is off for a moment.
    function gcc_setting(string $key, string $default = ''): string
    {
        return $default;
    }
    function gcc_digits(string $v): string
    {
        return preg_replace('/\D+/', '', $v) ?? '';
    }
    function gcc_lines(string $key): array
    {
        return [];
    }
    function gcc_page_url(string $role): string
    {
        return home_url('/');
    }
    function gcc_meta(int $post_id, string $key, string $default = ''): string
    {
        return $default;
    }
    function gcc_icon(string $name, string $class = ''): string
    {
        return '';
    }
    function gcc_socials(): array
    {
        return [];
    }
    function gcc_stats(): array
    {
        return [];
    }
    function gcc_projects(int $limit = -1, array $args = []): array
    {
        return [];
    }
    function gcc_featured_projects(): array
    {
        return [];
    }
    function gcc_services(): array
    {
        return [];
    }
    function gcc_team(): array
    {
        return [];
    }
    function gcc_testimonials(): array
    {
        return [];
    }
    function gcc_meta_lines(int $post_id, string $key): array
    {
        return [];
    }
    function gcc_meta_pairs(int $post_id, string $key): array
    {
        return [];
    }
    function gcc_project_images(int $post_id): array
    {
        return [];
    }
}

/** A bundled image's URL at one of the two widths. */
function gcc_img(string $name, int $width = 800): string
{
    return esc_url(get_theme_file_uri('assets/images/' . $name . '-' . $width . '.webp'));
}

/**
 * One responsive <img>, from either an attachment ID or a bundled base name.
 * The prototype's markup, so the CSS (object-fit, fade-in, reveal) applies
 * to uploaded and bundled photos alike.
 */
function gcc_image(int|string $src, string $alt = '', string $sizes = '(max-width: 960px) 100vw, 50vw', bool $eager = false): string
{
    $loading = $eager ? ' fetchpriority="high"' : ' loading="lazy" decoding="async"';
    if (is_int($src)) {
        $full = wp_get_attachment_image_src($src, 'gcc-1600');
        $small = wp_get_attachment_image_src($src, 'gcc-800');
        if (! $full) {
            return '';
        }
        $alt = $alt !== '' ? $alt : (string) get_post_meta($src, '_wp_attachment_image_alt', true);
        $srcset = ($small ? esc_url($small[0]) . ' 800w, ' : '') . esc_url($full[0]) . ' 1600w';

        return '<img src="' . esc_url($full[0]) . '" srcset="' . $srcset . '" sizes="' . esc_attr($sizes) . '" alt="' . esc_attr($alt) . '" width="' . (int) $full[1] . '" height="' . (int) $full[2] . '"' . $loading . '>';
    }
    if ($src === '' || ! file_exists(get_theme_file_path('assets/images/' . $src . '-800.webp'))) {
        return '';
    }

    return '<img src="' . gcc_img($src, 1600) . '" srcset="' . gcc_img($src, 800) . ' 800w, ' . gcc_img($src, 1600) . ' 1600w" sizes="' . esc_attr($sizes) . '" alt="' . esc_attr($alt) . '"' . $loading . '>';
}

/** The <img> wrapped in the prototype's .media box. */
function gcc_media(int|string $src, string $alt = '', string $ratio = 'ratio-4-3', string $sizes = '(max-width: 960px) 100vw, 50vw', string $reveal = 'wipe', bool $eager = false, string $extra = ''): string
{
    $img = gcc_image($src, $alt, $sizes, $eager);
    if ($img === '') {
        return '';
    }

    return '<div class="media ' . esc_attr($ratio) . '"' . ($reveal !== '' ? ' data-reveal="' . esc_attr($reveal) . '"' : '') . ($extra !== '' ? ' ' . $extra : '') . '>' . $img . '</div>';
}

/**
 * The picture for a record: its featured image when one is set, the bundled
 * prototype photo named in its meta otherwise.
 */
function gcc_cover(int $post_id, string $fallback = ''): int|string
{
    if (has_post_thumbnail($post_id)) {
        return (int) get_post_thumbnail_id($post_id);
    }
    $bundled = gcc_meta($post_id, 'bundled');
    $first = trim(explode(',', $bundled)[0]);

    return $first !== '' ? $first : $fallback;
}

/** An image setting from the plugin (a URL) or its bundled fallback, as the src gcc_media() takes. */
function gcc_setting_image(string $key): int|string
{
    $url = gcc_setting($key);
    if ($url !== '') {
        $id = attachment_url_to_postid($url);
        if ($id) {
            return $id;
        }
    }

    return class_exists(GCC\Settings::class) ? (GCC\Settings::BUNDLED[$key] ?? '') : '';
}

/** A setting's URL image when it is not in the media library (an external URL, say). */
function gcc_setting_image_tag(string $key, string $alt, string $sizes = '100vw', bool $eager = false): string
{
    $src = gcc_setting_image($key);
    if ($src !== '') {
        $tag = gcc_image($src, $alt, $sizes, $eager);
        if ($tag !== '') {
            return $tag;
        }
    }
    $url = gcc_setting($key);

    return $url !== '' ? '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '"' . ($eager ? '' : ' loading="lazy"') . '>' : '';
}

/** The arrow used after links and in buttons. */
function gcc_arrow(): string
{
    return gcc_icon('arrow', 'arrow');
}

/** Reading time from the text, when the editor has not typed one. */
function gcc_read_time(int $post_id): string
{
    $typed = gcc_meta($post_id, 'read');
    if ($typed !== '') {
        return $typed;
    }
    $words = str_word_count(wp_strip_all_tags((string) get_post_field('post_content', $post_id)));

    return max(1, (int) ceil($words / 200)) . ' min';
}

/** The plain text of a setting with *emphasis* turned into the teal <em>. */
function gcc_rich(string $text): string
{
    return preg_replace('/\*([^*]+)\*/', '<em>$1</em>', esc_html($text)) ?? esc_html($text);
}

/** "Country | note" lines as pairs. @return list<array{0:string,1:string}> */
function gcc_pairs(string $key): array
{
    return array_map(static fn (string $l): array => array_map('trim', array_pad(explode('|', $l, 2), 2, '')), gcc_lines($key));
}

/** The URL of the Work page (the project archive). */
function gcc_work_url(): string
{
    return (string) (get_post_type_archive_link('gcc_project') ?: home_url('/work/'));
}

/** A "left | right" caption setting as the prototype's caption row, or nothing when cleared. */
function gcc_caption(string $key): string
{
    $v = gcc_setting($key);
    if (trim($v) === '') {
        return '';
    }
    [$l, $r] = array_map('trim', array_pad(explode('|', $v, 2), 2, ''));

    return '<p class="caption"><span>' . esc_html($l) . '</span>' . ($r !== '' ? '<span>' . esc_html($r) . '</span>' : '') . '</p>';
}

/** A hero button's destination, from Settings. */
function gcc_link(string $key): string
{
    $to = gcc_setting($key . '_link');
    $phone = gcc_digits(gcc_setting('phone'));
    $wa = gcc_digits(gcc_setting('whatsapp'));

    return match ($to) {
        'work' => gcc_work_url(),
        'services' => gcc_page_url('services'),
        'about' => gcc_page_url('about'),
        'blog' => gcc_page_url('blog'),
        'contact' => gcc_page_url('contact'),
        'phone' => $phone !== '' ? 'tel:+' . ltrim($phone, '0') : gcc_page_url('contact'),
        'whatsapp' => $wa !== '' ? 'https://wa.me/' . $wa : gcc_page_url('contact'),
        'custom' => gcc_setting($key . '_url') ?: gcc_quote_url(),
        default => gcc_quote_url(),
    };
}

/**
 * The hero slides: from Settings, or the cover photos of the featured or
 * newest projects. Each: src (attachment ID or bundled name), url (a raw URL
 * when the photo is not in the library), plate, label, link.
 *
 * @return list<array{src:int|string, url:string, plate:string, label:string, link:string}>
 */
function gcc_hero_slides(): array
{
    $max = max(1, min(6, (int) gcc_setting('hero_max', '4')));
    $source = gcc_setting('hero_source');
    if ($source === 'featured' || $source === 'latest') {
        $out = [];
        foreach (array_slice($source === 'featured' ? gcc_featured_projects() : gcc_projects($max), 0, $max) as $p) {
            $cover = gcc_project_images($p->ID)[0] ?? null;
            if ($cover) {
                $out[] = ['src' => $cover['src'], 'url' => '', 'plate' => gcc_meta($p->ID, 'ref') ?: 'Project', 'label' => $p->post_title, 'link' => (string) get_permalink($p)];
            }
        }
        if ($out) {
            return $out;
        }
    }
    $out = [];
    for ($i = 1; $i <= 6; $i++) {
        $plate = gcc_setting("slide_{$i}_plate");
        $label = gcc_setting("slide_{$i}_label");
        $url = gcc_setting("slide_{$i}_image");
        $src = gcc_setting_image("slide_{$i}_image");
        if ($src === '' && $url === '') {
            continue; // nothing chosen and nothing bundled (slides 5 and 6)
        }
        if ($plate === '' && $label === '' && $url === '') {
            continue; // bundled slide the client cleared
        }
        $out[] = ['src' => $src, 'url' => $url, 'plate' => $plate, 'label' => $label, 'link' => ''];
    }

    return array_slice($out, 0, $max);
}

/** A service's place on the Services page. */
function gcc_service_url(WP_Post $service): string
{
    return gcc_page_url('services') . '#' . $service->post_name;
}

/** The quote form, pre-filled for a project type or a project. */
function gcc_quote_url(string $type = '', int $project = 0): string
{
    $url = gcc_page_url('contact');
    if ($type !== '') {
        $url = add_query_arg('type', $type, $url);
    }
    if ($project) {
        $url = add_query_arg('project', $project, $url);
    }

    return $url . '#quote';
}

/** aria-current on the active item, matching the prototype. */
add_filter('nav_menu_link_attributes', static function (array $atts, object $item): array {
    $classes = $item->classes ?? [];
    if (in_array('current-menu-item', $classes, true) || in_array('current_page_parent', $classes, true) || in_array('current-menu-ancestor', $classes, true)) {
        $atts['aria-current'] = 'page';
    }

    return $atts;
}, 10, 2);

add_filter('excerpt_length', static fn (): int => 28);
add_filter('excerpt_more', static fn (): string => '…');

/** Body class per page role, for the odd page-specific rule. */
add_filter('body_class', static function (array $classes): array {
    foreach (['home', 'about', 'services', 'blog', 'contact'] as $role) {
        if (get_queried_object_id() && get_queried_object_id() === (int) get_option('gcc_page_' . $role)) {
            $classes[] = 'page-' . $role;
        }
    }

    return $classes;
});

/**
 * The pages the plugin created always get their template, whatever the
 * page's stored template says. On a host where the theme went live after
 * the plugin, WordPress had refused the template (it did not exist yet).
 */
add_filter('template_include', static function (string $template): string {
    if (! is_page()) {
        return $template;
    }
    $id = get_queried_object_id();
    foreach (['about' => 'page-about.php', 'services' => 'page-services.php', 'contact' => 'page-contact.php'] as $role => $file) {
        if ($id && $id === (int) get_option('gcc_page_' . $role)) {
            $found = locate_template($file);

            return $found !== '' ? $found : $template;
        }
    }

    return $template;
});

/**
 * Article headings get ids so the sticky table of contents can link to them.
 * Only on single posts; the ids match what gcc_toc() generates.
 */
add_filter('the_content', static function (string $content): string {
    if (! is_singular('post') || ! in_the_loop()) {
        return $content;
    }

    return preg_replace_callback('/<h2([^>]*)>(.*?)<\/h2>/s', static fn (array $m): string => '<h2' . $m[1] . ' id="' . sanitize_title(wp_strip_all_tags($m[2])) . '">' . $m[2] . '</h2>', $content) ?? $content;
}, 20);

/** The h2 headings of a post, for its table of contents. @return list<array{0:string,1:string}> */
function gcc_toc(int $post_id): array
{
    preg_match_all('/<h2[^>]*>(.*?)<\/h2>/s', (string) get_post_field('post_content', $post_id), $m);
    $out = [];
    foreach ($m[1] as $h) {
        $text = wp_strip_all_tags($h);
        $out[] = [sanitize_title($text), $text];
    }

    return $out;
}

/** Project data for the Work page lightbox, one entry per card. */
function gcc_project_json(array $projects): string
{
    $cats = [];
    foreach (get_terms(['taxonomy' => 'gcc_project_category', 'hide_empty' => false]) as $t) {
        $cats[$t->slug] = $t->name;
    }
    $out = [];
    foreach ($projects as $p) {
        $terms = wp_get_post_terms($p->ID, 'gcc_project_category');
        $cat = $terms && ! is_wp_error($terms) ? $terms[0]->slug : '';
        $images = [];
        foreach (gcc_project_images($p->ID) as $im) {
            if (is_int($im['src'])) {
                $images[] = ['src' => wp_get_attachment_image_url($im['src'], 'gcc-1600'), 'thumb' => wp_get_attachment_image_url($im['src'], 'gcc-800'), 'alt' => $im['alt']];
            } else {
                $images[] = ['src' => gcc_img($im['src'], 1600), 'thumb' => gcc_img($im['src'], 800), 'alt' => $im['alt']];
            }
        }
        $out[] = [
            'id' => $p->post_name,
            'ref' => gcc_meta($p->ID, 'ref'),
            'title' => $p->post_title,
            'cat' => $cat,
            'catName' => $cats[$cat] ?? '',
            'url' => get_permalink($p),
            'quote' => gcc_quote_url('', $p->ID),
            'summary' => $p->post_excerpt !== '' ? $p->post_excerpt : wp_trim_words(wp_strip_all_tags($p->post_content), 40),
            'specs' => gcc_meta_pairs($p->ID, 'specs'),
            'features' => gcc_meta_lines($p->ID, 'features'),
            'confirm' => gcc_meta($p->ID, 'confirm') === 'yes',
            'images' => $images,
        ];
    }

    return (string) wp_json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
