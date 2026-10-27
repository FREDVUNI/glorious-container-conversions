<?php
declare(strict_types=1);

namespace GCC;

/**
 * The GCC dashboard: a greeting, tiles for the numbers that matter, a
 * twelve-month bar chart of enquiries, a "needs attention" list, and a
 * ring for where enquiries stand.
 *
 * All HTML and CSS — no chart library. The admin is on shared hosting and
 * the person reading it is on a phone as often as not.
 */
final class Dashboard
{
    public static function render(): void
    {
        $user = wp_get_current_user();
        $first = explode(' ', trim($user->display_name ?: $user->user_login))[0];
        $hour = (int) current_time('G');
        $greeting = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');

        echo '<div class="wrap gcc-wrap"><h1>GCC</h1>';
        echo '<div class="gcc-head"><div><span class="gcc-eyebrow">Glorious Container Conversions</span>'
            . '<h1>Good ' . esc_html($greeting) . ', ' . esc_html($first) . '</h1><p>' . esc_html(date_i18n('l, j F Y')) . '</p></div>'
            . '<div class="gcc-actions">'
            . '<a class="button" href="' . esc_url(admin_url('post-new.php?post_type=' . PostTypes::PROJECT)) . '">New project</a>'
            . '<a class="button" href="' . esc_url(admin_url('post-new.php')) . '">Write an article</a>'
            . '<a class="button" href="' . esc_url(admin_url('edit.php?post_type=' . PostTypes::ENQUIRY)) . '">Enquiries</a>'
            . '<a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=gcc-settings')) . '">Settings</a>'
            . '</div></div>';

        echo '<div class="gcc-tiles">';
        foreach (self::tiles() as [$label, $value, $note, $link, $tone]) {
            echo '<a class="gcc-tile gcc-tile--' . esc_attr($tone) . '" href="' . esc_url(admin_url($link)) . '"><span>' . esc_html($label) . '</span><b>' . esc_html((string) $value) . '</b><small>' . esc_html($note) . '</small></a>';
        }
        echo '</div>';

        echo '<div class="gcc-grid gcc-grid--main">';
        self::chart(self::trend());
        self::attentionCard(self::attention());
        echo '</div>';

        echo '<div class="gcc-grid gcc-grid--rings">';
        self::ringCard(self::ring());
        self::latestCard();
        self::confirmCard();
        echo '</div></div>';
    }

    private static function count(string $type, ?string $status = null, array $extra = []): int
    {
        return (int) (new \WP_Query($extra + [
            'post_type' => $type, 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 1, 'no_found_rows' => false,
            'meta_query' => $status ? [['key' => '_gcc_status', 'value' => $status]] : [],
        ]))->found_posts;
    }

    /** @return list<array{0:string,1:string|int,2:string,3:string,4:string}> */
    private static function tiles(): array
    {
        $new = self::count(PostTypes::ENQUIRY, 'new');
        $month = self::count(PostTypes::ENQUIRY, null, ['date_query' => [['after' => current_time('Y-m-01')]]]);
        $won = self::count(PostTypes::ENQUIRY, 'won');

        return [
            ['New enquiries', $new, $new ? 'waiting for a reply' : 'nothing waiting', 'edit.php?post_type=' . PostTypes::ENQUIRY . '&gcc_status=new', $new ? 'gold' : 'plain'],
            ['This month', $month, 'quote requests received', 'edit.php?post_type=' . PostTypes::ENQUIRY, 'green'],
            ['Won', $won, 'enquiries that became jobs', 'edit.php?post_type=' . PostTypes::ENQUIRY . '&gcc_status=won', 'green'],
            ['Projects', self::count(PostTypes::PROJECT), 'on the Work page', 'edit.php?post_type=' . PostTypes::PROJECT, 'plain'],
            ['Services', self::count(PostTypes::SERVICE), 'listed', 'edit.php?post_type=' . PostTypes::SERVICE, 'plain'],
            ['Articles', (int) wp_count_posts()->publish, 'published on the blog', 'edit.php', 'plain'],
            ['Team', self::count(PostTypes::TEAM), 'on the About page', 'edit.php?post_type=' . PostTypes::TEAM, 'plain'],
            ['Testimonials', self::count(PostTypes::TESTIMONIAL), 'published', 'edit.php?post_type=' . PostTypes::TESTIMONIAL, 'plain'],
        ];
    }

    /**
     * Twelve months of enquiries: won against everything else, by the month
     * they arrived.
     *
     * @return array{months: list<array{label:string, full:string, won:int, other:int}>, max:int, any:bool}
     */
    private static function trend(): array
    {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(p.post_date, '%%Y-%%m') AS ym, COALESCE(s.meta_value, 'new') AS status, COUNT(*) AS n
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = '_gcc_status'
             WHERE p.post_type = %s AND p.post_status = 'publish' AND p.post_date >= %s
             GROUP BY ym, status",
            PostTypes::ENQUIRY, gmdate('Y-m-01', strtotime('-11 months'))
        ), ARRAY_A);
        $by = [];
        foreach ($rows as $r) {
            $bucket = $r['status'] === 'won' ? 'won' : 'other';
            $by[$r['ym']][$bucket] = ($by[$r['ym']][$bucket] ?? 0) + (int) $r['n'];
        }
        $months = [];
        $max = 0;
        for ($i = 11; $i >= 0; $i--) {
            $t = strtotime("-{$i} months", strtotime(gmdate('Y-m-01')));
            $ym = gmdate('Y-m', $t);
            $w = (int) ($by[$ym]['won'] ?? 0);
            $o = (int) ($by[$ym]['other'] ?? 0);
            $max = max($max, $w + $o);
            $months[] = ['label' => gmdate('M', $t), 'full' => gmdate('F Y', $t), 'won' => $w, 'other' => $o];
        }

        return ['months' => $months, 'max' => $max, 'any' => $max > 0];
    }

    /** @return list<array{count:int, label:string, detail:string, link:string, tone:string}> */
    private static function attention(): array
    {
        $items = [];
        $n = self::count(PostTypes::ENQUIRY, 'new');
        if ($n) {
            $items[] = ['count' => $n, 'label' => 'new enquir' . ($n === 1 ? 'y' : 'ies'), 'detail' => 'not yet contacted', 'link' => 'edit.php?post_type=' . PostTypes::ENQUIRY . '&gcc_status=new', 'tone' => 'gold'];
        }
        $n = self::count(PostTypes::ENQUIRY, 'new', ['date_query' => [['before' => '2 days ago']]]);
        if ($n) {
            $items[] = ['count' => $n, 'label' => 'enquir' . ($n === 1 ? 'y' : 'ies'), 'detail' => 'waiting more than two days — the 48-hour promise is passing', 'link' => 'edit.php?post_type=' . PostTypes::ENQUIRY . '&gcc_status=new', 'tone' => 'danger'];
        }
        $n = self::count(PostTypes::ENQUIRY, 'quoted');
        if ($n) {
            $items[] = ['count' => $n, 'label' => 'quote' . ($n === 1 ? '' : 's'), 'detail' => 'sent and awaiting an answer', 'link' => 'edit.php?post_type=' . PostTypes::ENQUIRY . '&gcc_status=quoted', 'tone' => 'plain'];
        }
        $n = self::count(PostTypes::PROJECT, null, ['meta_query' => [['key' => '_gcc_confirm', 'value' => 'yes']]]);
        if ($n) {
            $items[] = ['count' => $n, 'label' => 'project' . ($n === 1 ? '' : 's'), 'detail' => 'still marked "details to be confirmed"', 'link' => 'edit.php?post_type=' . PostTypes::PROJECT, 'tone' => 'gold'];
        }
        if (Settings::get('maintenance') === 'maintenance') {
            $items[] = ['count' => 0, 'label' => 'Holding page is on', 'detail' => 'visitors see "coming soon"; switch to Live when ready', 'link' => 'admin.php?page=gcc-settings#gcc-site-status', 'tone' => 'danger'];
        }
        if (! Recaptcha::enabled()) {
            $items[] = ['count' => 0, 'label' => 'reCAPTCHA is off', 'detail' => 'the quote form relies on the honeypot and rate limit alone', 'link' => 'admin.php?page=gcc-settings#gcc-spam-protection', 'tone' => 'plain'];
        }

        return $items;
    }

    /** @return array{title:string, subtitle:string, total:int, slices:list<array{label:string, count:int, color:string}>, link:string} */
    private static function ring(): array
    {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT COALESCE(s.meta_value, 'new') AS status, COUNT(*) AS n FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = '_gcc_status'
             WHERE p.post_type = %s AND p.post_status = 'publish' GROUP BY status",
            PostTypes::ENQUIRY
        ), ARRAY_A);
        $counts = array_column($rows, 'n', 'status');
        $colors = ['new' => '#d99a00', 'contacted' => '#2e86ab', 'quoted' => '#0e9b9d', 'won' => '#0a7375', 'lost' => '#9aa0a6'];
        $slices = [];
        foreach (PostTypes::ENQUIRY_STATUSES as $key => $label) {
            $slices[] = ['label' => $label, 'count' => (int) ($counts[$key] ?? 0), 'color' => $colors[$key] ?? '#646970'];
        }

        return ['title' => 'Enquiries', 'subtitle' => 'Where each one stands', 'total' => (int) array_sum($counts), 'slices' => $slices, 'link' => 'edit.php?post_type=' . PostTypes::ENQUIRY];
    }

    private static function chart(array $trend): void
    {
        echo '<div class="gcc-card gcc-chart"><div class="gcc-card__head"><div><h2>Quote requests, month by month</h2><p>By the month they arrived, last 12 months</p></div>'
            . '<div class="gcc-legend"><span><i style="background:#0a7375"></i>Won</span><span><i style="background:#d99a00"></i>Everything else</span></div></div>';
        if (! $trend['any']) {
            echo '<div class="gcc-empty"><span class="dashicons dashicons-chart-bar"></span><b>No enquiries yet</b><small>Bars appear here as quote requests come in from the Contact page.</small></div></div>';
            return;
        }
        $max = max(1, $trend['max']);
        echo '<div class="gcc-bars"><div class="gcc-bars__axis">';
        foreach ([$max, (int) round($max / 2), 0] as $t) {
            echo '<span>' . esc_html((string) $t) . '</span>';
        }
        echo '</div><div class="gcc-bars__plot"><div class="gcc-bars__cols">';
        foreach ($trend['months'] as $m) {
            $w = (int) round($m['won'] / $max * 100);
            $o = (int) round(($m['won'] + $m['other']) / $max * 100);
            echo '<div class="gcc-bars__col" tabindex="0"><i class="is-pending" style="height:' . $o . '%"></i><i class="is-confirmed" style="height:' . $w . '%"></i>'
                . '<span class="gcc-bars__tip"><b>' . esc_html($m['full']) . '</b>' . esc_html((string) ($m['won'] + $m['other'])) . ' received<br>' . esc_html((string) $m['won']) . ' won</span></div>';
        }
        echo '</div><div class="gcc-bars__labels">';
        foreach ($trend['months'] as $m) {
            echo '<span>' . esc_html($m['label']) . '</span>';
        }
        echo '</div></div></div></div>';
    }

    private static function attentionCard(array $items): void
    {
        echo '<div class="gcc-card"><div class="gcc-card__head"><div><h2>Needs attention</h2><p>What is waiting on somebody</p></div></div>';
        if (! $items) {
            echo '<div class="gcc-empty"><span class="dashicons dashicons-yes-alt" style="color:#0a7375"></span><b>Nothing waiting</b><small>Everything is up to date.</small></div></div>';
            return;
        }
        echo '<ul class="gcc-attention">';
        foreach ($items as $it) {
            echo '<li><a href="' . esc_url(admin_url($it['link'])) . '"><span class="gcc-count is-' . esc_attr($it['tone']) . '">' . ($it['count'] ? esc_html((string) $it['count']) : '<span class="dashicons dashicons-info-outline"></span>') . '</span>'
                . '<span class="gcc-attention__text"><b>' . esc_html($it['label']) . '</b><small>' . esc_html($it['detail']) . '</small></span>'
                . '<span class="dashicons dashicons-arrow-right-alt2"></span></a></li>';
        }
        echo '</ul></div>';
    }

    private static function ringCard(array $ring): void
    {
        $total = max(1, $ring['total']);
        $stops = [];
        $at = 0.0;
        foreach ($ring['slices'] as $s) {
            $to = $at + $s['count'] / $total * 100;
            $stops[] = $s['color'] . ' ' . $at . '% ' . $to . '%';
            $at = $to;
        }
        $gradient = $ring['total'] ? 'conic-gradient(' . implode(', ', $stops) . ')' : 'conic-gradient(#e2e4e7 0% 100%)';
        echo '<a class="gcc-card gcc-ringcard" href="' . esc_url(admin_url($ring['link'])) . '"><div class="gcc-card__head"><div><h2>' . esc_html($ring['title']) . '</h2><p>' . esc_html($ring['subtitle']) . '</p></div></div>'
            . '<div class="gcc-ring"><div class="gcc-ring__disc" style="background:' . esc_attr($gradient) . '"><div class="gcc-ring__hole"><b>' . esc_html((string) $ring['total']) . '</b><small>total</small></div></div><ul>';
        foreach ($ring['slices'] as $s) {
            echo '<li><i style="background:' . esc_attr($s['color']) . '"></i><span>' . esc_html($s['label']) . '</span><b>' . esc_html((string) $s['count']) . '</b></li>';
        }
        echo '</ul></div></a>';
    }

    private static function latestCard(): void
    {
        echo '<div class="gcc-card"><div class="gcc-card__head"><div><h2>Latest enquiries</h2><p>The ten most recent</p></div></div>';
        $latest = get_posts(['post_type' => PostTypes::ENQUIRY, 'numberposts' => 10, 'post_status' => 'publish']);
        if (! $latest) {
            echo '<div class="gcc-empty"><span class="dashicons dashicons-email"></span><b>Nothing yet</b><small>Quote requests appear here as they arrive.</small></div></div>';
            return;
        }
        echo '<ul class="gcc-latest">';
        foreach ($latest as $p) {
            $status = MetaBoxes::get($p->ID, 'status', 'new');
            echo '<li><a href="' . esc_url((string) get_edit_post_link($p->ID)) . '"><span class="dashicons dashicons-email"></span>'
                . '<span class="gcc-latest__text"><b>' . esc_html($p->post_title) . '</b><small>' . esc_html(MetaBoxes::get($p->ID, 'location')) . ' · ' . esc_html(human_time_diff((int) get_post_time('U', true, $p))) . ' ago</small></span>'
                . '<span class="gcc-pill is-' . esc_attr($status) . '">' . esc_html(PostTypes::ENQUIRY_STATUSES[$status] ?? $status) . '</span></a></li>';
        }
        echo '</ul></div>';
    }

    /** The projects whose details the client has not yet confirmed. */
    private static function confirmCard(): void
    {
        echo '<div class="gcc-card"><div class="gcc-card__head"><div><h2>Projects to confirm</h2><p>Written from photographs; check and switch the flag off</p></div></div>';
        $list = get_posts(['post_type' => PostTypes::PROJECT, 'numberposts' => 10, 'post_status' => 'publish', 'meta_key' => '_gcc_confirm', 'meta_value' => 'yes']);
        if (! $list) {
            echo '<div class="gcc-empty"><span class="dashicons dashicons-yes-alt" style="color:#0a7375"></span><b>All confirmed</b><small>Every project\'s details have been checked.</small></div></div>';
            return;
        }
        echo '<ul class="gcc-latest">';
        foreach ($list as $p) {
            echo '<li><a href="' . esc_url((string) get_edit_post_link($p->ID)) . '"><span class="dashicons dashicons-building"></span>'
                . '<span class="gcc-latest__text"><b>' . esc_html($p->post_title) . '</b><small>' . esc_html(MetaBoxes::get($p->ID, 'ref')) . '</small></span>'
                . '<span class="gcc-pill is-new">To confirm</span></a></li>';
        }
        echo '</ul></div>';
    }
}
