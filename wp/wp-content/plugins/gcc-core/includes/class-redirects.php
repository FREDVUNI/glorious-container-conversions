<?php
declare(strict_types=1);

namespace GCC;

/**
 * Old addresses from the previous (Avada) site, sent to their new homes
 * with a permanent redirect, so links Google and WhatsApp still hold keep
 * working. The client can add more under Settings → Redirects, one per line
 * as "/old-path/ -> /new-path/".
 */
final class Redirects
{
    /** @var array<string, string> old path => new path (relative to home) or a page role in {braces} */
    private const MAP = [
        '/construction-articles/' => '{blog}',
        '/get-quote/' => '{contact}#quote',
        '/work/' => '/work/',
        '/portfolio_category/commercial/' => '/work/',
        '/portfolio-items/shipping-container-hospital/' => '/work/container-hospital/',
        '/portfolio-items/call-centre-sound-recording-studio/' => '/work/call-centre-studio/',
        '/portfolio-items/internal-sliding-doors/' => '/work/sliding-doors/',
        '/portfolio-items/school-computer-laboratory/' => '/work/cold-storage/',
        '/what-are-shipping-container-homes-and-buildings/' => '/what-are-shipping-container-homes/',
        '/seven-myths-about-shipping-container-homes-debunked/' => '/seven-myths-about-container-homes/',
        '/choosing-perfect-roof-shipping-container-home/' => '/choosing-the-perfect-roof/',
        '/the-ultimate-guide-to-shipping-container-insulation/' => '/ultimate-guide-to-container-insulation/',
        '/category/architecture/' => '/category/architecture/',
    ];

    public static function boot(): void
    {
        add_action('template_redirect', [self::class, 'maybeRedirect'], 0);
    }

    /** @return array<string, string> */
    public static function map(): array
    {
        $map = self::MAP;
        foreach (Settings::lines('redirects') as $line) {
            [$from, $to] = array_map('trim', array_pad(explode('->', $line, 2), 2, ''));
            if ($from !== '' && $to !== '') {
                $map[$from] = $to;
            }
        }

        return $map;
    }

    public static function maybeRedirect(): void
    {
        if (is_admin()) {
            return;
        }
        $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $path = '/' . trim($path, '/') . '/';
        if ($path === '//') {
            $path = '/';
        }
        $map = self::map();
        if (! isset($map[$path])) {
            return;
        }
        $to = $map[$path];
        $target = (string) preg_replace_callback('/\{(\w+)\}/', static fn (array $m): string => gcc_page_url($m[1]), $to);
        if (! str_starts_with($target, 'http')) {
            $target = home_url($target);
        }
        // Never loop: a map entry whose target is its own address is skipped.
        if (rtrim((string) parse_url($target, PHP_URL_PATH), '/') . '/' === $path) {
            return;
        }
        wp_safe_redirect($target, 301);
        exit;
    }
}
