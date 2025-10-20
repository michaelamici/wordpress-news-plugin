<?php
/**
 * Security Manager for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles security validation and sanitization
 */
class SecurityManager {
    
    /**
     * Initialize security manager
     */
    public function __construct() {
        add_action('init', [$this, 'add_capabilities']);
        add_action('wp_ajax_news_update_breaking', [$this, 'handle_breaking_alert_ajax']);
        add_action('wp_ajax_news_update_fronts', [$this, 'handle_fronts_ajax']);
    }
    
    /**
     * Add custom capabilities
     */
    public function add_capabilities(): void {
        $admin_role = get_role('administrator');
        $editor_role = get_role('editor');
        
        $capabilities = [
            'edit_news',
            'edit_others_news',
            'edit_published_news',
            'publish_news',
            'delete_news',
            'delete_others_news',
            'delete_published_news',
            'read_news',
            'read_others_news',
            'manage_news_sections',
            'manage_news_fronts',
        ];
        
        if ($admin_role) {
            foreach ($capabilities as $cap) {
                $admin_role->add_cap($cap);
            }
        }
        
        if ($editor_role) {
            $editor_caps = [
                'edit_news',
                'edit_others_news',
                'edit_published_news',
                'publish_news',
                'delete_news',
                'delete_others_news',
                'delete_published_news',
                'read_news',
                'read_others_news',
                'manage_news_sections',
            ];
            
            foreach ($editor_caps as $cap) {
                $editor_role->add_cap($cap);
            }
        }
    }
    
    /**
     * Handle breaking alert AJAX request
     */
    public function handle_breaking_alert_ajax(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'news_breaking_alert')) {
            wp_die(__('Security check failed', 'news'));
        }
        
        // Check capability
        if (!current_user_can('manage_news_fronts')) {
            wp_die(__('Insufficient permissions', 'news'));
        }
        
        $alert_data = [
            'active' => (bool) ($_POST['active'] ?? false),
            'headline' => sanitize_text_field($_POST['headline'] ?? ''),
            'link' => esc_url_raw($_POST['link'] ?? ''),
            'severity' => sanitize_text_field($_POST['severity'] ?? 'normal'),
            'start_time' => sanitize_text_field($_POST['start_time'] ?? ''),
            'end_time' => sanitize_text_field($_POST['end_time'] ?? ''),
        ];
        
        // Validate severity
        $valid_severities = ['low', 'normal', 'high', 'critical'];
        if (!in_array($alert_data['severity'], $valid_severities)) {
            $alert_data['severity'] = 'normal';
        }
        
        // Validate dates
        if (!empty($alert_data['start_time']) && !strtotime($alert_data['start_time'])) {
            $alert_data['start_time'] = '';
        }
        
        if (!empty($alert_data['end_time']) && !strtotime($alert_data['end_time'])) {
            $alert_data['end_time'] = '';
        }
        
        $result = Options::update_breaking_alert($alert_data);
        
        wp_send_json([
            'success' => $result,
            'message' => $result ? __('Breaking alert updated', 'news') : __('Failed to update breaking alert', 'news'),
        ]);
    }
    
    /**
     * Handle fronts AJAX request
     */
    public function handle_fronts_ajax(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'news_fronts')) {
            wp_die(__('Security check failed', 'news'));
        }
        
        // Check capability
        if (!current_user_can('manage_news_fronts')) {
            wp_die(__('Insufficient permissions', 'news'));
        }
        
        $fronts_data = $_POST['fronts'] ?? [];
        
        if (!is_array($fronts_data)) {
            wp_send_json_error(__('Invalid fronts data', 'news'));
        }
        
        // Sanitize fronts data
        $sanitized_fronts = [];
        foreach ($fronts_data as $front_id => $front_config) {
            $sanitized_fronts[sanitize_key($front_id)] = $this->sanitize_front_config($front_config);
        }
        
        $result = Options::update_fronts($sanitized_fronts);
        
        wp_send_json([
            'success' => $result,
            'message' => $result ? __('Fronts updated', 'news') : __('Failed to update fronts', 'news'),
        ]);
    }
    
    /**
     * Sanitize front configuration
     *
     * @param array $config Front configuration
     * @return array
     */
    private function sanitize_front_config(array $config): array {
        $sanitized = [];
        
        if (isset($config['type'])) {
            $sanitized['type'] = sanitize_text_field($config['type']);
        }
        
        if (isset($config['regions']) && is_array($config['regions'])) {
            $sanitized['regions'] = [];
            foreach ($config['regions'] as $region_name => $region_config) {
                $sanitized['regions'][sanitize_key($region_name)] = $this->sanitize_region_config($region_config);
            }
        }
        
        if (isset($config['placements']) && is_array($config['placements'])) {
            $sanitized['placements'] = [];
            foreach ($config['placements'] as $placement_id => $placement_config) {
                $sanitized['placements'][sanitize_key($placement_id)] = $this->sanitize_placement_config($placement_config);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize region configuration
     *
     * @param array $config Region configuration
     * @return array
     */
    private function sanitize_region_config(array $config): array {
        $sanitized = [];
        
        if (isset($config['query']) && is_array($config['query'])) {
            $sanitized['query'] = $this->sanitize_query_args($config['query']);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize query arguments
     *
     * @param array $query_args Query arguments
     * @return array
     */
    private function sanitize_query_args(array $query_args): array {
        $sanitized = [];
        
        $allowed_keys = [
            'post_type', 'posts_per_page', 'orderby', 'order', 'meta_query', 'tax_query',
            'post_status', 'post__in', 'post__not_in', 'author', 'author__in', 'author__not_in',
        ];
        
        foreach ($query_args as $key => $value) {
            if (in_array($key, $allowed_keys)) {
                $sanitized[sanitize_key($key)] = $this->sanitize_query_value($key, $value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize query value
     *
     * @param string $key Query key
     * @param mixed $value Query value
     * @return mixed
     */
    private function sanitize_query_value(string $key, $value) {
        switch ($key) {
            case 'post_type':
                return sanitize_text_field($value);
            case 'posts_per_page':
                return absint($value);
            case 'orderby':
                return sanitize_text_field($value);
            case 'order':
                return in_array(strtoupper($value), ['ASC', 'DESC']) ? strtoupper($value) : 'DESC';
            case 'meta_query':
            case 'tax_query':
                return is_array($value) ? $value : [];
            default:
                return $value;
        }
    }
    
    /**
     * Sanitize placement configuration
     *
     * @param array $config Placement configuration
     * @return array
     */
    private function sanitize_placement_config(array $config): array {
        $sanitized = [];
        
        if (isset($config['region'])) {
            $sanitized['region'] = sanitize_text_field($config['region']);
        }
        
        if (isset($config['priority'])) {
            $sanitized['priority'] = absint($config['priority']);
        }
        
        return $sanitized;
    }
}
