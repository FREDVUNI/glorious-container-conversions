<?php
/**
 * Global helpers the theme's templates call.
 *
 * Each is guarded: the theme ships the same names as fallbacks so it can
 * render with the plugin off, and on the very request that activates the
 * plugin the theme's functions.php has already loaded. Without the guards
 * that request fatals with "Cannot redeclare gcc_setting()" and the plugin
 * never activates.
 */
declare(strict_types=1);

if (! function_exists('gcc_setting')) {
    function gcc_setting(string $key, string $default = ''): string
    {
        return GCC\Settings::get($key, $default);
    }
}

/** Digits only, for tel: and wa.me links. */
if (! function_exists('gcc_digits')) {
    function gcc_digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}

/** One setting typed one-per-line, as a list with blanks dropped. */
if (! function_exists('gcc_lines')) {
    /** @return list<string> */
    function gcc_lines(string $key): array
    {
        return GCC\Settings::lines($key);
    }
}

/**
 * The URL of a page the installer created, by its role, so templates never
 * hard-code a slug the client might later rename.
 */
if (! function_exists('gcc_page_url')) {
    function gcc_page_url(string $role): string
    {
        $id = (int) get_option('gcc_page_' . $role);

        return $id ? (string) get_permalink($id) : home_url('/');
    }
}

/** Post meta set by the plugin. */
if (! function_exists('gcc_meta')) {
    function gcc_meta(int $post_id, string $key, string $default = ''): string
    {
        return GCC\MetaBoxes::get($post_id, $key, $default);
    }
}

/** Inline SVG icons used across the templates. */
if (! function_exists('gcc_icon')) {
    function gcc_icon(string $name, string $class = ''): string
    {
        $paths = [
            'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
            'arrow-l'  => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
            'up'       => '<path d="M12 19V5M6 11l6-6 6 6"/>',
            'external' => '<path d="M7 17 17 7M8 7h9v9"/>',
            'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
            'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
            'mail'     => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 7 9 6 9-6"/>',
            'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16v.5"/>',
            'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
            'warn'     => '<path d="M12 8v5M12 16.5v.5M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0z"/>',
            'list'     => '<path d="M4 7h16M4 12h16M4 17h10"/>',
            'check'    => '<path d="m5 12 4 4 10-10"/>',
        ];

        return '<svg' . ($class !== '' ? ' class="' . esc_attr($class) . '"' : '') . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
    }
}

/** The social links that are actually filled in, in a fixed order. */
if (! function_exists('gcc_socials')) {
    /** @return list<array{url:string, label:string, svg:string}> */
    function gcc_socials(): array
    {
        $networks = [
            'facebook'  => ['label' => 'Facebook',  'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 22v-8h2.7l.4-3.2h-3.1V8.8c0-.9.3-1.6 1.6-1.6h1.7V4.3c-.3 0-1.3-.1-2.5-.1-2.5 0-4.1 1.5-4.1 4.2v2.4H7.4V14h2.8v8z"/></svg>'],
            'x'         => ['label' => 'X (Twitter)', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.5 3h3l-6.8 7.8L21.7 21h-6.2l-4.9-6.3L5 21H2l7.3-8.3L1.6 3h6.4l4.4 5.8zm-1.1 16.2h1.7L6.7 4.7H4.9z"/></svg>'],
            'youtube'   => ['label' => 'YouTube',   'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8c1.6.4 7.8.4 7.8.4s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15V9l5.2 3z"/></svg>'],
            'instagram' => ['label' => 'Instagram', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>'],
            'linkedin'  => ['label' => 'LinkedIn',  'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.5 8.5h-3V21h3zM5 7a1.8 1.8 0 1 0 0-3.6A1.8 1.8 0 0 0 5 7zm16 14h-3v-6.2c0-1.5-.5-2.5-1.9-2.5-1 0-1.6.7-1.9 1.4-.1.2-.1.6-.1.9V21h-3V8.5h3v1.7c.4-.6 1.2-1.5 2.9-1.5 2.1 0 3.7 1.4 3.7 4.4z"/></svg>'],
            'tiktok'    => ['label' => 'TikTok',    'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 3h-3v12.2a2.6 2.6 0 1 1-2.2-2.6V9.5A5.8 5.8 0 1 0 16 15.2V9.6a6.9 6.9 0 0 0 4 1.3V7.8A3.9 3.9 0 0 1 16 3.9V3z"/></svg>'],
        ];

        $out = [];
        foreach ($networks as $key => $net) {
            $url = gcc_setting('social_' . $key);
            if ($url !== '') {
                $out[] = ['url' => $url, 'label' => $net['label'], 'svg' => $net['svg']];
            }
        }
        $wa = gcc_digits(gcc_setting('whatsapp'));
        if ($wa !== '') {
            $out[] = ['url' => 'https://wa.me/' . $wa, 'label' => 'WhatsApp', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 1.8a8.2 8.2 0 1 1-4.2 15.3l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 0 1 12 3.8zm-3.3 4.3c-.2 0-.5 0-.7.3-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 2 3.2 5 4.4 2.4 1 2.9.8 3.4.7.5 0 1.7-.7 1.9-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3l-2-1c-.3-.1-.5-.1-.7.2l-.9 1.1c-.2.2-.3.2-.6.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.3-.4.8-1.3.1-.2 0-.4 0-.5L10.4 9c-.2-.6-.5-.5-.7-.5z"/></svg>'];
        }
        $email = gcc_setting('email');
        if ($email !== '') {
            $out[] = ['url' => 'mailto:' . $email, 'label' => 'Email', 'svg' => gcc_icon('mail')];
        }

        return $out;
    }
}

if (! function_exists('gcc_stats')) {
    /**
     * The four hero figures, resolved: live counts where a source is chosen,
     * the typed number otherwise.
     *
     * @return list<array{value:string, suffix:string, label:string}>
     */
    function gcc_stats(): array
    {
        return \GCC\Settings::stats();
    }
}

if (! function_exists('gcc_projects')) {
    /**
     * Published projects, newest first unless a menu order is set.
     *
     * @return list<\WP_Post>
     */
    function gcc_projects(int $limit = -1, array $args = []): array
    {
        return get_posts($args + ['post_type' => \GCC\PostTypes::PROJECT, 'post_status' => 'publish', 'numberposts' => $limit, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
    }
}

if (! function_exists('gcc_featured_projects')) {
    /**
     * The three projects marked for the home page, in slot order; the three
     * newest when nothing is marked.
     *
     * @return list<\WP_Post>
     */
    function gcc_featured_projects(): array
    {
        $marked = get_posts(['post_type' => \GCC\PostTypes::PROJECT, 'post_status' => 'publish', 'numberposts' => 3, 'meta_key' => '_gcc_featured', 'meta_value' => ['1', '2', '3'], 'meta_compare' => 'IN', 'orderby' => 'meta_value', 'order' => 'ASC']);

        return $marked ?: gcc_projects(3);
    }
}

if (! function_exists('gcc_services')) {
    /** @return list<\WP_Post> */
    function gcc_services(): array
    {
        return get_posts(['post_type' => \GCC\PostTypes::SERVICE, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC']]);
    }
}

if (! function_exists('gcc_team')) {
    /** @return list<\WP_Post> */
    function gcc_team(): array
    {
        return get_posts(['post_type' => \GCC\PostTypes::TEAM, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC']]);
    }
}

if (! function_exists('gcc_testimonials')) {
    /** @return list<\WP_Post> */
    function gcc_testimonials(): array
    {
        return get_posts(['post_type' => \GCC\PostTypes::TESTIMONIAL, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
    }
}

if (! function_exists('gcc_meta_lines')) {
    /** A textarea meta field, one item per line, blanks dropped. @return list<string> */
    function gcc_meta_lines(int $post_id, string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', gcc_meta($post_id, $key)) ?: [])));
    }
}

if (! function_exists('gcc_meta_pairs')) {
    /** "Key: value" lines as an ordered map. @return array<string, string> */
    function gcc_meta_pairs(int $post_id, string $key): array
    {
        $out = [];
        foreach (gcc_meta_lines($post_id, $key) as $line) {
            [$k, $v] = array_pad(explode(':', $line, 2), 2, '');
            if (trim($k) !== '') {
                $out[trim($k)] = trim($v);
            }
        }

        return $out;
    }
}

if (! function_exists('gcc_project_images')) {
    /**
     * A project's photographs: the gallery chosen in wp-admin when there is
     * one, the bundled prototype photos otherwise. Each item is either an
     * attachment ID (int) or a bundled base name (string); the theme's
     * gcc_media() renders both.
     *
     * @return list<array{src:int|string, alt:string}>
     */
    function gcc_project_images(int $post_id): array
    {
        $ids = array_filter(array_map('absint', explode(',', gcc_meta($post_id, 'gallery'))));
        $out = [];
        foreach ($ids as $id) {
            if (wp_attachment_is_image($id)) {
                $out[] = ['src' => $id, 'alt' => (string) get_post_meta($id, '_wp_attachment_image_alt', true)];
            }
        }
        if ($out) {
            return $out;
        }
        if (has_post_thumbnail($post_id)) {
            $t = (int) get_post_thumbnail_id($post_id);
            $out[] = ['src' => $t, 'alt' => (string) get_post_meta($t, '_wp_attachment_image_alt', true)];
        }
        $names = array_values(array_filter(array_map('trim', explode(',', gcc_meta($post_id, 'bundled')))));
        $alts = gcc_meta_lines($post_id, 'alts');
        foreach ($names as $i => $n) {
            $out[] = ['src' => $n, 'alt' => $alts[$i] ?? get_the_title($post_id)];
        }

        return $out;
    }
}
