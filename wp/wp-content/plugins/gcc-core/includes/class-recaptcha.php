<?php
declare(strict_types=1);

namespace GCC;

/**
 * Google reCAPTCHA v2 on the quote form. Off until both keys are saved in
 * Settings, so the client can switch it on — and off again if it ever starts
 * blocking real people — without a developer.
 */
final class Recaptcha
{
    public const FIELD = 'g-recaptcha-response';

    public static function enabled(): bool
    {
        return Settings::get('recaptcha_site') !== '' && Settings::get('recaptcha_secret') !== '';
    }

    public static function widget(): string
    {
        if (! self::enabled()) {
            return '';
        }

        return '<div class="field is-full"><div class="g-recaptcha" data-sitekey="' . esc_attr(Settings::get('recaptcha_site')) . '"></div></div>';
    }

    public static function enqueue(): void
    {
        if (self::enabled()) {
            wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, ['strategy' => 'defer']);
        }
    }

    /**
     * True when the check is off, so callers read as "not blocked". Fails
     * open if Google cannot be reached: a network problem at Google must not
     * stop somebody asking for a quote, and the honeypot and rate limit are
     * still doing their work.
     */
    public static function verify(?string $token, string $ip): bool
    {
        if (! self::enabled()) {
            return true;
        }
        if ($token === null || $token === '') {
            return false;
        }
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'timeout' => 5,
            'body' => ['secret' => Settings::get('recaptcha_secret'), 'response' => $token, 'remoteip' => $ip],
        ]);
        if (is_wp_error($response)) {
            error_log('GCC: reCAPTCHA unreachable, letting the submission through: ' . $response->get_error_message());
            return true;
        }
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        return is_array($body) && ! empty($body['success']);
    }
}
