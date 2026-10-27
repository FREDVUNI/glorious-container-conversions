<?php
declare(strict_types=1);

namespace GCC;

/**
 * The extra fields on each record, hand-rolled rather than through a field
 * plugin, so the site depends on nothing that might stop being maintained.
 */
final class MetaBoxes
{
    /** @return array<string, array<string, array{label:string, type:string, options?:array<string,string>, hint?:string}>> */
    public static function schema(): array
    {
        return [
            PostTypes::PROJECT => [
                'ref' => ['label' => 'Reference', 'type' => 'text', 'hint' => 'The plate on the card, e.g. GCC-24-05-H. Any short code you use internally.'],
                'gallery' => ['label' => 'Photos', 'type' => 'gallery', 'hint' => 'The first photo is the cover. Drag to reorder. With no photos chosen the featured image, then the bundled prototype photos, are used.'],
                'specs' => ['label' => 'Specifications', 'type' => 'textarea', 'hint' => 'One per line, as "Label: value", e.g. "Container: 1 × 40ft high-cube".'],
                'features' => ['label' => 'Features', 'type' => 'textarea', 'hint' => 'One per line. The tick list in the lightbox and on the project page.'],
                'size' => ['label' => 'Card size on the Work page', 'type' => 'select', 'options' => ['normal' => 'Normal', 'wide' => 'Wide (two columns)', 'tall' => 'Tall (portrait)']],
                'featured' => ['label' => 'On the home page', 'type' => 'select', 'options' => ['' => 'Not featured', '1' => 'Recent builds, slot 1 (large)', '2' => 'Recent builds, slot 2 (portrait)', '3' => 'Recent builds, slot 3 (wide)']],
                'confirm' => ['label' => 'Details need confirming', 'type' => 'select', 'options' => ['no' => 'No', 'yes' => 'Yes — show the "details to be confirmed" note'], 'hint' => 'For entries written from photographs before the client confirmed them.'],
                'bundled' => ['label' => 'Bundled photos', 'type' => 'text', 'hint' => 'Set by the installer for the prototype projects: the theme\'s own photo names, comma-separated. Ignored once photos are chosen above.'],
                'alts' => ['label' => 'Bundled photo descriptions', 'type' => 'textarea', 'hint' => 'One per bundled photo, same order. Read out by screen readers.'],
            ],
            PostTypes::SERVICE => [
                'features' => ['label' => 'Bullet points', 'type' => 'textarea', 'hint' => 'One per line.'],
                'note' => ['label' => 'Label above the bullets', 'type' => 'text', 'hint' => 'e.g. "Options". Leave empty for none.'],
                'cta' => ['label' => 'Link text', 'type' => 'text', 'hint' => 'e.g. "Quote an office". Points at the quote form with this service chosen.'],
                'quote_type' => ['label' => 'Quote form project type', 'type' => 'select', 'options' => ['' => '— none —'] + Settings::PROJECT_TYPES],
                'home' => ['label' => 'On the home page', 'type' => 'select', 'options' => ['no' => 'Not shown', 'yes' => 'In "What we build" (the first three marked, in order)']],
                'tags' => ['label' => 'Home page tags', 'type' => 'text', 'hint' => 'Comma-separated, e.g. Insulated, Personalised layout. Shown on the home page card.'],
                'caption' => ['label' => 'Photo caption', 'type' => 'text', 'hint' => 'e.g. "Half timber cladding".'],
                'caption_right' => ['label' => 'Photo caption, right side', 'type' => 'text', 'hint' => 'e.g. "Kampala" or "Stock photo".'],
                'bundled' => ['label' => 'Bundled photo', 'type' => 'text', 'hint' => 'Set by the installer. Ignored once a featured image is set.'],
                'alt' => ['label' => 'Bundled photo description', 'type' => 'text'],
            ],
            PostTypes::TEAM => [
                'role' => ['label' => 'Role', 'type' => 'text', 'hint' => 'e.g. Business development'],
                'quote' => ['label' => 'A line from them', 'type' => 'textarea', 'hint' => 'Optional. Shown under the name.'],
                'bundled' => ['label' => 'Bundled portrait', 'type' => 'text', 'hint' => 'Set by the installer. Ignored once a featured image is set.'],
            ],
            PostTypes::TESTIMONIAL => [
                'role' => ['label' => 'Who they are', 'type' => 'text', 'hint' => 'e.g. "Clinic manager, Mukono" or "Homeowner, Entebbe".'],
                'project' => ['label' => 'Project', 'type' => 'post', 'options' => []],
            ],
            // The blog uses WordPress's own posts; these are the few extras
            // the article layout wants beyond title, text and featured image.
            'post' => [
                'read' => ['label' => 'Reading time', 'type' => 'text', 'hint' => 'e.g. "5 min". Worked out from the text when left empty.'],
                'cover_caption' => ['label' => 'Cover photo caption', 'type' => 'text', 'hint' => 'Under the featured image, e.g. "A 40ft home after its roof was fitted, Kampala".'],
                'bundled' => ['label' => 'Bundled cover photo', 'type' => 'text', 'hint' => 'Set by the installer for the first four articles. Ignored once a featured image is set.'],
                'cover_alt' => ['label' => 'Bundled cover description', 'type' => 'text'],
            ],
            PostTypes::ENQUIRY => [
                'sender_name' => ['label' => 'Name', 'type' => 'readonly'],
                'email' => ['label' => 'Email', 'type' => 'readonly'],
                'phone' => ['label' => 'Phone', 'type' => 'readonly'],
                'project_type' => ['label' => 'Project type', 'type' => 'readonly'],
                'size' => ['label' => 'Container size', 'type' => 'readonly'],
                'location' => ['label' => 'Site location', 'type' => 'readonly'],
                'timeline' => ['label' => 'Timeline', 'type' => 'readonly'],
                'about' => ['label' => 'Came from', 'type' => 'readonly'],
                'message' => ['label' => 'Their project', 'type' => 'readonly'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => PostTypes::ENQUIRY_STATUSES],
                'notes' => ['label' => 'Internal notes', 'type' => 'textarea', 'hint' => 'e.g. called 14 Nov, sending drawings Friday'],
            ],
        ];
    }

    public static function boot(): void
    {
        add_action('add_meta_boxes', [self::class, 'add']);
        add_action('save_post', [self::class, 'save'], 10, 2);
    }

    public static function add(): void
    {
        foreach (array_keys(self::schema()) as $type) {
            add_meta_box('gcc_fields', 'Details', [self::class, 'render'], $type, 'normal', 'high');
        }
    }

    public static function get(int $post_id, string $key, string $default = ''): string
    {
        $value = get_post_meta($post_id, '_gcc_' . $key, true);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function render(\WP_Post $post): void
    {
        $fields = self::schema()[$post->post_type] ?? [];
        wp_nonce_field('gcc_fields_' . $post->ID, 'gcc_fields_nonce');

        echo '<table class="form-table"><tbody>';
        foreach ($fields as $key => $field) {
            $value = self::get($post->ID, $key);
            $id = 'gcc_' . $key;
            echo '<tr><th scope="row"><label for="' . esc_attr($id) . '">' . esc_html($field['label']) . '</label></th><td>';

            switch ($field['type']) {
                case 'readonly':
                    echo '<div style="padding:6px 0;max-width:40em;white-space:pre-wrap">' . ($value === '' ? '<em>—</em>' : esc_html($value)) . '</div>';
                    break;
                case 'textarea':
                    echo '<textarea id="' . esc_attr($id) . '" name="gcc[' . esc_attr($key) . ']" rows="5" class="large-text">' . esc_textarea($value) . '</textarea>';
                    break;
                case 'select':
                    echo '<select id="' . esc_attr($id) . '" name="gcc[' . esc_attr($key) . ']">';
                    foreach ($field['options'] as $opt => $label) {
                        echo '<option value="' . esc_attr((string) $opt) . '"' . selected($value, (string) $opt, false) . '>' . esc_html($label) . '</option>';
                    }
                    echo '</select>';
                    break;
                case 'post':
                    $projects = get_posts(['post_type' => PostTypes::PROJECT, 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
                    echo '<select id="' . esc_attr($id) . '" name="gcc[' . esc_attr($key) . ']"><option value="">— None —</option>';
                    foreach ($projects as $p) {
                        echo '<option value="' . esc_attr((string) $p->ID) . '"' . selected($value, (string) $p->ID, false) . '>' . esc_html($p->post_title) . '</option>';
                    }
                    echo '</select>';
                    break;
                case 'gallery':
                    self::galleryField($key, $value);
                    break;
                default:
                    echo '<input id="' . esc_attr($id) . '" name="gcc[' . esc_attr($key) . ']" type="' . esc_attr($field['type']) . '" value="' . esc_attr($value) . '" class="regular-text">';
            }

            if (! empty($field['hint'])) {
                echo '<p class="description">' . esc_html($field['hint']) . '</p>';
            }
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }

    /** A multi-image picker on the Media Library: thumbnails, drag to reorder, remove one. Stores comma-separated attachment IDs. */
    private static function galleryField(string $key, string $value): void
    {
        $ids = array_filter(array_map('absint', explode(',', $value)));
        echo '<div class="gcc-gallery" data-gallery-field>'
            . '<input type="hidden" name="gcc[' . esc_attr($key) . ']" value="' . esc_attr(implode(',', $ids)) . '" data-gallery-input>'
            . '<ul class="gcc-gallery__list" data-gallery-list>';
        foreach ($ids as $id) {
            $thumb = wp_get_attachment_image_url($id, 'thumbnail');
            if ($thumb) {
                echo '<li data-id="' . esc_attr((string) $id) . '"><img src="' . esc_url($thumb) . '" alt=""><button type="button" class="gcc-gallery__remove" data-gallery-remove aria-label="Remove">&times;</button></li>';
            }
        }
        echo '</ul><p><button type="button" class="button" data-gallery-add><span class="dashicons dashicons-images-alt2"></span> Add photos</button></p></div>';
    }

    public static function save(int $post_id, \WP_Post $post): void
    {
        $fields = self::schema()[$post->post_type] ?? null;
        if ($fields === null || ! isset($_POST['gcc_fields_nonce'])) {
            return;
        }
        if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gcc_fields_nonce'])), 'gcc_fields_' . $post_id)) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $input = isset($_POST['gcc']) && is_array($_POST['gcc']) ? wp_unslash($_POST['gcc']) : [];

        foreach ($fields as $key => $field) {
            // What the visitor typed is theirs; staff cannot rewrite it.
            if ($field['type'] === 'readonly') {
                continue;
            }
            $raw = isset($input[$key]) ? (string) $input[$key] : '';
            $clean = match ($field['type']) {
                'textarea' => sanitize_textarea_field($raw),
                'select' => array_key_exists($raw, $field['options'] ?? []) ? $raw : '',
                'post', 'number' => $raw === '' ? '' : (string) absint($raw),
                'gallery' => implode(',', array_filter(array_map('absint', explode(',', $raw)))),
                default => sanitize_text_field($raw),
            };
            update_post_meta($post_id, '_gcc_' . $key, $clean);
        }

        // Only one project per home-page slot: marking this one clears any other.
        if ($post->post_type === PostTypes::PROJECT && ($input['featured'] ?? '') !== '') {
            foreach (get_posts(['post_type' => PostTypes::PROJECT, 'numberposts' => -1, 'exclude' => [$post_id], 'meta_key' => '_gcc_featured', 'meta_value' => $input['featured'], 'fields' => 'ids']) as $other) {
                update_post_meta($other, '_gcc_featured', '');
            }
        }
    }
}
