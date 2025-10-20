<?php
/**
 * Cache Manager for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles cache invalidation and management
 */
class CacheManager {
    
    /**
     * Initialize cache management
     */
    public function __construct() {
        add_action('save_post', [$this, 'invalidate_on_post_save'], 10, 2);
        add_action('edited_terms', [$this, 'invalidate_on_term_edit'], 10, 2);
        add_action('update_option_news_fronts', [$this, 'invalidate_fronts_cache']);
        add_action('update_option_news_breaking_alert', [$this, 'invalidate_breaking_cache']);
    }
    
    /**
     * Invalidate cache on post save
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     */
    public function invalidate_on_post_save(int $post_id, \WP_Post $post): void {
        if ($post->post_type !== 'news') {
            return;
        }
        
        // Clear all front caches
        \NewsPlugin\Fronts\FrontManager::clear_all_caches();
        
        // Clear object cache if available
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete('news_fronts', 'news_plugin');
        }
    }
    
    /**
     * Invalidate cache on term edit
     *
     * @param int $term_id Term ID
     * @param string $taxonomy Taxonomy name
     */
    public function invalidate_on_term_edit(int $term_id, string $taxonomy): void {
        if ($taxonomy !== 'news_section') {
            return;
        }
        
        // Clear all front caches
        \NewsPlugin\Fronts\FrontManager::clear_all_caches();
        
        // Clear object cache if available
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete('news_fronts', 'news_plugin');
        }
    }
    
    /**
     * Invalidate fronts cache when fronts config changes
     */
    public function invalidate_fronts_cache(): void {
        \NewsPlugin\Fronts\FrontManager::clear_all_caches();
        
        // Clear object cache if available
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete('news_fronts', 'news_plugin');
        }
    }
    
    /**
     * Invalidate breaking alert cache
     */
    public function invalidate_breaking_cache(): void {
        // Clear breaking alert transients
        delete_transient('news_breaking_alert');
        
        // Clear object cache if available
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete('news_breaking_alert', 'news_plugin');
        }
    }
    
    /**
     * Clear all plugin caches
     */
    public static function clear_all_caches(): void {
        // Clear front caches
        \NewsPlugin\Fronts\FrontManager::clear_all_caches();
        
        // Clear breaking alert cache
        delete_transient('news_breaking_alert');
        
        // Clear object cache if available
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('news_plugin');
        }
    }
    
    /**
     * Get cache key for fronts
     *
     * @param string $front_id Front ID
     * @param string $type Cache type
     * @return string
     */
    public static function get_front_cache_key(string $front_id, string $type): string {
        $hash = md5(serialize(\NewsPlugin\Includes\Options::get_fronts()));
        return "news_front_{$front_id}_{$type}_{$hash}";
    }
    
    /**
     * Get cache key for breaking alert
     *
     * @return string
     */
    public static function get_breaking_cache_key(): string {
        return 'news_breaking_alert';
    }
}
