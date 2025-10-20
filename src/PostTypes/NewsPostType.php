<?php
/**
 * News Post Type Registration
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the news custom post type registration
 */
class NewsPostType {
    
    /**
     * Post type slug
     */
    public const POST_TYPE = 'news';
    
    /**
     * Initialize the post type
     */
    public function __construct() {
        $this->register_post_type();
        $this->register_post_meta();
    }
    
    /**
     * Register the news post type
     */
    public function register_post_type(): void {
        $args = [
            'labels' => [
                'name' => __('News Articles', 'news'),
                'singular_name' => __('News Article', 'news'),
                'menu_name' => __('News', 'news'),
                'add_new' => __('Add New Article', 'news'),
                'add_new_item' => __('Add New Article', 'news'),
                'edit_item' => __('Edit Article', 'news'),
                'new_item' => __('New Article', 'news'),
                'view_item' => __('View Article', 'news'),
                'search_items' => __('Search Articles', 'news'),
                'not_found' => __('No articles found', 'news'),
                'not_found_in_trash' => __('No articles found in trash', 'news'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'news', 'with_front' => false],
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-media-document',
            'supports' => [
                'title',
                'editor',
                'excerpt',
                'thumbnail',
                'author',
                'revisions',
                'custom-fields',
                'page-attributes',
            ],
            'taxonomies' => ['news_section'],
        ];
        
        register_post_type(self::POST_TYPE, $args);
    }
    
    /**
     * Register post meta for news articles
     */
    public function register_post_meta(): void {
        $meta_fields = [
            'is_featured' => [
                'type' => 'boolean',
                'default' => false,
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'is_breaking' => [
                'type' => 'boolean',
                'default' => false,
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'is_exclusive' => [
                'type' => 'boolean',
                'default' => false,
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'is_sponsored' => [
                'type' => 'boolean',
                'default' => false,
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'dek' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'byline' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'location' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'embargo_date' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'expire_date' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
        
        foreach ($meta_fields as $meta_key => $args) {
            register_post_meta(self::POST_TYPE, $meta_key, $args);
        }
    }
    
}
