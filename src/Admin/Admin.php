<?php

declare(strict_types=1);

namespace NewsPlugin\Admin;

use NewsPlugin\Core\Plugin;
use NewsPlugin\Security\SecurityManager;
use NewsPlugin\Assets\AssetManager;

/**
 * Admin class
 * 
 * Handles all admin-related functionality
 */
class Admin
{
    /**
     * Plugin instance
     */
    private Plugin $plugin;

    /**
     * Security manager
     */
    private SecurityManager $security;

    /**
     * Asset manager
     */
    private AssetManager $assets;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin = Plugin::instance();
        $this->security = $this->plugin->getSecurityManager();
        $this->assets = $this->plugin->getAssetManager();
        
        $this->init();
    }

    /**
     * Initialize admin
     */
    private function init(): void
    {
        // Add admin hooks
        add_action('admin_menu', [$this, 'addAdminMenus']);
        add_action('admin_init', [$this, 'initSettings']);
        add_action('admin_notices', [$this, 'showAdminNotices']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Add meta boxes
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        
        // REST API support is handled by meta field registration in PostTypes.php
        
        // Add admin AJAX handlers
        add_action('wp_ajax_news_admin_action', [$this, 'handleAjaxAction']);
    }

    /**
     * Add admin menus
     */
    public function addAdminMenus(): void
    {
        // Main menu page
        add_menu_page(
            __('News Plugin', 'news'),
            __('News', 'news'),
            'manage_news',
            'news-dashboard',
            [$this, 'renderDashboardPage'],
            'dashicons-megaphone',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'news-dashboard',
            __('Dashboard', 'news'),
            __('Dashboard', 'news'),
            'manage_news',
            'news-dashboard',
            [$this, 'renderDashboardPage']
        );

        // Articles submenu
        add_submenu_page(
            'news-dashboard',
            __('Articles', 'news'),
            __('Articles', 'news'),
            'edit_news',
            'news-articles',
            [$this, 'renderArticlesPage']
        );

        // Sections submenu
        add_submenu_page(
            'news-dashboard',
            __('Sections', 'news'),
            __('Sections', 'news'),
            'manage_news',
            'news-sections',
            [$this, 'renderSectionsPage']
        );

        // Settings submenu
        add_submenu_page(
            'news-dashboard',
            __('Settings', 'news'),
            __('Settings', 'news'),
            'manage_news',
            'news-settings',
            [$this, 'renderSettingsPage']
        );

        // Analytics submenu
        add_submenu_page(
            'news-dashboard',
            __('Analytics', 'news'),
            __('Analytics', 'news'),
            'manage_news',
            'news-analytics',
            [$this, 'renderAnalyticsPage']
        );
    }

    /**
     * Initialize settings
     */
    public function initSettings(): void
    {
        // Register settings
        register_setting('news_settings', 'news_settings', [
            'sanitize_callback' => [$this, 'sanitizeSettings'],
        ]);

        // Add settings sections
        add_settings_section(
            'news_general',
            __('General Settings', 'news'),
            [$this, 'renderGeneralSection'],
            'news_settings'
        );

        add_settings_section(
            'news_display',
            __('Display Settings', 'news'),
            [$this, 'renderDisplaySection'],
            'news_settings'
        );

        // Add settings fields
        $this->addSettingsFields();
    }

    /**
     * Add settings fields
     */
    private function addSettingsFields(): void
    {
        // General settings
        add_settings_field(
            'enable_blocks',
            __('Enable Blocks', 'news'),
            [$this, 'renderCheckboxField'],
            'news_settings',
            'news_general',
            [
                'name' => 'enable_blocks',
                'description' => __('Enable Gutenberg blocks for news content', 'news'),
            ]
        );

        add_settings_field(
            'enable_widgets',
            __('Enable Widgets', 'news'),
            [$this, 'renderCheckboxField'],
            'news_settings',
            'news_general',
            [
                'name' => 'enable_widgets',
                'description' => __('Enable widgets for news content', 'news'),
            ]
        );

        add_settings_field(
            'enable_rest_api',
            __('Enable REST API', 'news'),
            [$this, 'renderCheckboxField'],
            'news_settings',
            'news_general',
            [
                'name' => 'enable_rest_api',
                'description' => __('Enable REST API endpoints', 'news'),
            ]
        );

        // Display settings
        add_settings_field(
            'articles_per_page',
            __('Articles Per Page', 'news'),
            [$this, 'renderNumberField'],
            'news_settings',
            'news_display',
            [
                'name' => 'articles_per_page',
                'description' => __('Number of articles to display per page', 'news'),
                'min' => 1,
                'max' => 100,
            ]
        );

        add_settings_field(
            'cache_duration',
            __('Cache Duration (seconds)', 'news'),
            [$this, 'renderNumberField'],
            'news_settings',
            'news_display',
            [
                'name' => 'cache_duration',
                'description' => __('How long to cache content (in seconds)', 'news'),
                'min' => 60,
                'max' => 86400,
            ]
        );
    }

    /**
     * Render dashboard page
     */
    public function renderDashboardPage(): void
    {
        $stats = $this->getDashboardStats();
        
        include NEWS_PLUGIN_DIR . 'src/Templates/admin/dashboard.php';
    }

    /**
     * Render articles page
     */
    public function renderArticlesPage(): void
    {
        $articles = $this->getArticles();
        
        include NEWS_PLUGIN_DIR . 'src/Templates/admin/articles.php';
    }

    /**
     * Render sections page
     */
    public function renderSectionsPage(): void
    {
        $sections = $this->getSections();
        
        include NEWS_PLUGIN_DIR . 'src/Templates/admin/sections.php';
    }

    /**
     * Render settings page
     */
    public function renderSettingsPage(): void
    {
        $settings = get_option('news_settings', []);
        
        include NEWS_PLUGIN_DIR . 'src/Templates/admin/settings.php';
    }

    /**
     * Render analytics page
     */
    public function renderAnalyticsPage(): void
    {
        $analytics = $this->getAnalytics();
        
        include NEWS_PLUGIN_DIR . 'src/Templates/admin/analytics.php';
    }

    /**
     * Add meta boxes
     */
    public function addMetaBoxes(): void
    {
        add_meta_box(
            'news_article_meta',
            __('Article Settings', 'news'),
            [$this, 'renderArticleMetaBox'],
            'news',
            'normal',
            'high'
        );
    }

    /**
     * Render article meta box
     */
    public function renderArticleMetaBox($post): void
    {
        // The template now handles getting individual meta fields directly
        include NEWS_PLUGIN_DIR . 'src/Templates/admin/meta-boxes/article-meta.php';
    }



    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets(): void
    {
        $this->assets->enqueueAdminAssets();
    }

    /**
     * Show admin notices
     */
    public function showAdminNotices(): void
    {
        $screen = get_current_screen();
        
        if ($screen->id === 'news-dashboard') {
            $this->showDashboardNotices();
        }
    }

    /**
     * Show dashboard notices
     */
    private function showDashboardNotices(): void
    {
        $settings = get_option('news_settings', []);
        
        if (empty($settings['enable_blocks'])) {
            echo '<div class="notice notice-warning"><p>';
            echo esc_html__('News blocks are disabled. Enable them in settings for full functionality.', 'news');
            echo '</p></div>';
        }
    }

    /**
     * Handle AJAX actions
     */
    public function handleAjaxAction(): void
    {
        if (!$this->security->canManageNews()) {
            wp_die('Insufficient permissions', 'Permission Error', ['response' => 403]);
        }

        $action = $_POST['action'] ?? '';
        $data = $_POST['data'] ?? [];

        switch ($action) {
            case 'news_admin_action':
                $this->processAdminAction($data);
                break;
            default:
                wp_die('Invalid action', 'Error', ['response' => 400]);
        }
    }

    /**
     * Process admin action
     */
    private function processAdminAction(array $data): void
    {
        $action_type = $data['type'] ?? '';
        
        switch ($action_type) {
            case 'clear_cache':
                // Cache functionality removed
                wp_send_json_success(['message' => __('Cache functionality has been removed', 'news')]);
                break;
            default:
                wp_send_json_error(['message' => __('Invalid action type', 'news')]);
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        return [
            'total_articles' => wp_count_posts('news')->publish,
            'total_sections' => wp_count_terms('news_section'),
            'cache_stats' => ['status' => 'disabled'],
            'database_stats' => $this->plugin->getDatabaseManager()->getStats(),
        ];
    }

    /**
     * Get articles
     */
    private function getArticles(): array
    {
        $query = new \WP_Query([
            'post_type' => 'news',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        ]);

        return $query->posts;
    }

    /**
     * Get sections
     */
    private function getSections(): array
    {
        return get_terms([
            'taxonomy' => 'news_section',
            'hide_empty' => false,
        ]);
    }

    /**
     * Get analytics data
     */
    private function getAnalytics(): array
    {
        // This would typically fetch from the analytics table
        return [
            'page_views' => 0,
            'unique_visitors' => 0,
            'popular_articles' => [],
        ];
    }

    /**
     * Sanitize settings
     */
    public function sanitizeSettings(array $input): array
    {
        $sanitized = [];
        
        $sanitized['enable_blocks'] = $this->security->sanitizeBool($input['enable_blocks'] ?? false);
        $sanitized['enable_widgets'] = $this->security->sanitizeBool($input['enable_widgets'] ?? false);
        $sanitized['enable_rest_api'] = $this->security->sanitizeBool($input['enable_rest_api'] ?? false);
        $sanitized['articles_per_page'] = $this->security->sanitizeInt($input['articles_per_page'] ?? 10);
        $sanitized['cache_duration'] = $this->security->sanitizeInt($input['cache_duration'] ?? 3600);
        
        return $sanitized;
    }

    /**
     * Render checkbox field
     */
    public function renderCheckboxField(array $args): void
    {
        $name = $args['name'];
        $settings = get_option('news_settings', []);
        $value = $settings[$name] ?? false;
        
        echo '<input type="checkbox" name="news_settings[' . esc_attr($name) . ']" value="1" ' . checked($value, true, false) . ' />';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render number field
     */
    public function renderNumberField(array $args): void
    {
        $name = $args['name'];
        $settings = get_option('news_settings', []);
        $value = $settings[$name] ?? '';
        
        $min = $args['min'] ?? '';
        $max = $args['max'] ?? '';
        
        echo '<input type="number" name="news_settings[' . esc_attr($name) . ']" value="' . esc_attr($value) . '"';
        
        if ($min !== '') {
            echo ' min="' . esc_attr($min) . '"';
        }
        
        if ($max !== '') {
            echo ' max="' . esc_attr($max) . '"';
        }
        
        echo ' />';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render general section
     */
    public function renderGeneralSection(): void
    {
        echo '<p>' . esc_html__('Configure general plugin settings', 'news') . '</p>';
    }

    /**
     * Render display section
     */
    public function renderDisplaySection(): void
    {
        echo '<p>' . esc_html__('Configure display and performance settings', 'news') . '</p>';
    }
}
