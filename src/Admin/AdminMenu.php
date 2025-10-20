<?php
/**
 * Admin Menu for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles admin menu and pages
 */
class AdminMenu {
    
    /**
     * Initialize admin menu
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu(): void {
        // Main News menu
        add_menu_page(
            __('News Management', 'news'),
            __('News', 'news'),
            'edit_news',
            'news-management',
            [$this, 'render_news_dashboard'],
            'dashicons-media-document',
            5
        );
        
        // Fronts management
        add_submenu_page(
            'news-management',
            __('Fronts', 'news'),
            __('Fronts', 'news'),
            'manage_news_fronts',
            'news-fronts',
            [$this, 'render_fronts_page']
        );
        
        // Breaking alerts
        add_submenu_page(
            'news-management',
            __('Breaking Alerts', 'news'),
            __('Breaking Alerts', 'news'),
            'manage_news_fronts',
            'news-breaking',
            [$this, 'render_breaking_page']
        );
        
        // Placements
        add_submenu_page(
            'news-management',
            __('Placements', 'news'),
            __('Placements', 'news'),
            'manage_news_fronts',
            'news-placements',
            [$this, 'render_placements_page']
        );
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook_suffix Current page hook
     */
    public function enqueue_admin_assets(string $hook_suffix): void {
        if (strpos($hook_suffix, 'news-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'news-admin',
            NEWS_PLUGIN_URL . 'src/Assets/css/admin.css',
            [],
            NEWS_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'news-admin',
            NEWS_PLUGIN_URL . 'src/Assets/js/admin.js',
            ['jquery', 'wp-api'],
            NEWS_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('news-admin', 'newsAdmin', [
            'apiUrl' => rest_url('news/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'fronts' => \NewsPlugin\Includes\Options::get_fronts(),
            'breakingAlert' => \NewsPlugin\Includes\Options::get_breaking_alert(),
            'placements' => \NewsPlugin\Includes\PlacementsRegistry::get_placements(),
        ]);
    }
    
    /**
     * Render news dashboard
     */
    public function render_news_dashboard(): void {
        $stats = $this->get_news_stats();
        
        ?>
        <div class="wrap">
            <h1><?php _e('News Dashboard', 'news'); ?></h1>
            
            <div class="news-dashboard-stats">
                <div class="news-stat-box">
                    <h3><?php echo esc_html($stats['total_articles']); ?></h3>
                    <p><?php _e('Total Articles', 'news'); ?></p>
                </div>
                
                <div class="news-stat-box">
                    <h3><?php echo esc_html($stats['published_articles']); ?></h3>
                    <p><?php _e('Published', 'news'); ?></p>
                </div>
                
                <div class="news-stat-box">
                    <h3><?php echo esc_html($stats['total_sections']); ?></h3>
                    <p><?php _e('Sections', 'news'); ?></p>
                </div>
                
                <div class="news-stat-box">
                    <h3><?php echo esc_html($stats['breaking_articles']); ?></h3>
                    <p><?php _e('Breaking News', 'news'); ?></p>
                </div>
            </div>
            
            <div class="news-dashboard-actions">
                <a href="<?php echo admin_url('post-new.php?post_type=news'); ?>" class="button button-primary">
                    <?php _e('Add New Article', 'news'); ?>
                </a>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=news_section'); ?>" class="button">
                    <?php _e('Manage Sections', 'news'); ?>
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render fronts management page
     */
    public function render_fronts_page(): void {
        ?>
        <div class="wrap">
            <h1><?php _e('Fronts Management', 'news'); ?></h1>
            <div id="news-fronts-app"></div>
        </div>
        <?php
    }
    
    /**
     * Render breaking alerts page
     */
    public function render_breaking_page(): void {
        ?>
        <div class="wrap">
            <h1><?php _e('Breaking Alerts', 'news'); ?></h1>
            <div id="news-breaking-app"></div>
        </div>
        <?php
    }
    
    /**
     * Render placements page
     */
    public function render_placements_page(): void {
        ?>
        <div class="wrap">
            <h1><?php _e('Placements', 'news'); ?></h1>
            <div id="news-placements-app"></div>
        </div>
        <?php
    }
    
    /**
     * Get news statistics
     *
     * @return array
     */
    private function get_news_stats(): array {
        $total_articles = wp_count_posts('news');
        $published_articles = $total_articles->publish ?? 0;
        $total_articles_count = array_sum((array) $total_articles);
        
        $total_sections = wp_count_terms([
            'taxonomy' => 'news_section',
            'hide_empty' => false,
        ]);
        
        $breaking_articles = get_posts([
            'post_type' => 'news',
            'meta_query' => [
                [
                    'key' => 'is_breaking',
                    'value' => true,
                    'compare' => '=',
                ],
            ],
            'fields' => 'ids',
            'posts_per_page' => -1,
        ]);
        
        return [
            'total_articles' => $total_articles_count,
            'published_articles' => $published_articles,
            'total_sections' => $total_sections,
            'breaking_articles' => count($breaking_articles),
        ];
    }
}
