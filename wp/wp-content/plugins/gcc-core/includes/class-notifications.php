<?php
declare(strict_types=1);

namespace GCC;

/**
 * Tells the office when an enquiry arrives, and the sender that it did.
 * Plain-text email, so it reads on a phone and never lands in spam for
 * having a picture in it.
 */
final class Notifications
{
    public static function enquiry(int $id): void
    {
        $m = static fn (string $k): string => MetaBoxes::get($id, $k);

        $subject = 'Quote request: ' . $m('project_type') . ' — ' . $m('sender_name');
        $body = "Someone has asked for a quote through the website.\n\n"
            . 'Name:      ' . $m('sender_name') . "\n"
            . 'Email:     ' . $m('email') . "\n"
            . 'Phone:     ' . ($m('phone') ?: '—') . "\n"
            . 'Project:   ' . $m('project_type') . "\n"
            . 'Size:      ' . ($m('size') ?: '—') . "\n"
            . 'Location:  ' . $m('location') . "\n"
            . 'Timeline:  ' . ($m('timeline') ?: '—') . "\n"
            . ($m('about') !== '' ? 'Came from: ' . $m('about') . "\n" : '')
            . "\nThey wrote:\n\n" . $m('message') . "\n\n"
            . 'Open it: ' . admin_url('post.php?post=' . $id . '&action=edit') . "\n\n"
            . 'Replying to this email writes straight back to them.';

        $headers = ['From: ' . Settings::get('from_name') . ' <' . get_option('admin_email') . '>'];
        if (is_email($m('email'))) {
            $headers[] = 'Reply-To: ' . $m('sender_name') . ' <' . $m('email') . '>';
        }
        wp_mail(Settings::notifyEmails(), $subject, $body, $headers);
    }

    /** To the sender, the moment they send: what happens next. */
    public static function acknowledge(int $id): void
    {
        $m = static fn (string $k): string => MetaBoxes::get($id, $k);
        if (! is_email($m('email'))) {
            return;
        }
        $name = Settings::get('short_name');
        $body = 'Dear ' . $m('sender_name') . ",\n\n"
            . 'Thank you for your request about a ' . strtolower($m('project_type')) . ($m('location') !== '' ? ' in ' . $m('location') : '') . ".\n\n"
            . "We have it, and one of the team will be in touch — with CAD drawings of a proposed layout within 48 hours.\n\n"
            . "If anything changes in the meantime, just reply to this email.\n\n"
            . $name . "\n" . Settings::get('phone') . ' · ' . Settings::get('email') . "\n" . Settings::get('address');

        $headers = ['From: ' . Settings::get('from_name') . ' <' . get_option('admin_email') . '>'];
        $office = Settings::notifyEmails();
        if ($office) {
            $headers[] = 'Reply-To: ' . $name . ' <' . $office[0] . '>';
        }
        wp_mail($m('email'), 'We have your quote request', $body, $headers);
    }
}
