<?php
/**
 * Advanced Placement Targeting
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles advanced placement targeting based on device, time, user, etc.
 */
class AdvancedPlacements {
    
    /**
     * Initialize advanced placements
     */
    public function __construct() {
        add_filter('news_register_placements', [$this, 'add_advanced_placements'], 20);
        add_action('news_render_slot', [$this, 'check_placement_conditions'], 10, 2);
    }
    
    /**
     * Add advanced placement types
     *
     * @param array $placements Existing placements
     * @return array
     */
    public function add_advanced_placements(array $placements): array {
        $advanced_placements = [
            'mobile-hero' => [
                'name' => __('Mobile Hero', 'news'),
                'description' => __('Hero placement for mobile devices only', 'news'),
                'region' => 'hero',
                'priority' => 5,
                'conditions' => ['device_mobile'],
                'targeting' => [
                    'device' => 'mobile',
                    'time_range' => 'all',
                    'user_role' => 'all',
                ],
            ],
            'desktop-sidebar' => [
                'name' => __('Desktop Sidebar', 'news'),
                'description' => __('Sidebar placement for desktop devices only', 'news'),
                'region' => 'sidebar',
                'priority' => 10,
                'conditions' => ['device_desktop'],
                'targeting' => [
                    'device' => 'desktop',
                    'time_range' => 'all',
                    'user_role' => 'all',
                ],
            ],
            'morning-promo' => [
                'name' => __('Morning Promo', 'news'),
                'description' => __('Morning promotional placement (6 AM - 12 PM)', 'news'),
                'region' => 'hero',
                'priority' => 15,
                'conditions' => ['time_morning'],
                'targeting' => [
                    'device' => 'all',
                    'time_range' => 'morning',
                    'user_role' => 'all',
                ],
            ],
            'evening-newsletter' => [
                'name' => __('Evening Newsletter', 'news'),
                'description' => __('Evening newsletter signup (6 PM - 12 AM)', 'news'),
                'region' => 'sidebar',
                'priority' => 20,
                'conditions' => ['time_evening'],
                'targeting' => [
                    'device' => 'all',
                    'time_range' => 'evening',
                    'user_role' => 'all',
                ],
            ],
            'subscriber-exclusive' => [
                'name' => __('Subscriber Exclusive', 'news'),
                'description' => __('Exclusive content for logged-in users', 'news'),
                'region' => 'hero',
                'priority' => 25,
                'conditions' => ['user_logged_in'],
                'targeting' => [
                    'device' => 'all',
                    'time_range' => 'all',
                    'user_role' => 'subscriber',
                ],
            ],
            'admin-notice' => [
                'name' => __('Admin Notice', 'news'),
                'description' => __('Administrative notices for site admins', 'news'),
                'region' => 'header',
                'priority' => 30,
                'conditions' => ['user_admin'],
                'targeting' => [
                    'device' => 'all',
                    'time_range' => 'all',
                    'user_role' => 'administrator',
                ],
            ],
        ];
        
        return array_merge($placements, $advanced_placements);
    }
    
    /**
     * Check placement conditions before rendering
     *
     * @param string $slot_id Slot identifier
     * @param array $context Context data
     */
    public function check_placement_conditions(string $slot_id, array $context): void {
        $placements = \NewsPlugin\Includes\PlacementsRegistry::get_placements();
        
        if (!isset($placements[$slot_id])) {
            return;
        }
        
        $placement = $placements[$slot_id];
        
        if (!$this->should_render_placement($placement, $context)) {
            return;
        }
        
        // Allow theme/plugin to override rendering
        $output = apply_filters("news_render_placement_{$slot_id}", '', $placement, $context);
        
        if (!empty($output)) {
            echo wp_kses_post($output);
        }
    }
    
    /**
     * Determine if placement should render based on conditions
     *
     * @param array $placement Placement configuration
     * @param array $context Context data
     * @return bool
     */
    private function should_render_placement(array $placement, array $context): bool {
        $conditions = $placement['conditions'] ?? [];
        
        foreach ($conditions as $condition) {
            if (!$this->check_condition($condition, $context)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check individual condition
     *
     * @param string $condition Condition name
     * @param array $context Context data
     * @return bool
     */
    private function check_condition(string $condition, array $context): bool {
        switch ($condition) {
            case 'device_mobile':
                return $this->is_mobile_device();
                
            case 'device_desktop':
                return !$this->is_mobile_device();
                
            case 'device_tablet':
                return $this->is_tablet_device();
                
            case 'time_morning':
                return $this->is_time_range('morning');
                
            case 'time_afternoon':
                return $this->is_time_range('afternoon');
                
            case 'time_evening':
                return $this->is_time_range('evening');
                
            case 'time_night':
                return $this->is_time_range('night');
                
            case 'user_logged_in':
                return is_user_logged_in();
                
            case 'user_admin':
                return current_user_can('administrator');
                
            case 'user_editor':
                return current_user_can('edit_news');
                
            case 'user_subscriber':
                return current_user_can('read') && !current_user_can('edit_posts');
                
            case 'breaking_alert_active':
                $alert = \NewsPlugin\Includes\Options::get_breaking_alert();
                return $alert['active'] ?? false;
                
            case 'weekend':
                return $this->is_weekend();
                
            case 'weekday':
                return !$this->is_weekend();
                
            default:
                // Allow custom conditions via filter
                return apply_filters('news_check_placement_condition', true, $condition, $context);
        }
    }
    
    /**
     * Check if device is mobile
     *
     * @return bool
     */
    private function is_mobile_device(): bool {
        if (function_exists('wp_is_mobile')) {
            return wp_is_mobile();
        }
        
        // Fallback user agent detection
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $user_agent);
    }
    
    /**
     * Check if device is tablet
     *
     * @return bool
     */
    private function is_tablet_device(): bool {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $user_agent);
    }
    
    /**
     * Check if current time is in specified range
     *
     * @param string $range Time range
     * @return bool
     */
    private function is_time_range(string $range): bool {
        $current_hour = (int) current_time('H');
        
        switch ($range) {
            case 'morning':
                return $current_hour >= 6 && $current_hour < 12;
                
            case 'afternoon':
                return $current_hour >= 12 && $current_hour < 18;
                
            case 'evening':
                return $current_hour >= 18 && $current_hour < 24;
                
            case 'night':
                return $current_hour >= 0 && $current_hour < 6;
                
            default:
                return true;
        }
    }
    
    /**
     * Check if current day is weekend
     *
     * @return bool
     */
    private function is_weekend(): bool {
        $day_of_week = (int) current_time('w');
        return $day_of_week === 0 || $day_of_week === 6; // Sunday or Saturday
    }
    
    /**
     * Get placement analytics data
     *
     * @param string $slot_id Slot identifier
     * @return array
     */
    public static function get_placement_analytics(string $slot_id): array {
        $analytics = get_option("news_placement_analytics_{$slot_id}", [
            'impressions' => 0,
            'clicks' => 0,
            'conversions' => 0,
            'last_updated' => current_time('timestamp'),
        ]);
        
        return $analytics;
    }
    
    /**
     * Track placement impression
     *
     * @param string $slot_id Slot identifier
     */
    public static function track_impression(string $slot_id): void {
        $analytics = self::get_placement_analytics($slot_id);
        $analytics['impressions']++;
        $analytics['last_updated'] = current_time('timestamp');
        
        update_option("news_placement_analytics_{$slot_id}", $analytics);
    }
    
    /**
     * Track placement click
     *
     * @param string $slot_id Slot identifier
     */
    public static function track_click(string $slot_id): void {
        $analytics = self::get_placement_analytics($slot_id);
        $analytics['clicks']++;
        $analytics['last_updated'] = current_time('timestamp');
        
        update_option("news_placement_analytics_{$slot_id}", $analytics);
    }
}
