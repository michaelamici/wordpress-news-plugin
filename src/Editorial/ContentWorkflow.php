<?php
/**
 * Content Workflow Management
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Editorial;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles content approval workflow
 */
class ContentWorkflow {
    
    /**
     * Workflow statuses
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_REVIEW = 'review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';
    
    /**
     * Initialize content workflow
     */
    public function __construct() {
        add_action('init', [$this, 'register_post_meta']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('transition_post_status', [$this, 'handle_status_transition'], 10, 3);
        add_action('admin_notices', [$this, 'show_workflow_notices']);
    }
    
    /**
     * Register post meta for workflow
     */
    public function register_post_meta(): void {
        register_post_meta('news', 'workflow_status', [
            'type' => 'string',
            'default' => self::STATUS_DRAFT,
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'description' => __('Content workflow status', 'news'),
        ]);
        
        register_post_meta('news', 'workflow_assignee', [
            'type' => 'integer',
            'default' => 0,
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'absint',
            'description' => __('Assigned editor/writer', 'news'),
        ]);
        
        register_post_meta('news', 'workflow_reviewer', [
            'type' => 'integer',
            'default' => 0,
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'absint',
            'description' => __('Assigned reviewer', 'news'),
        ]);
        
        register_post_meta('news', 'workflow_notes', [
            'type' => 'string',
            'default' => '',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_textarea_field',
            'description' => __('Workflow notes and comments', 'news'),
        ]);
        
        register_post_meta('news', 'workflow_history', [
            'type' => 'array',
            'default' => [],
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => [$this, 'sanitize_workflow_history'],
            'description' => __('Workflow history log', 'news'),
        ]);
    }
    
    /**
     * Register REST API routes for workflow
     */
    public function register_rest_routes(): void {
        register_rest_route('news/v1', '/workflow/statuses', [
            'methods' => 'GET',
            'callback' => [$this, 'get_workflow_statuses'],
            'permission_callback' => [$this, 'check_workflow_permissions'],
        ]);
        
        register_rest_route('news/v1', '/workflow/update', [
            'methods' => 'POST',
            'callback' => [$this, 'update_workflow_status'],
            'permission_callback' => [$this, 'check_workflow_permissions'],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'status' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'notes' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
            ],
        ]);
        
        register_rest_route('news/v1', '/workflow/assign', [
            'methods' => 'POST',
            'callback' => [$this, 'assign_workflow'],
            'permission_callback' => [$this, 'check_workflow_permissions'],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'assignee' => [
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'reviewer' => [
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }
    
    /**
     * Get workflow statuses
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_workflow_statuses(\WP_REST_Request $request): \WP_REST_Response {
        $statuses = [
            self::STATUS_DRAFT => __('Draft', 'news'),
            self::STATUS_ASSIGNED => __('Assigned', 'news'),
            self::STATUS_IN_PROGRESS => __('In Progress', 'news'),
            self::STATUS_REVIEW => __('Review', 'news'),
            self::STATUS_APPROVED => __('Approved', 'news'),
            self::STATUS_PUBLISHED => __('Published', 'news'),
            self::STATUS_REJECTED => __('Rejected', 'news'),
        ];
        
        return rest_ensure_response($statuses);
    }
    
    /**
     * Update workflow status
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_workflow_status(\WP_REST_Request $request) {
        $post_id = $request->get_param('post_id');
        $status = $request->get_param('status');
        $notes = $request->get_param('notes');
        
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'news') {
            return new \WP_Error('invalid_post', __('Invalid post ID', 'news'), ['status' => 400]);
        }
        
        $current_status = get_post_meta($post_id, 'workflow_status', true);
        
        // Validate status transition
        if (!$this->is_valid_transition($current_status, $status)) {
            return new \WP_Error('invalid_transition', __('Invalid status transition', 'news'), ['status' => 400]);
        }
        
        // Update status
        update_post_meta($post_id, 'workflow_status', $status);
        
        // Add to workflow history
        $this->add_workflow_history($post_id, $status, $notes);
        
        // Send notifications
        $this->send_workflow_notification($post_id, $status, $notes);
        
        return rest_ensure_response([
            'success' => true,
            'message' => __('Workflow status updated successfully', 'news'),
            'new_status' => $status,
        ]);
    }
    
    /**
     * Assign workflow
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function assign_workflow(\WP_REST_Request $request) {
        $post_id = $request->get_param('post_id');
        $assignee = $request->get_param('assignee');
        $reviewer = $request->get_param('reviewer');
        
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'news') {
            return new \WP_Error('invalid_post', __('Invalid post ID', 'news'), ['status' => 400]);
        }
        
        if ($assignee) {
            update_post_meta($post_id, 'workflow_assignee', $assignee);
        }
        
        if ($reviewer) {
            update_post_meta($post_id, 'workflow_reviewer', $reviewer);
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => __('Workflow assignment updated successfully', 'news'),
        ]);
    }
    
    /**
     * Handle status transition
     *
     * @param string $new_status
     * @param string $old_status
     * @param \WP_Post $post
     */
    public function handle_status_transition(string $new_status, string $old_status, \WP_Post $post): void {
        if ($post->post_type !== 'news') {
            return;
        }
        
        // Update workflow status based on post status
        $workflow_status = get_post_meta($post->ID, 'workflow_status', true);
        
        switch ($new_status) {
            case 'publish':
                if ($workflow_status !== self::STATUS_PUBLISHED) {
                    update_post_meta($post->ID, 'workflow_status', self::STATUS_PUBLISHED);
                    $this->add_workflow_history($post->ID, self::STATUS_PUBLISHED, 'Published automatically');
                }
                break;
            case 'draft':
                if ($workflow_status === self::STATUS_PUBLISHED) {
                    update_post_meta($post->ID, 'workflow_status', self::STATUS_DRAFT);
                    $this->add_workflow_history($post->ID, self::STATUS_DRAFT, 'Unpublished');
                }
                break;
        }
    }
    
    /**
     * Show workflow notices
     */
    public function show_workflow_notices(): void {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'post' || get_post_type() !== 'news') {
            return;
        }
        
        $post_id = get_the_ID();
        $workflow_status = get_post_meta($post_id, 'workflow_status', true);
        $workflow_notes = get_post_meta($post_id, 'workflow_notes', true);
        
        if ($workflow_status && $workflow_status !== self::STATUS_DRAFT) {
            $status_labels = [
                self::STATUS_ASSIGNED => __('Assigned', 'news'),
                self::STATUS_IN_PROGRESS => __('In Progress', 'news'),
                self::STATUS_REVIEW => __('Review', 'news'),
                self::STATUS_APPROVED => __('Approved', 'news'),
                self::STATUS_PUBLISHED => __('Published', 'news'),
                self::STATUS_REJECTED => __('Rejected', 'news'),
            ];
            
            $status_label = $status_labels[$workflow_status] ?? $workflow_status;
            
            echo '<div class="notice notice-info">';
            echo '<p><strong>' . __('Workflow Status:', 'news') . '</strong> ' . esc_html($status_label) . '</p>';
            if ($workflow_notes) {
                echo '<p><strong>' . __('Notes:', 'news') . '</strong> ' . esc_html($workflow_notes) . '</p>';
            }
            echo '</div>';
        }
    }
    
    /**
     * Check if status transition is valid
     *
     * @param string $from_status
     * @param string $to_status
     * @return bool
     */
    private function is_valid_transition(string $from_status, string $to_status): bool {
        $valid_transitions = [
            self::STATUS_DRAFT => [self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS],
            self::STATUS_ASSIGNED => [self::STATUS_IN_PROGRESS, self::STATUS_DRAFT],
            self::STATUS_IN_PROGRESS => [self::STATUS_REVIEW, self::STATUS_DRAFT],
            self::STATUS_REVIEW => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_IN_PROGRESS],
            self::STATUS_APPROVED => [self::STATUS_PUBLISHED, self::STATUS_REVIEW],
            self::STATUS_REJECTED => [self::STATUS_DRAFT, self::STATUS_IN_PROGRESS],
            self::STATUS_PUBLISHED => [self::STATUS_DRAFT],
        ];
        
        return isset($valid_transitions[$from_status]) && 
               in_array($to_status, $valid_transitions[$from_status]);
    }
    
    /**
     * Add entry to workflow history
     *
     * @param int $post_id
     * @param string $status
     * @param string $notes
     */
    private function add_workflow_history(int $post_id, string $status, string $notes = ''): void {
        $history = get_post_meta($post_id, 'workflow_history', true) ?: [];
        
        $history[] = [
            'status' => $status,
            'notes' => $notes,
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('mysql'),
        ];
        
        update_post_meta($post_id, 'workflow_history', $history);
    }
    
    /**
     * Send workflow notification
     *
     * @param int $post_id
     * @param string $status
     * @param string $notes
     */
    private function send_workflow_notification(int $post_id, string $status, string $notes = ''): void {
        $post = get_post($post_id);
        $assignee_id = get_post_meta($post_id, 'workflow_assignee', true);
        $reviewer_id = get_post_meta($post_id, 'workflow_reviewer', true);
        
        $recipients = [];
        
        if ($assignee_id) {
            $recipients[] = $assignee_id;
        }
        
        if ($reviewer_id) {
            $recipients[] = $reviewer_id;
        }
        
        if (empty($recipients)) {
            return;
        }
        
        $subject = sprintf(__('Workflow Update: %s', 'news'), $post->post_title);
        $message = sprintf(
            __('The article "%s" has been updated to status: %s', 'news'),
            $post->post_title,
            $status
        );
        
        if ($notes) {
            $message .= "\n\n" . __('Notes:', 'news') . "\n" . $notes;
        }
        
        foreach ($recipients as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }
    
    /**
     * Check workflow permissions
     *
     * @param \WP_REST_Request $request
     * @return bool
     */
    public function check_workflow_permissions(\WP_REST_Request $request): bool {
        return current_user_can('edit_news');
    }
    
    /**
     * Sanitize workflow history
     *
     * @param mixed $value
     * @return array
     */
    public function sanitize_workflow_history($value): array {
        if (!is_array($value)) {
            return [];
        }
        
        $sanitized = [];
        foreach ($value as $entry) {
            if (is_array($entry)) {
                $sanitized[] = [
                    'status' => sanitize_text_field($entry['status'] ?? ''),
                    'notes' => sanitize_textarea_field($entry['notes'] ?? ''),
                    'user_id' => absint($entry['user_id'] ?? 0),
                    'timestamp' => sanitize_text_field($entry['timestamp'] ?? ''),
                ];
            }
        }
        
        return $sanitized;
    }
}
