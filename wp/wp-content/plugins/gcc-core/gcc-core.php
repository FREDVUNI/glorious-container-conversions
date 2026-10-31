<?php

/**
 * Plugin Name: Glorious Container Conversions
 * Plugin URI: https://gloriouscontainerconversions.com
 * Description: Projects, services, team, the quote-request form and the GCC settings for the Glorious Container Conversions website. Required by the Glorious theme.
 * Version: 1.2.1
 * Author: Fred Vuni
 * Author URI: https://fredvuni.netlify.app
 * Requires PHP: 8.1
 * Text Domain: gcc
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('GCC_VERSION', '1.2.1');
define('GCC_FILE', __FILE__);
define('GCC_DIR', plugin_dir_path(__FILE__));
define('GCC_URL', plugin_dir_url(__FILE__));

/*
 * Why a plugin and not the theme.
 *
 * A theme is how the site looks this year. The projects, the services, every
 * enquiry a visitor has ever sent — those have to outlive the next redesign.
 * Keeping them here means switching theme loses nothing.
 */
require_once GCC_DIR . 'includes/helpers.php';
require_once GCC_DIR . 'includes/class-settings.php';
require_once GCC_DIR . 'includes/class-post-types.php';
require_once GCC_DIR . 'includes/class-meta-boxes.php';
require_once GCC_DIR . 'includes/class-recaptcha.php';
require_once GCC_DIR . 'includes/class-forms.php';
require_once GCC_DIR . 'includes/class-notifications.php';
require_once GCC_DIR . 'includes/class-admin.php';
require_once GCC_DIR . 'includes/class-dashboard.php';
require_once GCC_DIR . 'includes/class-installer.php';
require_once GCC_DIR . 'includes/class-seo.php';
require_once GCC_DIR . 'includes/class-maintenance.php';
require_once GCC_DIR . 'includes/class-redirects.php';

add_action('plugins_loaded', static function (): void {
    GCC\Settings::boot();
    GCC\PostTypes::boot();
    GCC\MetaBoxes::boot();
    GCC\Forms::boot();
    GCC\Admin::boot();
    GCC\Seo::boot();
    GCC\Maintenance::boot();
    GCC\Redirects::boot();
});

register_activation_hook(__FILE__, [GCC\Installer::class, 'activate']);
add_action('init', [GCC\Installer::class, 'upgrade'], 20);
// Templates are refused until the theme is active, so re-apply when it is.
add_action('after_switch_theme', [GCC\Installer::class, 'applyTemplates']);
register_deactivation_hook(__FILE__, [GCC\Installer::class, 'deactivate']);
