<?php
/**
 * Plugin Name: New Baltimore Gazette News
 * Description: News plugin for the New Baltimore Gazette - sections, fronts, articles, and placements.
 * Version: 0.2.0
 * Author: New Baltimore Gazette
 * License: Proprietary
 * Text Domain: news
 * Requires at least: 6.5
 * Tested up to: 6.6
 * Requires PHP: 8.1
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NEWS_PLUGIN_VERSION', '0.2.0');
define('NEWS_PLUGIN_FILE', __FILE__);
define('NEWS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NEWS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once NEWS_PLUGIN_DIR . 'src/Includes/Autoloader.php';
\NewsPlugin\Includes\Autoloader::register();

// Disable WordPress big image feature
add_filter('big_image_size_threshold', '__return_false');

// Load text domain and initialize components
add_action('init', function() {
    // Load text domain first
    load_plugin_textdomain('news', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize core components
    new \NewsPlugin\PostTypes\NewsPostType();
    new \NewsPlugin\PostTypes\NewsSection();
    new \NewsPlugin\Includes\Options();
    new \NewsPlugin\Includes\PlacementsRegistry();
    new \NewsPlugin\Includes\RestApi();
    new \NewsPlugin\Includes\CacheManager();
    
    // Initialize blocks and admin
    new \NewsPlugin\Blocks\FrontConfigBlock();
    new \NewsPlugin\Blocks\PlacementBlock();
    new \NewsPlugin\Admin\NewsArticlePanels();
    new \NewsPlugin\Admin\AdminMenu();
    new \NewsPlugin\Admin\EditorialCalendarPage();
    
    // Initialize security and performance
    new \NewsPlugin\Includes\SecurityManager();
    new \NewsPlugin\Includes\PerformanceOptimizer();
    
    // Initialize advanced features
    new \NewsPlugin\Widgets\BreakingNewsTicker();
    new \NewsPlugin\Includes\AdvancedPlacements();
    new \NewsPlugin\Includes\AnalyticsManager();
    
    // Initialize editorial workflow features (v0.2.0)
    new \NewsPlugin\Editorial\EditorialCalendar();
    new \NewsPlugin\Editorial\AuthorManager();
    new \NewsPlugin\Editorial\ContentWorkflow();
    
    // Initialize journalist role management
    new \NewsPlugin\Includes\JournalistRole();
    
    // Initialize role switcher for testing
    new \NewsPlugin\Admin\RoleSwitcher();
});

// Activation/Deactivation hooks
register_activation_hook(__FILE__, 'news_plugin_activate');
register_deactivation_hook(__FILE__, 'news_plugin_deactivate');

/**
 * Plugin activation
 */
function news_plugin_activate(): void {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set default options
    $options = new \NewsPlugin\Includes\Options();
    $options->register_options();
}

/**
 * Plugin deactivation
 */
function news_plugin_deactivate(): void {
    // Flush rewrite rules
    flush_rewrite_rules();
}
