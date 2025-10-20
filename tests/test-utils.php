<?php
/**
 * Test utilities for News Plugin
 *
 * @package NewsPlugin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test utilities class
 */
class News_Test_Utils {
    
    /**
     * Create a test news article
     *
     * @param array $args Article arguments
     * @return int Post ID
     */
    public static function create_news_article(array $args = []): int {
        $defaults = [
            'post_title' => 'Test News Article',
            'post_content' => 'This is test content for a news article.',
            'post_excerpt' => 'Test excerpt',
            'post_status' => 'publish',
            'post_type' => 'news',
            'post_author' => 1,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $post_id = wp_insert_post($args);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Set default meta
            $meta_defaults = [
                'is_featured' => false,
                'is_breaking' => false,
                'is_exclusive' => false,
                'dek' => 'Test dek',
                'byline' => 'Test Author',
                'location' => 'Test Location',
            ];
            
            foreach ($meta_defaults as $key => $value) {
                if (!isset($args['meta'][$key])) {
                    update_post_meta($post_id, $key, $value);
                }
            }
            
            // Set custom meta if provided
            if (isset($args['meta'])) {
                foreach ($args['meta'] as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }
            }
        }
        
        return $post_id;
    }
    
    /**
     * Create a test news section
     *
     * @param array $args Section arguments
     * @return int|WP_Error Term ID or error
     */
    public static function create_news_section(array $args = []): int {
        $defaults = [
            'name' => 'Test Section',
            'description' => 'Test section description',
            'slug' => 'test-section',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $term = wp_insert_term($args['name'], 'news_section', [
            'description' => $args['description'],
            'slug' => $args['slug'],
        ]);
        
        if (is_wp_error($term)) {
            return $term;
        }
        
        $term_id = $term['term_id'];
        
        // Set term meta if provided
        if (isset($args['meta'])) {
            foreach ($args['meta'] as $key => $value) {
                update_term_meta($term_id, $key, $value);
            }
        }
        
        return $term_id;
    }
    
    /**
     * Create test user with specific capabilities
     *
     * @param array $capabilities User capabilities
     * @return int User ID
     */
    public static function create_test_user(array $capabilities = []): int {
        $user_id = wp_create_user('testuser' . wp_rand(), 'password', 'test@example.com');
        
        if ($user_id && !is_wp_error($user_id)) {
            $user = new WP_User($user_id);
            
            foreach ($capabilities as $cap) {
                $user->add_cap($cap);
            }
        }
        
        return $user_id;
    }
    
    /**
     * Clean up test data
     */
    public static function cleanup_test_data(): void {
        // Remove test posts
        $posts = get_posts([
            'post_type' => 'news',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_test_post',
                    'value' => true,
                    'compare' => '=',
                ],
            ],
        ]);
        
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
        
        // Remove test terms
        $terms = get_terms([
            'taxonomy' => 'news_section',
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key' => '_test_term',
                    'value' => true,
                    'compare' => '=',
                ],
            ],
        ]);
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, 'news_section');
            }
        }
        
        // Remove test users
        $users = get_users([
            'meta_query' => [
                [
                    'key' => '_test_user',
                    'value' => true,
                    'compare' => '=',
                ],
            ],
        ]);
        
        foreach ($users as $user) {
            wp_delete_user($user->ID);
        }
    }
    
    /**
     * Mock WordPress functions for testing
     */
    public static function mock_wp_functions(): void {
        if (!function_exists('wp_rand')) {
            function wp_rand($min = 0, $max = 2147483647) {
                return rand($min, $max);
            }
        }
    }
    
    /**
     * Assert that a post has specific meta values
     *
     * @param int $post_id Post ID
     * @param array $expected_meta Expected meta values
     */
    public static function assert_post_meta($post_id, array $expected_meta): void {
        foreach ($expected_meta as $key => $expected_value) {
            $actual_value = get_post_meta($post_id, $key, true);
            PHPUnit\Framework\Assert::assertEquals($expected_value, $actual_value, "Meta key '$key' does not match expected value");
        }
    }
    
    /**
     * Assert that a term has specific meta values
     *
     * @param int $term_id Term ID
     * @param array $expected_meta Expected meta values
     */
    public static function assert_term_meta($term_id, array $expected_meta): void {
        foreach ($expected_meta as $key => $expected_value) {
            $actual_value = get_term_meta($term_id, $key, true);
            PHPUnit\Framework\Assert::assertEquals($expected_value, $actual_value, "Term meta key '$key' does not match expected value");
        }
    }
}
