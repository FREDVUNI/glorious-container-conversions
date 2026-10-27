<?php
declare(strict_types=1);

namespace GCC;

/**
 * The quote-request form.
 *
 * Posts to admin-post.php, is checked on the server (the browser's checks
 * are a convenience, not a defence), and comes back to the page it left
 * with either a thank-you or the errors and what the visitor typed.
 *
 * Every submission passes four gates: a nonce, a honeypot field bots fill
 * and people never see, a per-address rate limit, and reCAPTCHA when the
 * keys are set. None of them ever loses a genuine visitor their typed text.
 */
final class Forms
{
    private const RATE_LIMIT = 6;      // submissions
    private const RATE_WINDOW = 60;    // seconds

    public static function boot(): void
    {
        add_action('admin_post_nopriv_gcc_quote', [self::class, 'handle']);
        add_action('admin_post_gcc_quote', [self::class, 'handle']);
    }

    /**
     * Result of the last submission, read by the theme. Read once per
     * request and memoised; the transient is deleted on the first read so a
     * refresh does not show a stale message.
     */
    public static function flash(): ?array
    {
        static $flash = false;
        if ($flash !== false) {
            return $flash;
        }
        $token = isset($_GET['gcc']) ? sanitize_key((string) $_GET['gcc']) : '';
        $data = $token !== '' ? get_transient('gcc_flash_' . $token) : false;
        if ($token !== '') {
            delete_transient('gcc_flash_' . $token);
        }

        return $flash = is_array($data) ? $data : null;
    }

    /** What the visitor typed, on a rejected submission, so nothing is retyped. */
    public static function old(string $key, string $default = ''): string
    {
        return (string) (self::flash()['old'][$key] ?? $default);
    }

    /** The message for one field, if that field was rejected. */
    public static function error(string $key): string
    {
        return (string) (self::flash()['errors'][$key] ?? '');
    }

    /**
     * What the form should arrive pre-filled with: ?type=home from a service
     * link, ?project=<id> from a project page. Nothing unknown gets through.
     *
     * @return array{type:string, project:int, about:string}
     */
    public static function preset(): array
    {
        $type = sanitize_key((string) ($_GET['type'] ?? ''));
        $project = absint($_GET['project'] ?? 0);
        $p = $project ? get_post($project) : null;
        $about = $p instanceof \WP_Post && $p->post_type === PostTypes::PROJECT && $p->post_status === 'publish' ? $p->post_title : '';

        return ['type' => isset(Settings::PROJECT_TYPES[$type]) ? $type : '', 'project' => $about !== '' ? $project : 0, 'about' => $about];
    }

    public static function handle(): void
    {
        $back = isset($_POST['_back']) ? esc_url_raw(wp_unslash((string) $_POST['_back'])) : home_url('/');
        $back = wp_validate_redirect($back, home_url('/'));
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field((string) $_SERVER['REMOTE_ADDR']) : '0.0.0.0';
        $input = array_map(static fn ($v) => is_string($v) ? trim(wp_unslash($v)) : '', $_POST);

        if (! isset($_POST['_gcc_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_gcc_nonce'])), 'gcc_quote')) {
            self::back($back, ['ok' => false, 'message' => 'That page had sat open too long. Please send again.', 'old' => $input]);
        }
        // Honeypot. Answered as if it worked, so a bot learns nothing.
        if (! empty($input['website'])) {
            self::back($back, ['ok' => true, 'message' => self::thanks()]);
        }
        if (self::throttled($ip)) {
            self::back($back, ['ok' => false, 'message' => 'That is a lot of requests at once. Please wait a minute and try again.', 'old' => $input]);
        }

        $errors = self::validate($input);
        if (! Recaptcha::verify($input[Recaptcha::FIELD] ?? null, $ip)) {
            $errors['recaptcha'] = 'Please confirm you are not a robot, then send again.';
        }
        if ($errors !== []) {
            self::back($back, ['ok' => false, 'message' => 'Please check the highlighted fields.', 'errors' => $errors, 'old' => $input]);
        }

        $id = self::store($input, $ip);

        // Saved first, notified second: a mail problem must never lose what
        // the visitor sent or show them an error.
        try {
            Notifications::enquiry($id);
            if (Settings::get('ack_sender') === 'yes') {
                Notifications::acknowledge($id);
            }
        } catch (\Throwable $e) {
            error_log('GCC: notification failed for #' . $id . ': ' . $e->getMessage());
        }

        self::back($back, ['ok' => true, 'message' => self::thanks(), 'summary' => self::summary($id)]);
    }

    private static function thanks(): string
    {
        return 'Thank you. Your request has reached the team; expect drawings within 48 hours.';
    }

    /** @return array<string, string> What the thank-you card shows back. */
    private static function summary(int $id): array
    {
        $m = static fn (string $k): string => MetaBoxes::get($id, $k);
        $out = ['Name' => $m('sender_name'), 'Email' => $m('email')];
        if ($m('phone') !== '') {
            $out['Phone'] = $m('phone');
        }
        $out += ['Project' => $m('project_type'), 'Size' => $m('size') ?: 'Not specified', 'Location' => $m('location'), 'Timeline' => $m('timeline') ?: 'Not specified'];

        return $out;
    }

    private static function back(string $url, array $flash): never
    {
        $token = wp_generate_password(12, false);
        set_transient('gcc_flash_' . $token, $flash, 120);
        wp_safe_redirect(add_query_arg('gcc', $token, $url) . '#quote');
        exit;
    }

    private static function throttled(string $ip): bool
    {
        $key = 'gcc_rl_' . md5($ip);
        $count = (int) get_transient($key);
        set_transient($key, $count + 1, self::RATE_WINDOW);

        return $count >= self::RATE_LIMIT;
    }

    /**
     * Phone numbers from seven countries come through this form, so the
     * check is loose: a plus sign or digit first, then 7–15 digits in all.
     */
    private static function phone(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '256' . substr($digits, 1); // a Ugandan number typed locally
        }

        return strlen($digits) >= 7 && strlen($digits) <= 15 ? '+' . $digits : null;
    }

    /** @return array<string, string> */
    private static function validate(array $in): array
    {
        $e = [];
        $name = $in['name'] ?? '';
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120 || preg_match('/(.)\1{4,}/u', $name)) {
            $e['name'] = 'Please enter your name.';
        }
        if (! is_email($in['email'] ?? '')) {
            $e['email'] = 'Enter a valid email address, e.g. name@example.com.';
        }
        if (($in['phone'] ?? '') !== '' && self::phone($in['phone']) === null) {
            $e['phone'] = 'Enter a phone number with the country code, e.g. +256 7xx xxx xxx.';
        }
        if (! isset(Settings::PROJECT_TYPES[$in['project_type'] ?? ''])) {
            $e['project_type'] = 'Choose the type of project.';
        }
        if (($in['size'] ?? '') !== '' && ! isset(Settings::SIZES[$in['size']])) {
            $e['size'] = 'Choose one of the sizes listed.';
        }
        if (mb_strlen($in['location'] ?? '') < 2 || mb_strlen($in['location'] ?? '') > 160) {
            $e['location'] = 'Tell us where the unit will be installed.';
        }
        if (($in['timeline'] ?? '') !== '' && ! in_array($in['timeline'], Settings::TIMELINES, true)) {
            $e['timeline'] = 'Choose one of the options listed.';
        }
        $msg = $in['message'] ?? '';
        if (mb_strlen($msg) < 20 || mb_strlen($msg) > 3000 || preg_match('/(.)\1{9,}/u', $msg)) {
            $e['message'] = 'A few sentences help us quote accurately (20 characters minimum).';
        }

        return $e;
    }

    private static function store(array $in, string $ip): int
    {
        $type = Settings::PROJECT_TYPES[$in['project_type']];
        $id = wp_insert_post([
            'post_type' => PostTypes::ENQUIRY,
            'post_status' => 'publish',
            'post_title' => sanitize_text_field($in['name']) . ' — ' . $type,
        ], true);
        if (is_wp_error($id)) {
            wp_die('Could not save your request. Please try again.');
        }
        $project = absint($in['project'] ?? 0);
        $p = $project ? get_post($project) : null;
        $about = $p instanceof \WP_Post && $p->post_type === PostTypes::PROJECT ? $p->post_title . ' (#' . $p->ID . ')' : '';

        foreach ([
            'sender_name' => sanitize_text_field($in['name']),
            'email' => sanitize_email($in['email']),
            'phone' => (string) (($in['phone'] ?? '') !== '' ? self::phone($in['phone']) : ''),
            'project_type' => $type,
            'size' => sanitize_text_field($in['size'] ?? ''),
            'location' => sanitize_text_field($in['location']),
            'timeline' => sanitize_text_field($in['timeline'] ?? ''),
            'about' => $about,
            'message' => sanitize_textarea_field($in['message']),
            'status' => 'new',
            'ip' => $ip,
        ] as $k => $v) {
            update_post_meta($id, '_gcc_' . $k, $v);
        }

        return $id;
    }
}
