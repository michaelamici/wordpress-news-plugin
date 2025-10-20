<?php
/**
 * Performance Optimizer for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles performance optimization and monitoring
 */
class PerformanceOptimizer {
    
    /**
     * Initialize performance optimizer
     */
    public function __construct() {
        add_action('init', [$this, 'optimize_queries']);
        add_action('wp_enqueue_scripts', [$this, 'optimize_assets']);
        add_action('wp_head', [$this, 'add_performance_meta']);
    }
    
    /**
     * Optimize database queries
     */
    public function optimize_queries(): void {
        // Preload thumbnails for news posts
        add_action('pre_get_posts', [$this, 'preload_thumbnails']);
        
        // Optimize WP_Query for news posts
        add_action('pre_get_posts', [$this, 'optimize_news_queries']);
    }
    
    /**
     * Preload thumbnails for news posts
     *
     * @param \WP_Query $query Query object
     */
    public function preload_thumbnails(\WP_Query $query): void {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        if ($query->get('post_type') === 'news' || is_post_type_archive('news')) {
            $query->set('meta_query', [
                [
                    'key' => '_thumbnail_id',
                    'compare' => 'EXISTS',
                ],
            ]);
        }
    }
    
    /**
     * Optimize news queries
     *
     * @param \WP_Query $query Query object
     */
    public function optimize_news_queries(\WP_Query $query): void {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        if ($query->get('post_type') === 'news' || is_post_type_archive('news')) {
            // Only select necessary fields
            $query->set('fields', 'ids');
            
            // Add meta cache
            $query->set('update_post_meta_cache', true);
            $query->set('update_post_term_cache', true);
        }
    }
    
    /**
     * Optimize asset loading
     */
    public function optimize_assets(): void {
        // Only load assets on relevant pages
        if (!is_admin() && !is_singular('news') && !is_post_type_archive('news')) {
            return;
        }
        
        // Add preload hints for critical assets
        add_action('wp_head', [$this, 'add_preload_hints'], 1);
    }
    
    /**
     * Add preload hints
     */
    public function add_preload_hints(): void {
        if (is_singular('news') || is_post_type_archive('news')) {
            echo '<link rel="preload" href="' . NEWS_PLUGIN_URL . 'src/Assets/css/admin.css" as="style">' . "\n";
            echo '<link rel="preload" href="' . NEWS_PLUGIN_URL . 'src/Assets/js/blocks.js" as="script">' . "\n";
        }
    }
    
    /**
     * Add performance meta tags
     */
    public function add_performance_meta(): void {
        if (is_singular('news')) {
            $post_id = get_the_ID();
            $cache_time = get_post_meta($post_id, '_news_cache_time', true);
            
            if ($cache_time) {
                echo '<meta name="news-cache-time" content="' . esc_attr($cache_time) . '">' . "\n";
            }
        }
    }
    
    /**
     * Get performance metrics
     *
     * @return array
     */
    public static function get_performance_metrics(): array {
        global $wpdb;
        
        $metrics = [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'query_count' => get_num_queries(),
            'load_time' => timer_stop(0, 3),
        ];
        
        // Get news-specific metrics
        $news_count = wp_count_posts('news');
        $metrics['news_posts'] = array_sum((array) $news_count);
        
        $sections_count = wp_count_terms(['taxonomy' => 'news_section']);
        $metrics['news_sections'] = $sections_count;
        
        // Get cache hit ratio
        $cache_hits = wp_cache_get('news_cache_hits', 'news_plugin') ?: 0;
        $cache_misses = wp_cache_get('news_cache_misses', 'news_plugin') ?: 0;
        
        if ($cache_hits + $cache_misses > 0) {
            $metrics['cache_hit_ratio'] = round(($cache_hits / ($cache_hits + $cache_misses)) * 100, 2);
        } else {
            $metrics['cache_hit_ratio'] = 0;
        }
        
        return $metrics;
    }
    
    /**
     * Log cache hit
     */
    public static function log_cache_hit(): void {
        $hits = wp_cache_get('news_cache_hits', 'news_plugin') ?: 0;
        wp_cache_set('news_cache_hits', $hits + 1, 'news_plugin', 3600);
    }
    
    /**
     * Log cache miss
     */
    public static function log_cache_miss(): void {
        $misses = wp_cache_get('news_cache_misses', 'news_plugin') ?: 0;
        wp_cache_set('news_cache_misses', $misses + 1, 'news_plugin', 3600);
    }
}
