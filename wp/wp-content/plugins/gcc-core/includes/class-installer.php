<?php

declare(strict_types=1);

namespace GCC;

/**
 * First-run setup, so activating the plugin gives a working site rather than
 * a list of things to click through: the pages, the menu, the thirteen
 * projects, ten services, three team members and four articles from the
 * approved prototype.
 */
final class Installer
{
    /** @var array<string, array{title:string, template:string}> */
    private const PAGES = [
        'home'     => ['title' => 'Home',     'template' => 'front-page'],
        'about'    => ['title' => 'About',    'template' => 'page-about.php'],
        'services' => ['title' => 'Services', 'template' => 'page-services.php'],
        'blog'     => ['title' => 'Blog',     'template' => ''],
        'contact'  => ['title' => 'Contact',  'template' => 'page-contact.php'],
    ];

    /**
     * Runs once per plugin version, on the first request after an upload:
     * re-applies the page templates (they are refused by WordPress when the
     * theme is not yet active, which is the usual upload order).
     */
    public static function upgrade(): void
    {
        if (get_option('gcc_version') === GCC_VERSION) {
            return;
        }
        self::pages();
        self::applyTemplates();
        self::migrateFromAvada();
        self::fixMenu();
        flush_rewrite_rules();
        update_option('gcc_version', GCC_VERSION);
    }

    /**
     * The site went live on top of the old Avada install, so some of what
     * activation found was the old site's: a "Primary" menu pointing at
     * Articles and Get Quote, an About page full of builder shortcodes, and
     * old pages still published. Put each right, once; anything an editor has
     * since changed is left alone.
     */
    public static function migrateFromAvada(): void
    {
        if (get_option('gcc_avada_migrated')) {
            return;
        }
        update_option('gcc_avada_migrated', 1);

        // The About page: builder markup becomes our copy.
        $about = (int) get_option('gcc_page_about');
        $body = $about ? (string) get_post_field('post_content', $about) : '';
        if ($about && (str_contains($body, '[fusion_') || str_contains($body, 'fusion-builder'))) {
            wp_update_post(['ID' => $about, 'post_content' => self::ABOUT_BODY]);
        }

        self::fixMenu();

        // Any other published page built with the builder is retired; the
        // redirects in Redirects::MAP carry its visitors to the new page.
        $ours = array_filter(array_map(static fn (string $r): int => (int) get_option('gcc_page_' . $r), array_keys(self::PAGES)));
        foreach (get_posts(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'exclude' => $ours]) as $page) {
            if (str_contains($page->post_content, '[fusion_') || get_page_template_slug($page->ID) === '100-width.php') {
                wp_update_post(['ID' => $page->ID, 'post_status' => 'draft']);
            }
        }

    }

    /**
     * If what sits in the primary slot is the old site's menu (links or
     * titles for Articles / Get Quote, or no link to our Blog or Contact),
     * build ours and assign it. Runs once per plugin version, so a menu the
     * client has since edited to their liking is checked again only after
     * an upload, and only replaced if it still carries the old items.
     */
    public static function fixMenu(): void
    {
        $locations = (array) get_theme_mod('nav_menu_locations');
        $current = (int) ($locations['primary'] ?? 0);
        $stale = $current === 0;
        $hasBlog = $hasContact = false;
        $blog = (int) get_option('gcc_page_blog');
        $contact = (int) get_option('gcc_page_contact');
        if ($current) {
            foreach (wp_get_nav_menu_items($current, ['post_status' => 'any']) ?: [] as $item) {
                if (preg_match('#/(construction-articles|get-quote)/?$#', (string) $item->url) || preg_match('/^(articles|get quote)$/i', trim((string) $item->title))) {
                    $stale = true;
                }
                $hasBlog = $hasBlog || (int) $item->object_id === $blog;
                $hasContact = $hasContact || (int) $item->object_id === $contact;
            }
            if (! $hasBlog || ! $hasContact) {
                $stale = true;
            }
        }
        if ($stale) {
            self::menu('Glorious');
        }
    }

    /** Create any page from the list that does not exist yet. Safe to repeat. */
    public static function pages(): void
    {
        foreach (self::PAGES as $role => $page) {
            $existing = (int) get_option('gcc_page_' . $role);
            if ($existing && get_post($existing)) {
                continue;
            }
            // A site that already has a page at this address keeps it: the
            // template is set on that page rather than a second one being
            // created at /contact-2/.
            $slug = sanitize_title($page['title']);
            $found = get_page_by_path($slug, OBJECT, 'page');
            if ($found instanceof \WP_Post && in_array($found->post_status, ['publish', 'draft'], true)) {
                wp_update_post(['ID' => $found->ID, 'post_status' => 'publish', 'page_template' => $page['template'] ?: 'default']);
                update_option('gcc_page_' . $role, $found->ID);
                continue;
            }
            $id = wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => $page['title'],
                'post_name' => $slug,
                'page_template' => $page['template'] ?: 'default',
                'post_content' => $role === 'about' ? self::ABOUT_BODY : '',
            ]);
            if ($id && ! is_wp_error($id)) {
                update_option('gcc_page_' . $role, $id);
            }
        }
    }

    /** The About page's prose, editable in the normal page editor. */
    private const ABOUT_BODY = '<p class="body-lg">Our team brings more than a decade of expertise in converting shipping containers into infrastructure. The range runs from simple storage units to high-specification, bespoke conversions: homes, offices, clinics, laboratories, cold rooms, restaurants and ablution blocks.</p><p>Everything is built to the client\'s specification. Layouts, finishes and colours are agreed on CAD drawings before fabrication starts, and the drawings are with you within 48 hours of your first enquiry.</p><p>Because all conversion work happens at our own premises, a unit arrives on site finished. Our in-house transport division moves it by road anywhere in Uganda and to neighbouring countries, and our trained team erects and commissions it.</p>';

    /** Give each page the installer made its template (again). */
    public static function applyTemplates(): void
    {
        foreach (self::PAGES as $role => $page) {
            $id = (int) get_option('gcc_page_' . $role);
            if ($id && $page['template'] !== '' && $page['template'] !== 'front-page' && get_page_template_slug($id) !== $page['template']) {
                update_post_meta($id, '_wp_page_template', $page['template']);
            }
        }
    }

    public static function activate(): void
    {
        PostTypes::register();
        self::pages();

        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) get_option('gcc_page_home'));
        update_option('page_for_posts', (int) get_option('gcc_page_blog'));
        update_option('permalink_structure', '/%postname%/');

        self::menu();
        self::seedProjects();
        self::seedServices();
        self::seedTeam();
        self::seedPosts();
        self::tidyFreshSite();
        update_option('gcc_version', GCC_VERSION);
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }

    /**
     * On a brand-new WordPress, the things nobody wants to be greeted by:
     * "Hello world!", "Sample Page", a UTC clock. Each is touched only if it
     * is still exactly as WordPress made it, so a real site loses nothing.
     */
    private static function tidyFreshSite(): void
    {
        $hello = get_page_by_path('hello-world', OBJECT, 'post');
        if ($hello instanceof \WP_Post && $hello->post_title === 'Hello world!') {
            wp_trash_post($hello->ID);
        }
        $sample = get_page_by_path('sample-page', OBJECT, 'page');
        if ($sample instanceof \WP_Post && $sample->post_title === 'Sample Page') {
            wp_trash_post($sample->ID);
        }
        if (get_option('timezone_string') === '' && (float) get_option('gmt_offset') === 0.0) {
            update_option('timezone_string', 'Africa/Kampala');
        }
        if (get_option('blogdescription') === 'Just another WordPress site') {
            update_option('blogdescription', 'Building inspiring spaces');
        }
        update_option('thumbnail_size_w', 400);
        update_option('thumbnail_size_h', 400);
        update_option('thumbnail_crop', 1);
    }

    private static function menu(string $name = 'Primary'): void
    {
        $existing = wp_get_nav_menu_object($name);
        if ($existing && $name === 'Primary') {
            return;
        }
        $menu = $existing ? (int) $existing->term_id : wp_create_nav_menu($name);
        if (is_wp_error($menu)) {
            return;
        }
        if ($existing && wp_get_nav_menu_items($menu)) {
            $locations = (array) get_theme_mod('nav_menu_locations');
            $locations['primary'] = $menu;
            set_theme_mod('nav_menu_locations', $locations);

            return;
        }
        $add = static function (array $item) use ($menu): void {
            wp_update_nav_menu_item($menu, 0, $item + ['menu-item-status' => 'publish']);
        };
        foreach (['home' => 'Home', 'about' => 'About', 'services' => 'Services'] as $role => $label) {
            $add(['menu-item-title' => $label, 'menu-item-object' => 'page', 'menu-item-object-id' => (int) get_option('gcc_page_' . $role), 'menu-item-type' => 'post_type']);
        }
        $add(['menu-item-title' => 'Work', 'menu-item-type' => 'post_type_archive', 'menu-item-object' => PostTypes::PROJECT]);
        foreach (['blog' => 'Blog', 'contact' => 'Contact'] as $role => $label) {
            $add(['menu-item-title' => $label, 'menu-item-object' => 'page', 'menu-item-object-id' => (int) get_option('gcc_page_' . $role), 'menu-item-type' => 'post_type']);
        }
        $locations = (array) get_theme_mod('nav_menu_locations');
        $locations['primary'] = $menu;
        set_theme_mod('nav_menu_locations', $locations);
    }

    /** @param array<string, string> $meta */
    private static function meta(int $id, array $meta): void
    {
        foreach ($meta as $k => $v) {
            update_post_meta($id, '_gcc_' . $k, $v);
        }
    }

    /** The prototype's projects and categories, created once on a site with no projects. */
    public static function seedProjects(): void
    {
        if (get_posts(['post_type' => PostTypes::PROJECT, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids'])) {
            return;
        }
        $data = include GCC_DIR . 'includes/seed-projects.php';
        $terms = [];
        foreach ($data['categories'] as $slug => $name) {
            $t = term_exists($slug, PostTypes::CATEGORY) ?: wp_insert_term($name, PostTypes::CATEGORY, ['slug' => $slug]);
            if (is_array($t)) {
                $terms[$slug] = (int) $t['term_id'];
            }
        }
        foreach ($data['projects'] as $i => $p) {
            $id = wp_insert_post([
                'post_type' => PostTypes::PROJECT,
                'post_status' => 'publish',
                'post_title' => $p['title'],
                'post_name' => $p['slug'],
                'post_excerpt' => $p['excerpt'],
                'post_content' => '',
                'menu_order' => $i + 1,
            ]);
            if (! $id || is_wp_error($id)) {
                continue;
            }
            if (isset($terms[$p['category']])) {
                wp_set_object_terms($id, [$terms[$p['category']]], PostTypes::CATEGORY);
            }
            self::meta($id, ['ref' => $p['ref'], 'size' => $p['size'], 'specs' => $p['specs'], 'features' => $p['features'], 'confirm' => $p['confirm'], 'featured' => $p['featured'], 'bundled' => $p['bundled'], 'alts' => $p['alts']]);
        }
    }

    /** The ten services, created once on a site with none. */
    public static function seedServices(): void
    {
        if (get_posts(['post_type' => PostTypes::SERVICE, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids'])) {
            return;
        }
        $data = include GCC_DIR . 'includes/seed-services.php';
        $types = ['offices' => 'office', 'homes' => 'home', 'cladding' => 'cladding', 'ablution' => 'ablution', 'labs' => 'lab', 'restaurants' => 'shop', 'valuation' => 'buyback', 'erection' => 'other', 'repairs' => 'cladding', 'freight' => 'freight'];
        foreach ($data['services'] as $i => $s) {
            $id = wp_insert_post([
                'post_type' => PostTypes::SERVICE,
                'post_status' => 'publish',
                'post_title' => $s['title'],
                'post_name' => $s['slug'],
                'post_excerpt' => $s['excerpt'],
                'post_content' => '<p>' . $s['excerpt'] . '</p>',
                'menu_order' => $i + 1,
            ]);
            if (! $id || is_wp_error($id)) {
                continue;
            }
            $home = ['homes' => 'Insulated, Personalised layout, Delivered assembled', 'offices' => 'Self-assembly option, Brand colours, Partitions', 'restaurants' => 'Roller shutter, Melamine walls, Electrical hook-up'];
            self::meta($id, ['features' => $s['features'], 'note' => $s['note'], 'cta' => $s['cta'], 'quote_type' => $types[$s['slug']] ?? '', 'caption' => $s['caption'], 'caption_right' => $s['caption_right'], 'bundled' => $s['bundled'], 'alt' => $s['alt'], 'home' => isset($home[$s['slug']]) ? 'yes' : 'no', 'tags' => $home[$s['slug']] ?? '']);
        }
    }

    public static function seedTeam(): void
    {
        if (get_posts(['post_type' => PostTypes::TEAM, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids'])) {
            return;
        }
        $data = include GCC_DIR . 'includes/seed-services.php';
        foreach ($data['team'] as $i => $t) {
            $id = wp_insert_post(['post_type' => PostTypes::TEAM, 'post_status' => 'publish', 'post_title' => $t['title'], 'post_name' => $t['slug'], 'menu_order' => $i + 1]);
            if ($id && ! is_wp_error($id)) {
                self::meta($id, ['role' => $t['role'], 'bundled' => $t['bundled']]);
            }
        }
    }

    /**
     * The four articles, created once on a site whose only post is (or was)
     * "Hello world!". Bundled photos inside the text become [gcc_image]
     * shortcodes so they keep working wherever the theme is served from.
     */
    public static function seedPosts(): void
    {
        if (get_option('gcc_posts_seeded')) {
            return;
        }
        update_option('gcc_posts_seeded', 1);
        $real = get_posts(['post_type' => 'post', 'post_status' => 'any', 'numberposts' => 2, 'fields' => 'ids', 'exclude' => array_map(static fn ($p) => $p->ID, array_filter([get_page_by_path('hello-world', OBJECT, 'post')]))]);
        if ($real) {
            return;
        }
        $data = include GCC_DIR . 'includes/seed-posts.php';
        $author = (int) (get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID'])[0] ?? 1);
        foreach ($data as $post) {
            $cats = [];
            foreach ($post['categories'] as $name) {
                $t = term_exists($name, 'category') ?: wp_insert_term($name, 'category');
                if (is_array($t)) {
                    $cats[] = (int) $t['term_id'];
                }
            }
            $content = preg_replace('/\{\{IMG:([a-z0-9-]+)\|([^}]*)\}\}/', '[gcc_image name="$1" alt="$2"]', $post['content']) ?? $post['content'];
            $id = wp_insert_post([
                'post_type' => 'post',
                'post_status' => 'publish',
                'post_title' => $post['title'],
                'post_name' => $post['slug'],
                'post_excerpt' => $post['excerpt'],
                'post_content' => $content,
                'post_date' => $post['date'],
                'post_author' => $author,
                'post_category' => $cats ?: [(int) get_option('default_category')],
            ]);
            if ($id && ! is_wp_error($id)) {
                self::meta($id, ['read' => $post['read'], 'bundled' => $post['bundled'], 'cover_alt' => $post['cover_alt'], 'cover_caption' => $post['cover_caption']]);
            }
        }
    }
}
