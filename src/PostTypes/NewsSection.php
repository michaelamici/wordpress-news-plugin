<?php
/**
 * News Section Taxonomy Registration
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the news_section taxonomy registration
 */
class NewsSection {
    
    /**
     * Taxonomy slug
     */
    public const TAXONOMY = 'news_section';
    
    /**
     * Initialize the taxonomy
     */
    public function __construct() {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('init', [$this, 'register_term_meta']);
    }
    
    /**
     * Register the news_section taxonomy
     */
    public function register_taxonomy(): void {
        $args = [
            'labels' => [
                'name' => __('News Sections', 'news'),
                'singular_name' => __('News Section', 'news'),
                'menu_name' => __('Sections', 'news'),
                'all_items' => __('All Sections', 'news'),
                'edit_item' => __('Edit Section', 'news'),
                'view_item' => __('View Section', 'news'),
                'update_item' => __('Update Section', 'news'),
                'add_new_item' => __('Add New Section', 'news'),
                'new_item_name' => __('New Section Name', 'news'),
                'search_items' => __('Search Sections', 'news'),
                'popular_items' => __('Popular Sections', 'news'),
                'separate_items_with_commas' => __('Separate sections with commas', 'news'),
                'add_or_remove_items' => __('Add or remove sections', 'news'),
                'choose_from_most_used' => __('Choose from most used sections', 'news'),
                'not_found' => __('No sections found', 'news'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => false,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'hierarchical' => true,
            'rewrite' => [
                'slug' => 'section',
                'with_front' => false,
                'hierarchical' => true,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_news_sections',
                'edit_terms' => 'manage_news_sections',
                'delete_terms' => 'manage_news_sections',
                'assign_terms' => 'edit_news',
            ],
        ];
        
        register_taxonomy(self::TAXONOMY, ['news'], $args);
    }
    
    /**
     * Register term meta for news sections
     */
    public function register_term_meta(): void {
        $meta_fields = [
            'display_name' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'order' => [
                'type' => 'integer',
                'default' => 0,
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'absint',
            ],
            'visibility' => [
                'type' => 'string',
                'default' => 'public',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'front_config' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'description_short' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'default_color' => [
                'type' => 'string',
                'default' => '',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_hex_color',
            ],
        ];
        
        foreach ($meta_fields as $meta_key => $args) {
            register_term_meta(self::TAXONOMY, $meta_key, $args);
        }
    }
}
