<?php
/**
 * REST API Endpoints for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles REST API endpoints for fronts and breaking alerts
 */
class RestApi {
    
    /**
     * Initialize REST API
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST routes
     */
    public function register_routes(): void {
        // Front endpoint
        register_rest_route('news/v1', '/front/(?P<slug>[a-z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_front'],
            'permission_callback' => '__return_true',
            'args' => [
                'slug' => [
                    'sanitize_callback' => 'sanitize_title',
                    'validate_callback' => function($value) {
                        return (bool) preg_match('/^[a-z0-9-]+$/', $value);
                    },
                ],
            ],
        ]);
        
        // Breaking alert endpoint
        register_rest_route('news/v1', '/breaking', [
            'methods' => 'GET',
            'callback' => [$this, 'get_breaking_alert'],
            'permission_callback' => '__return_true',
        ]);
        
        // All fronts endpoint
        register_rest_route('news/v1', '/fronts', [
            'methods' => 'GET',
            'callback' => [$this, 'get_all_fronts'],
            'permission_callback' => '__return_true',
        ]);
    }
    
    /**
     * Get front data
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_front(\WP_REST_Request $request) {
        $slug = $request->get_param('slug');
        
        $front = \NewsPlugin\Fronts\FrontManager::get_front($slug);
        
        if (!$front) {
            return new \WP_Error(
                'front_not_found',
                __('Front not found', 'news'),
                ['status' => 404]
            );
        }
        
        return rest_ensure_response($front->to_json());
    }
    
    /**
     * Get breaking alert
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function get_breaking_alert(\WP_REST_Request $request) {
        $alert = Options::get_breaking_alert();
        
        // Check if alert is active and within time bounds
        if ($alert['active']) {
            $now = current_time('timestamp');
            $start_time = !empty($alert['start_time']) ? strtotime($alert['start_time']) : 0;
            $end_time = !empty($alert['end_time']) ? strtotime($alert['end_time']) : PHP_INT_MAX;
            
            if ($now >= $start_time && $now <= $end_time) {
                return rest_ensure_response($alert);
            }
        }
        
        return rest_ensure_response([
            'active' => false,
            'headline' => '',
            'link' => '',
            'severity' => 'normal',
        ]);
    }
    
    /**
     * Get all fronts
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function get_all_fronts(\WP_REST_Request $request) {
        $fronts = \NewsPlugin\Fronts\FrontManager::get_all_fronts();
        $fronts_data = [];
        
        foreach ($fronts as $front_id => $front) {
            $fronts_data[$front_id] = $front->to_json();
        }
        
        return rest_ensure_response($fronts_data);
    }
}
