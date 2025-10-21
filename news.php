<?php
/**
 * Plugin Name: New Baltimore Gazette News
 * Plugin URI: https://newbaltimoregazette.com
 * Description: Comprehensive news management plugin for WordPress with modern development practices, Gutenberg blocks, widgets, and REST API.
 * Version: 1.0.0
 * Author: New Baltimore Gazette
 * Author URI: https://newbaltimoregazette.com
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: news
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 6.6
 * Requires PHP: 8.1
 * Network: false
 * Update URI: false
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NEWS_PLUGIN_VERSION', '1.0.0');
define('NEWS_PLUGIN_FILE', __FILE__);
define('NEWS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NEWS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NEWS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('NEWS_PLUGIN_SLUG', 'news');

// Check minimum requirements
if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        printf(
            esc_html__('News Plugin requires PHP 8.1 or higher. You are running PHP %s.', 'news'),
            PHP_VERSION
        );
        echo '</p></div>';
    });
    return;
}

if (version_compare(get_bloginfo('version'), '6.5', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        printf(
            esc_html__('News Plugin requires WordPress 6.5 or higher. You are running WordPress %s.', 'news'),
            get_bloginfo('version')
        );
        echo '</p></div>';
    });
    return;
}

// Autoloader
if (file_exists(NEWS_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once NEWS_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Fallback autoloader for development
    require_once NEWS_PLUGIN_DIR . 'src/NewsPlugin.php';
}

// Initialize the plugin
use NewsPlugin\Core\Plugin;

/**
 * Get the main plugin instance
 */
function news_plugin(): Plugin {
    return Plugin::instance();
}

// Initialize the plugin after WordPress is loaded
add_action('plugins_loaded', function() {
    error_log('News Plugin: Plugin loaded and initializing...');
    news_plugin()->init();
});

// Activation/Deactivation hooks
register_activation_hook(__FILE__, 'news_plugin_activate');
register_deactivation_hook(__FILE__, 'news_plugin_deactivate');

/**
 * Plugin activation
 */
function news_plugin_activate(): void {
    // Check requirements
    if (version_compare(PHP_VERSION, '8.1', '<') || version_compare(get_bloginfo('version'), '6.5', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('News Plugin requires PHP 8.1+ and WordPress 6.5+', 'news'));
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set default options
    add_option('news_plugin_version', NEWS_PLUGIN_VERSION);
    add_option('news_plugin_activated', time());
    
    // Create database tables if needed
    do_action('news_plugin_activate');
}

/**
 * Plugin deactivation
 */
function news_plugin_deactivate(): void {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Clear caches
    do_action('news_plugin_deactivate');
}
