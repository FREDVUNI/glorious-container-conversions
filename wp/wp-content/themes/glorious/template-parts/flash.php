<?php
/** The result of the last quote submission, when there is one. */
$flash = class_exists(GCC\Forms::class) ? GCC\Forms::flash() : null;
if (! is_array($flash)) {
    return;
}
$ok = ! empty($flash['ok']);
?>
<div class="flash form-status" id="form-status">
  <div class="status-card <?php echo $ok ? 'is-success' : 'is-error'; ?>" role="<?php echo $ok ? 'status' : 'alert'; ?>" tabindex="-1">
    <div class="icon"><?php echo gcc_icon($ok ? 'check' : 'warn'); ?></div>
    <div>
      <h3><?php echo $ok ? 'Request received' : 'Please check the form'; ?></h3>
      <p><?php echo esc_html((string) $flash['message']); ?></p>
      <?php if ($ok && ! empty($flash['summary'])) : ?>
        <dl><?php foreach ((array) $flash['summary'] as $k => $v) : ?><dt><?php echo esc_html((string) $k); ?></dt><dd><?php echo esc_html((string) $v); ?></dd><?php endforeach; ?></dl>
      <?php endif; ?>
    </div>
  </div>
</div>
