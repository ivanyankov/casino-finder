<?php
/**
 * Plugin Name: Casino Finder
 * Plugin URI:  https://example.com/casino-finder
 * Description: Recommends online casinos based on user preferences through a step-by-step wizard.
 * Version:     1.0.0
 * Author:      Ivan Yankov
 * Author URI:  https://example.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: casino-finder
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('CASINO_FINDER_VERSION', '1.0.0');
define('CASINO_FINDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CASINO_FINDER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CASINO_FINDER_PLUGIN_BASENAME', plugin_basename(__FILE__));

$autoloader = CASINO_FINDER_PLUGIN_DIR . 'vendor/autoload.php';

if (! file_exists($autoloader)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Casino Finder: Composer autoloader not found. Please run "composer install" in the plugin directory.', 'casino-finder');
        echo '</p></div>';
    });
    return;
}

require_once $autoloader;

register_activation_hook(__FILE__, [CasinoFinder\Activator::class, 'activate']);
register_deactivation_hook(__FILE__, [CasinoFinder\Deactivator::class, 'deactivate']);

add_action('plugins_loaded', static function (): void {
    CasinoFinder\Plugin::instance()->boot();
});
