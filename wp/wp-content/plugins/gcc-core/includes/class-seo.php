<?php
declare(strict_types=1);

namespace GCC;

/**
 * Basic SEO: a description per page, a social share image, and the sitemap.
 *
 * WordPress has generated /wp-sitemap.xml for every public post type since
 * 5.5, so nothing is built for that beyond pointing robots.txt at it. What is
 * added here is the Open Graph block, which is what makes a shared link show
 * a picture and a sentence on WhatsApp and Facebook rather than a bare URL.
 */
final class Seo
{
    public static function boot(): void
    {
        add_action('wp_head', [self::class, 'tags'], 5);
        add_filter('robots_txt', [self::class, 'robots'], 10, 2);
    }

    public static function tags(): void
    {
        $title = wp_get_document_title();
        $description = self::description();
        $image = self::image();
        $url = is_singular() ? get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));

        echo "\n<!-- GCC: share preview -->\n";
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(Settings::get('short_name')) . '">' . "\n";
        echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    }

    /** The excerpt when there is one, the site description otherwise. */
    private static function description(): string
    {
        if (is_singular() && has_excerpt()) {
            return wp_strip_all_tags(get_the_excerpt());
        }
        if (is_singular()) {
            $text = wp_strip_all_tags((string) get_post_field('post_content', get_queried_object_id()));
            if ($text !== '') {
                return wp_trim_words($text, 28, '…');
            }
        }

        return Settings::get('site_description');
    }

    /**
     * The featured image of the page being shared, or its first project photo;
     * the share image from Settings otherwise; a bundled photograph last.
     */
    private static function image(): string
    {
        if (is_singular() && has_post_thumbnail()) {
            $url = get_the_post_thumbnail_url(null, 'large');
            if ($url) {
                return $url;
            }
        }
        if (is_singular(PostTypes::PROJECT) && function_exists('gcc_project_images')) {
            $first = gcc_project_images(get_queried_object_id())[0] ?? null;
            if ($first) {
                $url = is_int($first['src']) ? wp_get_attachment_image_url($first['src'], 'large') : get_theme_file_uri('assets/images/' . $first['src'] . '-1600.webp');
                if ($url) {
                    return $url;
                }
            }
        }
        $custom = Settings::get('share_image');
        if ($custom !== '') {
            return $custom;
        }

        return get_theme_file_uri('assets/images/hero-call-centre-1600.webp');
    }

    public static function robots(string $output, bool $public): string
    {
        if ($public) {
            $output .= "\nSitemap: " . home_url('/wp-sitemap.xml') . "\n";
        }

        return $output;
    }
}
