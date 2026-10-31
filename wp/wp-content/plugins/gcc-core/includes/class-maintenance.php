<?php
declare(strict_types=1);

namespace GCC;

/**
 * The holding page. While Settings says "Coming soon", visitors get a short
 * branded page with a 503 (so search engines wait rather than index it),
 * and anyone signed in who can edit sees the real site to work on it.
 */
final class Maintenance
{
    public static function boot(): void
    {
        add_action('template_redirect', [self::class, 'maybeHold'], 1);
    }

    public static function maybeHold(): void
    {
        if (Settings::get('maintenance') !== 'maintenance' || current_user_can('edit_posts') || is_admin()) {
            return;
        }
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if (str_contains($uri, 'wp-login.php') || str_contains($uri, 'admin-post.php')) {
            return;
        }

        status_header(503);
        header('Retry-After: 3600');
        nocache_headers();
        $logo = Settings::get('logo') !== '' ? Settings::get('logo') : get_theme_file_uri('assets/images/logo.png');
        $bg = gcc_photo_url('hospital-02', 1600);
        $name = Settings::get('company_name');
        $phone = Settings::get('phone');
        $email = Settings::get('email');
        $message = Settings::get('maintenance_message');
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">'
            . '<title>' . esc_html($name) . ' — coming soon</title>'
            . '<style>html,body{margin:0;min-height:100%}body{font-family:"Barlow",system-ui,-apple-system,"Segoe UI",sans-serif;color:#f4f1ea;background:#15181b url(' . esc_url($bg) . ') center/cover no-repeat;display:grid;place-items:center;text-align:center;padding:2rem}body::before{content:"";position:fixed;inset:0;background:linear-gradient(180deg,rgba(21,24,27,.78),rgba(21,24,27,.92))}main{position:relative;max-width:36rem}img{width:220px;height:auto;background:#f4f1ea;padding:14px 18px}h1{font-size:clamp(1.8rem,4vw,2.6rem);line-height:1.1;margin:1.25rem 0 .5rem;text-transform:uppercase;letter-spacing:-.01em}p{font-size:1.05rem;color:rgba(244,241,234,.85);line-height:1.6}a{color:#feba0b;font-weight:700;text-decoration:none}.c{margin-top:1.5rem;display:flex;gap:1.5rem;justify-content:center;flex-wrap:wrap}</style></head>'
            . '<body><main><img src="' . esc_url($logo) . '" alt=""><h1>' . esc_html($name) . '</h1><p>' . esc_html($message) . '</p>'
            . '<div class="c">' . ($phone ? '<a href="tel:' . esc_attr(preg_replace('/\\D+/', '', $phone)) . '">' . esc_html($phone) . '</a>' : '') . ($email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '') . '</div>'
            . '</main></body></html>';
        exit;
    }
}
