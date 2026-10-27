<?php
/** The server's message for one field, if that field was rejected. $args: field. */
$msg = class_exists(GCC\Forms::class) ? GCC\Forms::error((string) ($args['field'] ?? '')) : '';
?>
<p class="error" aria-live="polite"><?php echo esc_html($msg); ?></p>
