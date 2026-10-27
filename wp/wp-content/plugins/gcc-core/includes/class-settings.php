<?php

declare(strict_types=1);

namespace GCC;

/**
 * One settings page, in the GCC menu, that controls the whole site.
 *
 * Stored as a single option array rather than one option per field, so a
 * backup or a migration moves it as one piece. Every piece of copy the
 * prototype had hard-coded — the hero lines, the statement, the process
 * steps, the countries, the unit sizes — is a field here, so the client
 * changes wording without a developer.
 */
final class Settings
{
    public const OPTION = 'gcc_settings';

    /** What a hero figure can count. Anything the site holds is counted live. */
    public const STAT_SOURCES = [
        'manual' => 'A number typed by hand',
        'projects' => 'Projects published',
        'services' => 'Services listed',
        'posts' => 'Articles published',
        'countries' => 'Countries in the coverage list',
        'team' => 'Team members',
    ];

    /** Where a hero button can point. */
    public const LINKS = ['quote' => 'The quote form', 'work' => 'The Work page', 'services' => 'The Services page', 'about' => 'The About page', 'blog' => 'The Blog', 'contact' => 'The Contact page', 'phone' => 'Call the main phone number', 'whatsapp' => 'Open WhatsApp', 'custom' => 'Somewhere else (address below)'];

    /** The project types the quote form offers. Keys are stored on enquiries. */
    public const PROJECT_TYPES = [
        'home' => 'Home',
        'office' => 'Office or workspace',
        'shop' => 'Shop, kiosk or restaurant',
        'clinic' => 'Clinic or healthcare',
        'cold' => 'Cold storage',
        'ablution' => 'Ablution unit',
        'lab' => 'Computer laboratory',
        'cladding' => 'Cladding, refurbishment or repair',
        'freight' => 'Freight or relocation',
        'buyback' => 'Valuation or buyback',
        'other' => 'Something else',
    ];

    public const SIZES = ['20ft' => '20ft', '40ft' => '40ft', '40ft HC' => '40ft HC', 'Multiple units' => 'Multiple', 'Not sure yet' => 'Not sure'];
    public const TIMELINES = ['As soon as possible', '1–3 months', '3–6 months', 'Just exploring'];

    /**
     * The four hero figures, resolved: live counts where a source is chosen,
     * the typed number otherwise. Empty figures are skipped.
     *
     * @return list<array{value:string, suffix:string, label:string}>
     */
    public static function stats(): array
    {
        $out = [];
        for ($i = 1; $i <= 4; $i++) {
            $source = self::get("stat_{$i}_source");
            $label = self::get("stat_{$i}_label");
            $suffix = self::get("stat_{$i}_suffix");
            if ($source === 'manual') {
                $typed = trim(self::get("stat_{$i}_num"));
                if ($typed === '') {
                    continue;
                }
                $out[] = ['value' => $typed, 'suffix' => $suffix, 'label' => $label];
                continue;
            }
            $out[] = ['value' => (string) self::count($source), 'suffix' => $suffix, 'label' => $label];
        }

        return $out;
    }

    /** Count one source from the database. Cached for a minute; the home page is the busiest page. */
    public static function count(string $source): int
    {
        $cached = get_transient('gcc_stat_' . $source);
        if ($cached !== false) {
            return (int) $cached;
        }
        $count = static fn (string $type): int => (int) (new \WP_Query(['post_type' => $type, 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 1, 'no_found_rows' => false]))->found_posts;
        $value = match ($source) {
            'projects' => $count(PostTypes::PROJECT),
            'services' => $count(PostTypes::SERVICE),
            'posts' => (int) wp_count_posts()->publish,
            'countries' => count(self::lines('countries')),
            'team' => $count(PostTypes::TEAM),
            default => 0,
        };
        set_transient('gcc_stat_' . $source, $value, MINUTE_IN_SECONDS);

        return $value;
    }

    /** A textarea setting, one item per line, blanks dropped. @return list<string> */
    public static function lines(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', self::get($key)) ?: [])));
    }

    /** @return array<string, array<string, array{label:string, type:string, default:string, hint?:string, secret?:bool, options?:array<string,string>}>> */
    public static function fields(): array
    {
        $f = [
            'Notifications' => [
                'notify_emails' => ['label' => 'Send enquiries to', 'type' => 'text', 'default' => get_option('admin_email'), 'hint' => 'One or more addresses, separated by commas. Every quote request goes here.'],
                'from_name' => ['label' => 'Sender name on emails', 'type' => 'text', 'default' => 'Glorious Container Conversions'],
                'ack_sender' => ['label' => 'Email the sender an acknowledgement', 'type' => 'select', 'options' => ['yes' => 'Yes — "we have your request, drawings within 48 hours"', 'no' => 'No'], 'default' => 'yes'],
                'reply_within' => ['label' => 'Reply promise on the form', 'type' => 'text', 'default' => 'We reply within one working day.'],
            ],
            'Company' => [
                'logo' => ['label' => 'Logo', 'type' => 'image', 'default' => '', 'hint' => 'Shown in the header. A PNG with a transparent background. Leave empty to use the bundled logo.'],
                'company_name' => ['label' => 'Company name', 'type' => 'text', 'default' => 'Glorious Container Conversions Uganda Limited'],
                'short_name' => ['label' => 'Short name', 'type' => 'text', 'default' => 'Glorious Container Conversions', 'hint' => 'Used in sentences and the footer wordmark ("Glorious").'],
                'tagline' => ['label' => 'Tagline', 'type' => 'text', 'default' => 'Building inspiring spaces'],
                'phone' => ['label' => 'Phone', 'type' => 'text', 'default' => '+256 783 063 789'],
                'phone_hint' => ['label' => 'Phone example on the form', 'type' => 'text', 'default' => '+256 …', 'hint' => 'The grey example text in the quote form\'s phone box.'],
                'phone_2' => ['label' => 'Second phone', 'type' => 'text', 'default' => '+256 751 486 514'],
                'whatsapp' => ['label' => 'WhatsApp number', 'type' => 'text', 'default' => '+256 783 063 789', 'hint' => 'Drives the WhatsApp links. Leave empty to hide them.'],
                'email' => ['label' => 'Public email', 'type' => 'email', 'default' => 'gloriouscontainerconversions@gmail.com'],
                'address' => ['label' => 'Street address', 'type' => 'text', 'default' => 'Plot 135, Mulwana Road, Bugolobi Industrial Area, Kampala'],
                'po_box' => ['label' => 'Postal address', 'type' => 'text', 'default' => 'P.O. Box 130766, Kampala GPO'],
                'hours_week' => ['label' => 'Hours, Monday to Friday', 'type' => 'text', 'default' => '09:00–18:00'],
                'hours_weekend' => ['label' => 'Hours, Saturday and Sunday', 'type' => 'text', 'default' => '09:00–12:00'],
                'map_query' => ['label' => 'Map search text', 'type' => 'text', 'default' => 'Plot 135 Mulwana Road, Bugolobi Industrial Area, Kampala, Uganda', 'hint' => 'What the embedded Google map searches for.'],
            ],
            'Site status' => [
                'maintenance' => ['label' => 'Site status', 'type' => 'select', 'options' => ['live' => 'Live — everyone can see the site', 'maintenance' => 'Coming soon — visitors see a holding page, signed-in staff see the site'], 'default' => 'live'],
                'maintenance_message' => ['label' => 'Holding page message', 'type' => 'text', 'default' => 'Our new website is on its way. In the meantime, call or write to us about your project.'],
            ],
            'Search and sharing' => [
                'site_description' => ['label' => 'Site description', 'type' => 'text', 'default' => 'Glorious Container Conversions converts shipping containers into homes, offices, clinics, cold rooms and shops. Designed and built in Kampala, delivered by road across East and Central Africa.', 'hint' => 'Shown under the site name in search results and when a page without its own summary is shared.'],
                'share_image' => ['label' => 'Share image', 'type' => 'image', 'default' => '', 'hint' => 'The picture shown when a link is shared on WhatsApp or Facebook. 1200×630 works best. Leave empty to use the bundled photograph.'],
            ],
            'Social media' => [
                'social_facebook' => ['label' => 'Facebook', 'type' => 'url', 'default' => 'https://www.facebook.com/GloriousContainerConversionsUgandaLtd'],
                'social_x' => ['label' => 'X (Twitter)', 'type' => 'url', 'default' => 'https://x.com/GloriousCUg'],
                'social_youtube' => ['label' => 'YouTube', 'type' => 'url', 'default' => 'https://www.youtube.com/channel/UC0tNDjVGvpX_ql-baC46UOw'],
                'social_instagram' => ['label' => 'Instagram', 'type' => 'url', 'default' => ''],
                'social_linkedin' => ['label' => 'LinkedIn', 'type' => 'url', 'default' => ''],
                'social_tiktok' => ['label' => 'TikTok', 'type' => 'url', 'default' => ''],
            ],
            'Home page' => [
                'hero_eyebrow' => ['label' => 'Hero eyebrow', 'type' => 'text', 'default' => 'Container conversions · Kampala, Uganda'],
                'hero_line_1' => ['label' => 'Headline, line 1', 'type' => 'text', 'default' => 'Inspiring spaces,'],
                'hero_line_2' => ['label' => 'Headline, line 2 (lighter weight)', 'type' => 'text', 'default' => 'fabricated'],
                'hero_line_3' => ['label' => 'Headline, line 3', 'type' => 'text', 'default' => 'from steel.'],
                'hero_lede' => ['label' => 'Hero paragraph', 'type' => 'textarea', 'default' => 'We turn shipping containers into homes, offices, clinics, cold rooms and shops. Designed in Kampala, built at our Bugolobi yard, delivered by road across East and Central Africa.'],
                'hero_cta' => ['label' => 'Main button', 'type' => 'text', 'default' => 'Request a quote'],
                'hero_cta_link' => ['label' => 'Main button goes to', 'type' => 'select', 'options' => self::LINKS, 'default' => 'quote'],
                'hero_cta_url' => ['label' => 'Main button address (if "Somewhere else")', 'type' => 'url', 'default' => ''],
                'hero_cta_2' => ['label' => 'Second button', 'type' => 'text', 'default' => 'See our work', 'hint' => 'Leave empty for one button only.'],
                'hero_cta_2_link' => ['label' => 'Second button goes to', 'type' => 'select', 'options' => self::LINKS, 'default' => 'work'],
                'hero_cta_2_url' => ['label' => 'Second button address (if "Somewhere else")', 'type' => 'url', 'default' => ''],
                'marquee' => ['label' => 'Ticker items', 'type' => 'textarea', 'default' => "Container homes\nSite offices\nMobile clinics\nCold rooms\nAblution blocks\nComputer labs\nPop-up restaurants\nCladding\nFreight & relocation\nBuybacks", 'hint' => 'One per line. The yellow scrolling strip under the hero.'],
                'statement' => ['label' => 'Statement', 'type' => 'textarea', 'default' => 'A shipping container is a structural steel frame that has already survived the ocean. We cut, insulate, line and finish it into a space people are *proud to live and work in*, in weeks rather than months.', 'hint' => 'Put *asterisks* around the words to colour teal.'],
                'uses_intro' => ['label' => '"What we build" intro', 'type' => 'textarea', 'default' => 'Every build starts from a standard 20ft or 40ft unit and is designed to your specification: single or double storey, lined, insulated and wired.'],
                'why_title' => ['label' => '"Why containers" heading', 'type' => 'text', 'default' => 'Built for the sea. Ready for the site.'],
                'why_lede' => ['label' => '"Why containers" paragraph', 'type' => 'textarea', 'default' => 'Corten steel, a rigid frame and standard dimensions mean every unit is strong, stackable and transportable before we have touched it.'],
                'why_items' => ['label' => '"Why containers" reasons', 'type' => 'textarea', 'default' => "Fast | Workshop fabrication runs in parallel with site preparation. Units arrive finished, not as materials.\nRelocatable | Move the building when you move the business. We relocate units as a standard part of the service.\nDurable | Designed to carry cargo across oceans, a container shrugs off weather, pests and rough handling.\nResource-efficient | Every build reuses a decommissioned unit. Solar power, rainwater harvesting and efficient insulation are available options.\nBudget-certain | A fixed scope built off-site keeps costs predictable, from a single storage box to a high-spec bespoke conversion.", 'hint' => 'One per line, as "Title | Sentence".'],
                'countries' => ['label' => 'Countries served', 'type' => 'textarea', 'default' => "Uganda | HQ · Kampala\nTanzania | Road\nRwanda | Road\nBurundi | Road\nEthiopia | Road\nDR Congo | Road\nSouth Sudan | Road", 'hint' => 'One per line, as "Country | note".'],
                'coverage_title' => ['label' => 'Coverage heading', 'type' => 'text', 'default' => 'East & Central Africa, by road'],
                'coverage_text' => ['label' => 'Coverage paragraph', 'type' => 'textarea', 'default' => 'Our in-house transport division handles domestic and cross-border freight so units arrive without damage and on schedule.'],
                'cta_title' => ['label' => 'Footer call to action', 'type' => 'text', 'default' => 'Tell us what you need. Drawings in 48 hours.'],
            ],
            'About page' => [
                'about_lede' => ['label' => 'Intro under the title', 'type' => 'textarea', 'default' => 'Glorious Container Conversions Uganda Limited designs, fabricates and delivers container buildings from its yard in Bugolobi Industrial Area, Kampala.'],
                'about_heading' => ['label' => '"The company" heading', 'type' => 'text', 'default' => 'Fast, modular, cost-effective, eco-friendly'],
                'mission' => ['label' => 'Mission', 'type' => 'textarea', 'default' => 'To meet our clients\' needs for fast, modular, cost-effective and eco-friendly infrastructure using shipping containers and other modular systems.'],
                'vision' => ['label' => 'Vision', 'type' => 'textarea', 'default' => 'To be the market leader in modular construction, using recycled shipping containers as the building blocks for society\'s infrastructure needs and green housing.'],
                'commitments_intro' => ['label' => 'Commitments intro', 'type' => 'text', 'default' => 'Steel first, then everything that makes it a building: insulation, lining, services, finishes.'],
                'commitments' => ['label' => 'Commitments', 'type' => 'textarea', 'default' => "Sustainability | Every build gives a decommissioned container a second life. Solar, rainwater and insulation options cut running costs.\nCustomisation | Layouts, openings, linings and colours follow your drawings, not a catalogue.\nDurability | Steel built for the ocean, finished with anti-vandal windows, proper insulation and protective coatings.\nEfficient installation | Finished off-site, placed by crane, commissioned by our own trained team.\nBudget-conscious | From a storage box to a bespoke clinic, scope and price are fixed before fabrication begins.", 'hint' => 'One per line, as "Title | Sentence".'],
                'team_intro' => ['label' => 'Team intro', 'type' => 'text', 'default' => 'A 12-year-experienced workforce of fabricators, electricians and finishers, led by the people below.'],
                'yard_heading' => ['label' => 'Yard heading', 'type' => 'text', 'default' => 'Bugolobi Industrial Area'],
                'yard_text' => ['label' => 'Yard text', 'type' => 'textarea', 'default' => 'Visit the workshop to see units in progress, or send us a site location and we will come to you.'],
            ],
            'Services page' => [
                'services_lede' => ['label' => 'Intro under the title', 'type' => 'text', 'default' => 'Design, fabrication, supply, transport and after-care, all from one yard in Kampala.'],
                'work_lede' => ['label' => 'Work page intro', 'type' => 'text', 'default' => 'Projects from the Glorious Container Conversions yard. Filter by use, or search for a feature.'],
                'blog_lede' => ['label' => 'Blog intro', 'type' => 'text', 'default' => 'Articles by the Glorious Container Conversions team on designing, insulating, roofing and living in container buildings.'],
                'contact_lede' => ['label' => 'Contact intro', 'type' => 'text', 'default' => 'Describe the use, the site and the timeline. CAD drawings follow within 48 hours.'],
            ],
            'Photo captions' => [
                'caption_statement_1' => ['label' => 'Statement photo 1', 'type' => 'text', 'default' => 'Raw material | Stock photo', 'hint' => 'Left text | right text. The small labels under the fixed photos. Leave empty to hide a caption.'],
                'caption_statement_2' => ['label' => 'Statement photo 2', 'type' => 'text', 'default' => 'Finished unit | Bugolobi yard'],
                'caption_why' => ['label' => '"Why containers" photo', 'type' => 'text', 'default' => 'Two-high stacking | Site build'],
                'caption_coverage' => ['label' => 'Coverage photo', 'type' => 'text', 'default' => 'Road freight | Stock photo'],
                'caption_about_wide' => ['label' => 'About page wide photo', 'type' => 'text', 'default' => 'Two-storey hospital block | Kampala'],
            ],
            'Hero photos' => [
                'hero_source' => ['label' => 'Photos come from', 'type' => 'select', 'options' => ['manual' => 'The photos chosen below', 'featured' => 'The featured projects (Recent builds), cover photo of each', 'latest' => 'The newest projects, cover photo of each'], 'default' => 'manual', 'hint' => 'With projects as the source, the plate is the project reference and the caption its title, and the slides update themselves as projects change.'],
                'hero_autoplay' => ['label' => 'Autoplay', 'type' => 'select', 'options' => ['yes' => 'Yes — advance on its own', 'no' => 'No — arrows and dots only'], 'default' => 'yes'],
                'hero_interval' => ['label' => 'Seconds per photo', 'type' => 'text', 'default' => '5.5', 'hint' => 'When autoplaying. Between 2 and 20.'],
                'hero_max' => ['label' => 'Most photos to show', 'type' => 'text', 'default' => '4', 'hint' => 'Up to 6.'],
            ],
            'Hero figures' => [],
            'Process' => [
                'process_intro' => ['label' => 'Intro', 'type' => 'textarea', 'default' => 'All conversion work happens at our own premises. Your unit arrives finished, is craned onto its base and commissioned by our team.'],
            ],
            'Unit sizes' => [
                'sizes_intro' => ['label' => 'Intro', 'type' => 'textarea', 'default' => 'ISO shipping container dimensions. Internal figures are approximate and reduce once walls are insulated and lined.'],
            ],
            'Spam protection' => [
                'recaptcha_site' => ['label' => 'reCAPTCHA site key', 'type' => 'text', 'default' => '', 'hint' => 'reCAPTCHA v2 "I\'m not a robot", from google.com/recaptcha. Both keys must be filled in before the check turns on.'],
                'recaptcha_secret' => ['label' => 'reCAPTCHA secret key', 'type' => 'password', 'default' => '', 'secret' => true, 'hint' => 'Never shown again once saved. Leave blank to keep the current one.'],
            ],
        ];

        $slides = [
            ['hospital-02', 'GCC-24-05-H', 'Shipping container hospital'],
            ['callcentre-06', '40ft HC', 'Call centre & studio'],
            ['home-red-after', '40ft', 'Home with pitched roof'],
            ['home-blue-01', 'Residence', 'Blue container home'],
            ['', '', ''],
            ['', '', ''],
        ];
        foreach ($slides as $i => [$bundled, $plate, $label]) {
            $n = $i + 1;
            $f['Hero photos']["slide_{$n}_image"] = ['label' => "Photo {$n}", 'type' => 'image', 'default' => '', 'hint' => $n === 1 ? 'Photos 1–4 fall back to the bundled prototype photos; clear a plate and caption to drop that slide. Photos 5 and 6 show only when chosen.' : ''];
            $f['Hero photos']["slide_{$n}_plate"] = ['label' => "Photo {$n} plate", 'type' => 'text', 'default' => $plate];
            $f['Hero photos']["slide_{$n}_label"] = ['label' => "Photo {$n} caption", 'type' => 'text', 'default' => $label];
        }

        $stats = [['manual', '10', '+ yrs', 'Conversion expertise'], ['manual', '48', 'hrs', 'Enquiry to CAD drawings'], ['countries', '7', '', 'Countries served by road'], ['manual', '20 / 40', 'ft', 'Standard & high-cube units']];
        foreach ($stats as $i => [$source, $num, $suffix, $label]) {
            $n = $i + 1;
            $f['Hero figures']["stat_{$n}_source"] = ['label' => "Figure {$n} counts", 'type' => 'select', 'options' => self::STAT_SOURCES, 'default' => $source];
            $f['Hero figures']["stat_{$n}_num"] = ['label' => "Figure {$n} number (when typed by hand)", 'type' => 'text', 'default' => $num];
            $f['Hero figures']["stat_{$n}_suffix"] = ['label' => "Figure {$n} suffix", 'type' => 'text', 'default' => $suffix, 'hint' => $n === 1 ? 'Small text after the number, e.g. "+ yrs".' : ''];
            $f['Hero figures']["stat_{$n}_label"] = ['label' => "Figure {$n} label", 'type' => 'text', 'default' => $label];
        }

        $steps = [
            ['Enquiry', 'Tell us the use, the site and the budget. We advise on new or used units and the right size.', 'Day 0', 'office-int-03'],
            ['Design', 'CAD drawings of the proposed layout and elevations are delivered within 48 hours of the first enquiry.', 'Within 48 hrs', 'u-design-01'],
            ['Fabrication', 'Cutting, welding, insulation, lining, electrics and finishes at the Bugolobi yard, in your brand colours.', 'Workshop build', 'u-weld-03'],
            ['Delivery', 'Road transport by our in-house division, crane placement, erection and commissioning on site.', 'Anywhere by road', 'crane-01'],
        ];
        foreach ($steps as $i => [$title, $text, $time, $bundled]) {
            $n = $i + 1;
            $f['Process']["step_{$n}_title"] = ['label' => "Step {$n} title", 'type' => 'text', 'default' => $title];
            $f['Process']["step_{$n}_text"] = ['label' => "Step {$n} text", 'type' => 'textarea', 'default' => $text];
            $f['Process']["step_{$n}_time"] = ['label' => "Step {$n} tag", 'type' => 'text', 'default' => $time];
            $f['Process']["step_{$n}_image"] = ['label' => "Step {$n} photo", 'type' => 'image', 'default' => '', 'hint' => $n === 1 ? 'Leave empty to use the bundled photo.' : ''];
        }

        $sizes = [
            ['20ft', 'Standard', 'Compact unit', 'Kiosks, site offices, single rooms, storage.', '6.06 × 2.44 × 2.59 m', '5.90 × 2.35 × 2.39 m', '≈ 13.9 m²', 'unit-red-01'],
            ['40ft', 'Standard', 'Full-length unit', 'Homes, offices, classrooms, clinics.', '12.19 × 2.44 × 2.59 m', '12.03 × 2.35 × 2.39 m', '≈ 28.3 m²', 'home-blue-02'],
            ['40ft HC', 'High cube', 'Extra headroom', 'Studios, call centres, anywhere ceiling height matters.', '12.19 × 2.44 × 2.90 m', '12.03 × 2.35 × 2.69 m', '≈ 28.3 m²', 'hero-call-centre'],
        ];
        foreach ($sizes as $i => [$name, $tag, $title, $use, $ext, $int, $floor, $bundled]) {
            $n = $i + 1;
            $f['Unit sizes']["size_{$n}_name"] = ['label' => "Size {$n} name", 'type' => 'text', 'default' => $name];
            $f['Unit sizes']["size_{$n}_tag"] = ['label' => "Size {$n} tag", 'type' => 'text', 'default' => $tag];
            $f['Unit sizes']["size_{$n}_title"] = ['label' => "Size {$n} title", 'type' => 'text', 'default' => $title];
            $f['Unit sizes']["size_{$n}_use"] = ['label' => "Size {$n} typical use", 'type' => 'text', 'default' => $use];
            $f['Unit sizes']["size_{$n}_external"] = ['label' => "Size {$n} external", 'type' => 'text', 'default' => $ext];
            $f['Unit sizes']["size_{$n}_internal"] = ['label' => "Size {$n} internal", 'type' => 'text', 'default' => $int];
            $f['Unit sizes']["size_{$n}_floor"] = ['label' => "Size {$n} floor area", 'type' => 'text', 'default' => $floor];
            $f['Unit sizes']["size_{$n}_image"] = ['label' => "Size {$n} photo", 'type' => 'image', 'default' => ''];
        }

        return $f;
    }

    /** The bundled photo behind an image setting that has not been filled in. */
    public const BUNDLED = [
        'slide_1_image' => 'hospital-02', 'slide_2_image' => 'callcentre-06', 'slide_3_image' => 'home-red-after', 'slide_4_image' => 'home-blue-01',
        'step_1_image' => 'office-int-03', 'step_2_image' => 'u-design-01', 'step_3_image' => 'u-weld-03', 'step_4_image' => 'crane-01',
        'size_1_image' => 'unit-red-01', 'size_2_image' => 'home-blue-02', 'size_3_image' => 'hero-call-centre',
    ];

    public static function boot(): void
    {
        add_action('admin_init', [self::class, 'register']);
    }

    public static function get(string $key, string $default = ''): string
    {
        static $cache = null;
        if ($cache === null) {
            $cache = (array) get_option(self::OPTION, []);
        }
        if (array_key_exists($key, $cache) && $cache[$key] !== '') {
            return (string) $cache[$key];
        }
        foreach (self::fields() as $group) {
            if (isset($group[$key])) {
                return $default !== '' ? $default : (string) $group[$key]['default'];
            }
        }

        return $default;
    }

    public static function register(): void
    {
        register_setting('gcc_settings', self::OPTION, ['type' => 'array', 'sanitize_callback' => [self::class, 'sanitize']]);
    }

    /** @param mixed $input */
    public static function sanitize($input): array
    {
        $current = (array) get_option(self::OPTION, []);
        $clean = [];
        $input = is_array($input) ? $input : [];

        foreach (self::fields() as $group) {
            foreach ($group as $key => $field) {
                $raw = isset($input[$key]) ? trim((string) wp_unslash($input[$key])) : '';
                // A secret rendered blank means "keep it"; the stored value is
                // never sent back to the browser, so blank cannot mean "clear".
                if (! empty($field['secret']) && $raw === '') {
                    $clean[$key] = $current[$key] ?? '';
                    continue;
                }
                $clean[$key] = match ($field['type']) {
                    'select' => isset($field['options'][$raw]) ? $raw : $field['default'],
                    'email' => sanitize_email($raw),
                    'url', 'image' => $raw === '' ? '' : esc_url_raw($raw),
                    'textarea' => sanitize_textarea_field($raw),
                    default => sanitize_text_field($raw),
                };
            }
        }

        $clean['hero_interval'] = (string) min(20, max(2, (float) ($clean['hero_interval'] ?: 5.5)));
        $clean['hero_max'] = (string) min(6, max(1, (int) ($clean['hero_max'] ?: 4)));

        $emails = array_filter(array_map('sanitize_email', array_map('trim', explode(',', $clean['notify_emails'] ?? ''))));
        $clean['notify_emails'] = implode(', ', $emails);

        add_settings_error('gcc_settings', 'saved', 'Settings saved.', 'updated');

        return $clean;
    }

    /** @return list<string> */
    public static function notifyEmails(): array
    {
        $list = array_filter(array_map('trim', explode(',', self::get('notify_emails'))));

        return $list ? array_values($list) : [(string) get_option('admin_email')];
    }
}
