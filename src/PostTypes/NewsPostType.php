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
        add_filter('map_meta_cap', [$this, 'map_meta_cap'], 10, 4);
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
            'capability_type' => 'news',
            'map_meta_cap' => false,
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
    
    /**
     * Map meta capabilities for news posts
     *
     * @param array $caps Required capabilities
     * @param string $cap Capability being checked
     * @param int $user_id User ID
     * @param array $args Additional arguments
     * @return array
     */
    public function map_meta_cap(array $caps, string $cap, int $user_id, array $args): array {
        // Only handle news post capabilities
        if (strpos($cap, 'news_') !== 0) {
            return $caps;
        }
        
        $post_id = $args[0] ?? 0;
        
        switch ($cap) {
            case 'edit_news':
                if ($post_id) {
                    $post = get_post($post_id);
                    if ($post && $post->post_type === self::POST_TYPE) {
                        if ($post->post_author == $user_id) {
                            $caps[] = 'edit_news';
                        } else {
                            $caps[] = 'edit_others_news';
                        }
                    }
                } else {
                    $caps[] = 'edit_news';
                }
                break;
                
            case 'delete_news':
                if ($post_id) {
                    $post = get_post($post_id);
                    if ($post && $post->post_type === self::POST_TYPE) {
                        if ($post->post_author == $user_id) {
                            $caps[] = 'delete_news';
                        } else {
                            $caps[] = 'delete_others_news';
                        }
                    }
                } else {
                    $caps[] = 'delete_news';
                }
                break;
                
            case 'read_news':
                if ($post_id) {
                    $post = get_post($post_id);
                    if ($post && $post->post_type === self::POST_TYPE) {
                        if ($post->post_author == $user_id) {
                            $caps[] = 'read_news';
                        } else {
                            $caps[] = 'read_others_news';
                        }
                    }
                } else {
                    $caps[] = 'read_news';
                }
                break;
                
            case 'publish_news':
                $caps[] = 'publish_news';
                break;
                
            case 'edit_others_news':
                $caps[] = 'edit_others_news';
                break;
                
            case 'delete_others_news':
                $caps[] = 'delete_others_news';
                break;
                
            case 'read_others_news':
                $caps[] = 'read_others_news';
                break;
        }
        
        return $caps;
    }
}
