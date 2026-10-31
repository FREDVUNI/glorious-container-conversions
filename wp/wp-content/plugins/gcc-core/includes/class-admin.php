<?php
declare(strict_types=1);

namespace GCC;

/**
 * The GCC menu: a dashboard, the record types, and settings, in one place
 * rather than scattered down the WordPress sidebar.
 */
final class Admin
{
    public static function boot(): void
    {
        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_menu', [self::class, 'orderMenu'], 999);
        add_action('admin_enqueue_scripts', [self::class, 'assets']);
        add_action('admin_post_gcc_export_enquiries', [self::class, 'exportEnquiries']);
        foreach ([PostTypes::PROJECT, PostTypes::SERVICE, PostTypes::TEAM, PostTypes::TESTIMONIAL, PostTypes::ENQUIRY] as $type) {
            add_filter('manage_' . $type . '_posts_columns', [self::class, 'columns']);
            add_action('manage_' . $type . '_posts_custom_column', [self::class, 'column'], 10, 2);
        }
        add_filter('post_row_actions', [self::class, 'rowActions'], 10, 2);
        add_action('restrict_manage_posts', [self::class, 'statusFilter']);
        add_action('restrict_manage_posts', [self::class, 'exportButton']);
        add_action('pre_get_posts', [self::class, 'applyStatusFilter']);
        add_action('admin_notices', [self::class, 'settingsSavedNotice']);
        add_action('admin_notices', [self::class, 'photosNotice']);
        add_action('after_switch_theme', [Installer::class, 'keepPhotos']);
    }

    /** The site's photographs are missing from both places they can live. */
    public static function photosNotice(): void
    {
        if (! current_user_can('manage_options') || (function_exists('gcc_photo_dir') && gcc_photo_dir() !== null)) {
            return;
        }
        $up = wp_upload_dir();
        echo '<div class="notice notice-error"><p><strong>GCC:</strong> the site\'s photographs are missing, so pages show empty frames. In the host\'s File Manager open <code>' . esc_html(trailingslashit($up['basedir'])) . '</code>, upload <code>glorious-images.zip</code> there and extract it; it creates the <code>gcc-photos</code> folder. That folder is kept when the theme or plugin is replaced.</p></div>';
    }

    public static function menu(): void
    {
        add_menu_page('GCC', 'GCC', 'edit_posts', 'gcc', [self::class, 'dashboard'], 'dashicons-building', 3);
        add_submenu_page('gcc', 'Dashboard', 'Dashboard', 'edit_posts', 'gcc', [self::class, 'dashboard']);
        // The record types register themselves under 'gcc' via show_in_menu.
        add_submenu_page('gcc', 'Settings', 'Settings', 'manage_options', 'gcc-settings', [self::class, 'settings']);
    }

    /** Dashboard first, records in a sensible order, Settings last. */
    public static function orderMenu(): void
    {
        global $submenu;
        if (empty($submenu['gcc'])) {
            return;
        }
        $rank = static fn (array $item): int => match ((string) ($item[2] ?? '')) {
            'gcc' => 0,
            'edit.php?post_type=' . PostTypes::PROJECT => 10,
            'edit.php?post_type=' . PostTypes::SERVICE => 11,
            'edit.php?post_type=' . PostTypes::TEAM => 12,
            'edit.php?post_type=' . PostTypes::TESTIMONIAL => 13,
            'edit.php?post_type=' . PostTypes::ENQUIRY => 14,
            'gcc-settings' => 99,
            default => 50,
        };
        $items = $submenu['gcc'];
        usort($items, static fn (array $a, array $b): int => $rank($a) <=> $rank($b));
        $submenu['gcc'] = $items;
    }

    public static function assets(string $hook): void
    {
        $screen = get_current_screen();
        $ours = str_contains($hook, 'gcc') || ($screen && str_starts_with((string) $screen->post_type, 'gcc_'));
        if ($ours) {
            wp_enqueue_style('gcc-admin', GCC_URL . 'assets/admin.css', ['dashicons'], GCC_VERSION);
            wp_enqueue_media();
            wp_enqueue_script('gcc-admin', GCC_URL . 'assets/admin.js', ['jquery'], GCC_VERSION, true);
        } else {
            wp_add_inline_style('wp-admin', '#adminmenu .toplevel_page_gcc .wp-submenu a{white-space:nowrap}');
        }
    }

    public static function dashboard(): void
    {
        Dashboard::render();
    }

    /** Icon and one-line description for each settings group, keyed by the group title. */
    private const GROUPS = [
        'Site status' => ['dashicons-visibility', 'Show the site, or a holding page while you enter content.'],
        'Notifications' => ['dashicons-email-alt', 'Where the site sends word when a quote request comes in.'],
        'Company' => ['dashicons-building', 'The details shown in the header, footer and contact page.'],
        'Search and sharing' => ['dashicons-share', 'What search engines and shared links show for the site.'],
        'Social media' => ['dashicons-share-alt', 'Links in the footer. Leave one empty to hide it.'],
        'Home page' => ['dashicons-admin-home', 'Every line of copy on the home page.'],
        'Photo captions' => ['dashicons-tag', 'The small labels under the fixed photographs on the home and About pages.'],
        'Hero photos' => ['dashicons-format-gallery', 'The slideshow at the top of the home page: up to four photos with a plate and caption each.'],
        'Hero figures' => ['dashicons-chart-bar', 'The four numbers under the hero. Pick what each one counts and it stays right as records are added; "typed by hand" is for numbers the site does not hold.'],
        'Process' => ['dashicons-randomize', 'The four steps from enquiry to keys, on the home page.'],
        'Unit sizes' => ['dashicons-editor-expand', 'The three standard container sizes at the foot of the Services page.'],
        'Redirects' => ['dashicons-randomize', 'Send old addresses to their new pages so no link ever breaks.'],
        'Spam protection' => ['dashicons-shield', 'Google reCAPTCHA on the quote form.'],
    ];

    public static function settings(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die('Not allowed.');
        }
        $groups = Settings::fields();

        echo '<div class="wrap gcc-wrap"><h1>GCC Settings</h1>';
        add_action('admin_footer', static fn () => print('<p class="gcc-version" style="text-align:right;color:#a7aaad;font-size:11px;margin:12px 0 0">GCC Core ' . esc_html(GCC_VERSION) . ' · theme ' . esc_html(wp_get_theme()->get('Version')) . '</p>'));
        echo '<div class="gcc-head"><div><span class="gcc-eyebrow">Glorious Container Conversions</span><h1>Settings</h1><p>Everything the site reads from one place. Change it here; it shows everywhere.</p></div>'
            . '<a class="button" href="' . esc_url(home_url('/')) . '" target="_blank" rel="noopener"><span class="dashicons dashicons-external"></span> View site</a></div>';

        echo '<form method="post" action="options.php">';
        settings_fields('gcc_settings');
        echo '<div class="gcc-settings"><nav class="gcc-nav" aria-label="Settings sections">';
        foreach (array_keys($groups) as $group) {
            [$icon] = self::GROUPS[$group] ?? ['dashicons-admin-generic', ''];
            echo '<a href="#' . esc_attr(self::panelId($group)) . '" data-panel="' . esc_attr(self::panelId($group)) . '"><span class="dashicons ' . esc_attr($icon) . '"></span>' . esc_html($group) . '</a>';
        }
        echo '</nav><div>';
        foreach ($groups as $group => $fields) {
            [, $blurb] = self::GROUPS[$group] ?? ['', ''];
            echo '<section class="gcc-panel" id="' . esc_attr(self::panelId($group)) . '"><div class="gcc-panel__head"><h2>' . esc_html($group) . '</h2>' . ($blurb ? '<p>' . esc_html($blurb) . '</p>' : '') . '</div><div class="gcc-fields">';
            foreach ($fields as $key => $f) {
                self::field($key, $f);
            }
            echo '</div></section>';
        }
        echo '</div></div>';
        echo '<div class="gcc-savebar"><p>Saving keeps you on the same section. Secret keys are never shown again once saved.</p>';
        submit_button('Save settings', 'primary', 'submit', false);
        echo '</div></form></div>';
    }

    /** @param array{label:string,type:string,default:string,hint?:string,secret?:bool,options?:array<string,string>} $f */
    private static function field(string $key, array $f): void
    {
        $secret = ! empty($f['secret']);
        $stored = Settings::get($key);
        $value = $secret ? '' : $stored;
        $placeholder = $secret ? ($stored !== '' ? '•••••••• — leave blank to keep' : 'Not set') : $f['default'];
        $wide = in_array($f['type'], ['url', 'image', 'textarea'], true) || in_array($key, ['site_description', 'address', 'notify_emails', 'map_query', 'cta_title'], true);
        $name = Settings::OPTION . '[' . esc_attr($key) . ']';

        echo '<div class="gcc-field' . ($wide ? ' gcc-field--wide' : '') . '"><label for="gcc_' . esc_attr($key) . '">' . esc_html($f['label']) . '</label>';
        if ($f['type'] === 'select') {
            echo '<div class="gcc-field__row"><select id="gcc_' . esc_attr($key) . '" name="' . $name . '">';
            foreach ($f['options'] as $v => $label) {
                echo '<option value="' . esc_attr((string) $v) . '"' . selected($stored, (string) $v, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select></div>';
        } elseif ($f['type'] === 'image') {
            $bundled = Settings::BUNDLED[$key] ?? '';
            self::imageField($key, $stored, $bundled !== '' ? gcc_photo_url($bundled, 800) : '');
        } elseif ($f['type'] === 'textarea') {
            echo '<textarea id="gcc_' . esc_attr($key) . '" name="' . $name . '" rows="' . (substr_count($f['default'], "\n") > 3 ? 8 : 3) . '" placeholder="' . esc_attr($placeholder) . '">' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<div class="gcc-field__row"><input id="gcc_' . esc_attr($key) . '" name="' . $name . '" type="' . esc_attr($f['type']) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '"' . ($secret ? ' autocomplete="new-password"' : '') . '>';
            if ($secret) {
                echo $stored !== '' ? '<span class="gcc-set"><span class="dashicons dashicons-yes"></span>Set</span>' : '<span class="gcc-set is-off">Not set</span>';
            }
            echo '</div>';
        }
        if (! empty($f['hint'])) {
            echo '<p class="description">' . esc_html($f['hint']) . '</p>';
        }
        echo '</div>';
    }

    /** An image picker on the Media Library: a preview, Choose and Remove. What is stored is the file's URL. */
    private static function imageField(string $key, string $url, string $fallback = ''): void
    {
        $shown = $url !== '' ? $url : $fallback;
        echo '<div class="gcc-image' . ($url !== '' ? ' has-image' : '') . '" data-image-field data-fallback="' . esc_attr($fallback) . '">'
            . '<input type="hidden" id="gcc_' . esc_attr($key) . '" name="' . Settings::OPTION . '[' . esc_attr($key) . ']" value="' . esc_attr($url) . '" data-image-input>'
            . '<div class="gcc-image__preview" data-image-preview>' . ($shown !== '' ? '<img src="' . esc_url($shown) . '" alt="">' : '<span class="dashicons dashicons-format-image"></span>') . '</div>'
            . '<div class="gcc-image__actions">'
            . '<button type="button" class="button" data-image-choose><span class="dashicons dashicons-upload"></span> ' . ($url !== '' ? 'Change image' : 'Choose image') . '</button>'
            . '<button type="button" class="button-link gcc-image__remove" data-image-remove' . ($url === '' ? ' hidden' : '') . '>Remove</button>'
            . '<p class="gcc-image__name" data-image-name>' . esc_html($url !== '' ? wp_basename($url) : ($fallback !== '' ? 'Nothing chosen — the bundled photo is used.' : 'Nothing chosen.')) . '</p>'
            . '</div></div>';
    }

    private static function panelId(string $group): string
    {
        return 'gcc-' . sanitize_title($group);
    }

    /** Every enquiry as a CSV, for the spreadsheet or CRM of the day. */
    public static function exportEnquiries(): void
    {
        if (! current_user_can('edit_posts') || ! check_admin_referer('gcc_export_enquiries')) {
            wp_die('Not allowed.');
        }
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="gcc-enquiries-' . gmdate('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Received', 'Name', 'Email', 'Phone', 'Project type', 'Size', 'Location', 'Timeline', 'Came from', 'Message', 'Status', 'Notes']);
        foreach (get_posts(['post_type' => PostTypes::ENQUIRY, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC']) as $p) {
            $m = static fn (string $k): string => MetaBoxes::get($p->ID, $k);
            fputcsv($out, [get_the_date('Y-m-d H:i', $p), $m('sender_name'), $m('email'), $m('phone'), $m('project_type'), $m('size'), $m('location'), $m('timeline'), $m('about'), $m('message'), PostTypes::ENQUIRY_STATUSES[$m('status')] ?? $m('status'), $m('notes')]);
        }
        fclose($out);
        exit;
    }

    public static function exportButton(string $type): void
    {
        if ($type === PostTypes::ENQUIRY) {
            echo '<a class="button" style="margin-left:6px" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=gcc_export_enquiries'), 'gcc_export_enquiries')) . '">Export CSV</a>';
        }
    }

    public static function settingsSavedNotice(): void
    {
        settings_errors('gcc_settings');
    }

    public static function columns(array $columns): array
    {
        $type = get_current_screen()->post_type ?? '';
        $extra = match ($type) {
            PostTypes::PROJECT => ['gc_ref' => 'Reference', 'gc_photos' => 'Photos', 'gc_featured' => 'Home page', 'gc_confirm' => 'To confirm'],
            PostTypes::SERVICE => ['gc_order' => 'Order', 'gc_cta' => 'Link text'],
            PostTypes::TEAM => ['gc_role' => 'Role', 'gc_order' => 'Order'],
            PostTypes::TESTIMONIAL => ['gc_role' => 'Who', 'gc_project' => 'Project'],
            PostTypes::ENQUIRY => ['gc_project_type' => 'Project', 'gc_location' => 'Location', 'gc_email' => 'Email', 'gc_phone' => 'Phone', 'gc_status' => 'Status'],
            default => [],
        };
        $date = $columns['date'] ?? 'Date';
        unset($columns['date']);

        return $columns + $extra + ['date' => $date];
    }

    public static function column(string $column, int $id): void
    {
        if (! str_starts_with($column, 'gc_')) {
            return;
        }
        $key = substr($column, 3);
        $value = MetaBoxes::get($id, $key);
        echo match ($key) {
            'status' => esc_html(PostTypes::ENQUIRY_STATUSES[$value] ?? ($value ?: '—')),
            'order' => esc_html((string) get_post_field('menu_order', $id)),
            'photos' => esc_html((string) count(function_exists('gcc_project_images') ? gcc_project_images($id) : [])),
            'featured' => $value !== '' ? 'Slot ' . esc_html($value) : '—',
            'confirm' => $value === 'yes' ? '<span style="color:#b32d2e">Yes</span>' : '—',
            'project' => $value !== '' ? esc_html(get_the_title((int) $value)) : '—',
            default => $value !== '' ? esc_html($value) : '—',
        };
    }

    /** Submissions cannot be edited into something else, so "Quick Edit" goes. */
    public static function rowActions(array $actions, \WP_Post $post): array
    {
        if ($post->post_type === PostTypes::ENQUIRY) {
            unset($actions['inline hide-if-no-js'], $actions['view']);
            $actions['edit'] = '<a href="' . esc_url((string) get_edit_post_link($post->ID)) . '">Open</a>';
        }

        return $actions;
    }

    public static function statusFilter(string $type): void
    {
        if ($type !== PostTypes::ENQUIRY) {
            return;
        }
        $current = isset($_GET['gcc_status']) ? sanitize_key((string) $_GET['gcc_status']) : '';
        echo '<select name="gcc_status"><option value="">All statuses</option>';
        foreach (PostTypes::ENQUIRY_STATUSES as $k => $label) {
            echo '<option value="' . esc_attr($k) . '"' . selected($current, $k, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public static function applyStatusFilter(\WP_Query $q): void
    {
        if (! is_admin() || ! $q->is_main_query() || empty($_GET['gcc_status'])) {
            return;
        }
        $q->set('meta_query', [['key' => '_gcc_status', 'value' => sanitize_key((string) $_GET['gcc_status'])]]);
    }
}
