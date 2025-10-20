<?php
/**
 * News Article Editor Panels
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds editor panels for news article settings
 */
class NewsArticlePanels {
    
    /**
     * Initialize the panels
     */
    public function __construct() {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('init', [$this, 'register_meta_fields']);
    }
    
    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets(): void {
        global $post;
        
        if (!$post || $post->post_type !== 'news') {
            return;
        }
        
        wp_enqueue_script(
            'news-editor',
            NEWS_PLUGIN_URL . 'src/Assets/js/editor.js',
            ['wp-edit-post', 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-i18n'],
            NEWS_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('news-editor', 'newsEditor', [
            'postId' => $post->ID,
            'meta' => [
                'is_featured' => get_post_meta($post->ID, 'is_featured', true),
                'is_breaking' => get_post_meta($post->ID, 'is_breaking', true),
                'is_exclusive' => get_post_meta($post->ID, 'is_exclusive', true),
                'is_sponsored' => get_post_meta($post->ID, 'is_sponsored', true),
                'dek' => get_post_meta($post->ID, 'dek', true),
                'byline' => get_post_meta($post->ID, 'byline', true),
                'location' => get_post_meta($post->ID, 'location', true),
                'embargo_date' => get_post_meta($post->ID, 'embargo_date', true),
                'expire_date' => get_post_meta($post->ID, 'expire_date', true),
            ],
            'sections' => $this->get_sections_data(),
            'apiUrl' => rest_url('news/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
    
    /**
     * Register meta fields for the editor
     */
    public function register_meta_fields(): void {
        $meta_fields = [
            'is_featured',
            'is_breaking', 
            'is_exclusive',
            'is_sponsored',
            'dek',
            'byline',
            'location',
            'embargo_date',
            'expire_date',
        ];
        
        foreach ($meta_fields as $field) {
            register_meta('post', $field, [
                'type' => $this->get_meta_type($field),
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => $this->get_sanitize_callback($field),
            ]);
        }
    }
    
    /**
     * Get sections data for the editor
     *
     * @return array
     */
    private function get_sections_data(): array {
        $sections = get_terms([
            'taxonomy' => 'news_section',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
        
        if (is_wp_error($sections)) {
            return [];
        }
        
        $sections_data = [];
        foreach ($sections as $section) {
            $sections_data[] = [
                'id' => $section->term_id,
                'name' => $section->name,
                'slug' => $section->slug,
                'parent' => $section->parent,
            ];
        }
        
        return $sections_data;
    }
    
    /**
     * Get meta field type
     *
     * @param string $field Field name
     * @return string
     */
    private function get_meta_type(string $field): string {
        $boolean_fields = ['is_featured', 'is_breaking', 'is_exclusive', 'is_sponsored'];
        
        return in_array($field, $boolean_fields) ? 'boolean' : 'string';
    }
    
    /**
     * Get sanitize callback for field
     *
     * @param string $field Field name
     * @return callable
     */
    private function get_sanitize_callback(string $field): callable {
        $boolean_fields = ['is_featured', 'is_breaking', 'is_exclusive', 'is_sponsored'];
        
        if (in_array($field, $boolean_fields)) {
            return 'rest_sanitize_boolean';
        }
        
        if ($field === 'location') {
            return 'sanitize_text_field';
        }
        
        if (in_array($field, ['embargo_date', 'expire_date'])) {
            return 'sanitize_text_field';
        }
        
        return 'sanitize_textarea_field';
    }
}
