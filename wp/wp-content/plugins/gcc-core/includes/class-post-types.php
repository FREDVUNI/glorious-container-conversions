<?php
declare(strict_types=1);

namespace GCC;

/**
 * The records the site keeps.
 *
 * Projects, services, team members and testimonials are content the public
 * reads. Enquiries are what visitors send through the quote form, kept
 * private and never given a URL. The blog uses WordPress's own posts: its
 * editor, categories and media library are already better than anything
 * worth rebuilding.
 */
final class PostTypes
{
    public const PROJECT = 'gcc_project';
    public const SERVICE = 'gcc_service';
    public const TEAM = 'gcc_team';
    public const TESTIMONIAL = 'gcc_testimonial';
    public const ENQUIRY = 'gcc_enquiry';
    public const CATEGORY = 'gcc_project_category';

    /** The statuses the office moves an enquiry through. */
    public const ENQUIRY_STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'quoted' => 'Quoted',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    public static function boot(): void
    {
        add_action('init', [self::class, 'register']);
        // A photo bundled with the theme, inside an article. The seeded
        // articles use it; an editor can too: [gcc_image name="home-blue-01" alt="…"].
        add_shortcode('gcc_image', static function (array $atts): string {
            $a = shortcode_atts(['name' => '', 'alt' => '', 'caption' => ''], $atts);
            $name = sanitize_file_name((string) $a['name']);
            if ($name === '' || ! file_exists(get_theme_file_path('assets/images/' . $name . '-800.webp'))) {
                return '';
            }
            $base = get_theme_file_uri('assets/images/' . $name);
            $img = '<div class="media ratio-3-2"><img src="' . esc_url($base . '-800.webp') . '" srcset="' . esc_url($base . '-800.webp') . ' 800w, ' . esc_url($base . '-1600.webp') . ' 1600w" sizes="(max-width: 960px) 100vw, 60vw" alt="' . esc_attr((string) $a['alt']) . '" loading="lazy" decoding="async"></div>';

            return $a['caption'] !== '' ? '<figure class="wide">' . $img . '<figcaption class="caption"><span>' . esc_html((string) $a['caption']) . '</span></figcaption></figure>' : $img;
        });
        // A hero figure counts records live; the minute-long cache is dropped
        // the moment any record is saved.
        add_action('save_post', static function (): void {
            foreach (array_keys(Settings::STAT_SOURCES) as $source) {
                delete_transient('gcc_stat_' . $source);
            }
        });
    }

    public static function register(): void
    {
        register_taxonomy(self::CATEGORY, self::PROJECT, [
            'labels' => ['name' => 'Project categories', 'singular_name' => 'Project category', 'menu_name' => 'Categories', 'all_items' => 'All categories', 'add_new_item' => 'Add new category', 'edit_item' => 'Edit category'],
            'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'work/category', 'with_front' => false],
        ]);

        // The projects: what the Work page, the lightbox and the home page's
        // "Recent builds" are built from. Each has a page of its own at
        // /work/<slug>/, which is what gets shared on WhatsApp.
        register_post_type(self::PROJECT, [
            'public' => true,
            'has_archive' => 'work',
            'show_in_menu' => 'gcc',
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions'],
            'labels' => self::labels('Project', 'Projects'),
            'rewrite' => ['slug' => 'work', 'with_front' => false],
            'taxonomies' => [self::CATEGORY],
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'menu_icon' => 'dashicons-building',
        ]);

        // The services, in the order set under GCC → Services. They have no
        // page of their own: the Services page lists them all, and links go
        // to /services/#<slug> (see gcc_service_url()).
        register_post_type(self::SERVICE, [
            'public' => false,
            'show_ui' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'show_in_menu' => 'gcc',
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions'],
            'labels' => self::labels('Service', 'Services'),
            'rewrite' => false,
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ]);

        $private = [
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'gcc',
            'show_in_rest' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ];

        register_post_type(self::TEAM, $private + [
            'labels' => self::labels('Team member', 'Team'),
            'supports' => ['title', 'thumbnail', 'page-attributes'],
        ]);

        register_post_type(self::TESTIMONIAL, $private + [
            'labels' => self::labels('Testimonial', 'Testimonials'),
            'supports' => ['title', 'editor', 'page-attributes'],
        ]);

        // Submissions. No editor: the fields are what the visitor typed and
        // staff only change the status and notes.
        register_post_type(self::ENQUIRY, $private + [
            'show_in_rest' => false,
            'supports' => ['title'],
            'labels' => self::labels('Enquiry', 'Enquiries'),
            'capabilities' => ['create_posts' => 'do_not_allow'],
        ]);
    }

    /** @return array<string, string> */
    private static function labels(string $singular, string $plural): array
    {
        return [
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $plural,
            'add_new' => 'Add new',
            'add_new_item' => 'Add new ' . strtolower($singular),
            'edit_item' => 'Edit ' . strtolower($singular),
            'new_item' => 'New ' . strtolower($singular),
            'view_item' => 'View ' . strtolower($singular),
            'search_items' => 'Search ' . strtolower($plural),
            'not_found' => 'None yet.',
            'all_items' => $plural,
        ];
    }
}
