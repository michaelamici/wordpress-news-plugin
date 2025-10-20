<?php
/**
 * Editorial Calendar Management
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Editorial;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles editorial calendar functionality
 */
class EditorialCalendar {
    
    /**
     * Initialize editorial calendar
     */
    public function __construct() {
        add_action('init', [$this, 'register_post_meta']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_calendar_assets']);
    }
    
    /**
     * Register post meta for editorial calendar
     */
    public function register_post_meta(): void {
        register_post_meta('news', 'editorial_status', [
            'type' => 'string',
            'default' => 'draft',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'description' => __('Editorial status of the article', 'news'),
        ]);
        
        register_post_meta('news', 'editorial_priority', [
            'type' => 'string',
            'default' => 'normal',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'description' => __('Editorial priority level', 'news'),
        ]);
        
        register_post_meta('news', 'editorial_deadline', [
            'type' => 'string',
            'default' => '',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'description' => __('Editorial deadline', 'news'),
        ]);
        
        register_post_meta('news', 'editorial_assignee', [
            'type' => 'integer',
            'default' => 0,
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'absint',
            'description' => __('Assigned editor/writer', 'news'),
        ]);
        
        register_post_meta('news', 'editorial_notes', [
            'type' => 'string',
            'default' => '',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_textarea_field',
            'description' => __('Editorial notes and comments', 'news'),
        ]);
    }
    
    /**
     * Register REST API routes for calendar
     */
    public function register_rest_routes(): void {
        register_rest_route('news/v1', '/editorial/calendar', [
            'methods' => 'GET',
            'callback' => [$this, 'get_calendar_data'],
            'permission_callback' => [$this, 'check_calendar_permissions'],
        ]);
        
        register_rest_route('news/v1', '/editorial/calendar', [
            'methods' => 'POST',
            'callback' => [$this, 'update_calendar_item'],
            'permission_callback' => [$this, 'check_calendar_permissions'],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'status' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'priority' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'deadline' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'assignee' => [
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'notes' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
            ],
        ]);
    }
    
    /**
     * Get calendar data
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_calendar_data(\WP_REST_Request $request): \WP_REST_Response {
        $start_date = $request->get_param('start') ?: date('Y-m-01');
        $end_date = $request->get_param('end') ?: date('Y-m-t');
        
        $posts = get_posts([
            'post_type' => 'news',
            'post_status' => ['draft', 'pending', 'publish'],
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'editorial_deadline',
                    'value' => [$start_date, $end_date],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ],
            ],
        ]);
        
        $calendar_data = [];
        
        foreach ($posts as $post) {
            $deadline = get_post_meta($post->ID, 'editorial_deadline', true);
            $status = get_post_meta($post->ID, 'editorial_status', true);
            $priority = get_post_meta($post->ID, 'editorial_priority', true);
            $assignee_id = get_post_meta($post->ID, 'editorial_assignee', true);
            $assignee = $assignee_id ? get_userdata($assignee_id) : null;
            
            $calendar_data[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'deadline' => $deadline,
                'status' => $status,
                'priority' => $priority,
                'assignee' => $assignee ? [
                    'id' => $assignee->ID,
                    'name' => $assignee->display_name,
                ] : null,
                'url' => get_edit_post_link($post->ID),
            ];
        }
        
        return rest_ensure_response($calendar_data);
    }
    
    /**
     * Update calendar item
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_calendar_item(\WP_REST_Request $request) {
        $post_id = $request->get_param('post_id');
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'news') {
            return new \WP_Error('invalid_post', __('Invalid post ID', 'news'), ['status' => 400]);
        }
        
        $status = $request->get_param('status');
        $priority = $request->get_param('priority');
        $deadline = $request->get_param('deadline');
        $assignee = $request->get_param('assignee');
        $notes = $request->get_param('notes');
        
        if ($status) {
            update_post_meta($post_id, 'editorial_status', $status);
        }
        
        if ($priority) {
            update_post_meta($post_id, 'editorial_priority', $priority);
        }
        
        if ($deadline) {
            update_post_meta($post_id, 'editorial_deadline', $deadline);
        }
        
        if ($assignee) {
            update_post_meta($post_id, 'editorial_assignee', $assignee);
        }
        
        if ($notes !== null) {
            update_post_meta($post_id, 'editorial_notes', $notes);
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => __('Calendar item updated successfully', 'news'),
        ]);
    }
    
    /**
     * Check calendar permissions
     *
     * @param \WP_REST_Request $request
     * @return bool
     */
    public function check_calendar_permissions(\WP_REST_Request $request): bool {
        return current_user_can('edit_news');
    }
    
    /**
     * Enqueue calendar assets
     */
    public function enqueue_calendar_assets(): void {
        $screen = get_current_screen();
        
        if (!$screen || strpos($screen->id, 'news') === false) {
            return;
        }
        
        wp_enqueue_script(
            'news-editorial-calendar',
            NEWS_PLUGIN_URL . 'src/Assets/js/editorial-calendar.js',
            ['jquery', 'wp-api'],
            NEWS_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'news-editorial-calendar',
            NEWS_PLUGIN_URL . 'src/Assets/css/editorial-calendar.css',
            [],
            NEWS_PLUGIN_VERSION
        );
        
        wp_localize_script('news-editorial-calendar', 'newsEditorial', [
            'apiUrl' => rest_url('news/v1/editorial/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
    
    /**
     * Get editorial statuses
     *
     * @return array
     */
    public static function get_editorial_statuses(): array {
        return [
            'draft' => __('Draft', 'news'),
            'assigned' => __('Assigned', 'news'),
            'in_progress' => __('In Progress', 'news'),
            'review' => __('Review', 'news'),
            'approved' => __('Approved', 'news'),
            'published' => __('Published', 'news'),
        ];
    }
    
    /**
     * Get editorial priorities
     *
     * @return array
     */
    public static function get_editorial_priorities(): array {
        return [
            'low' => __('Low', 'news'),
            'normal' => __('Normal', 'news'),
            'high' => __('High', 'news'),
            'urgent' => __('Urgent', 'news'),
        ];
    }
}
