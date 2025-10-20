<?php
/**
 * Placements Registry for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles placement slots registration and rendering
 */
class PlacementsRegistry {
    
    /**
     * Initialize placements registry
     */
    public function __construct() {
        add_action('init', [$this, 'register_default_placements'], 20);
    }
    
    /**
     * Register default placement slots
     */
    public function register_default_placements(): void {
        $default_placements = [
            'hero-top' => [
                'name' => __('Hero Top', 'news'),
                'description' => __('Top of hero region', 'news'),
                'region' => 'hero',
                'priority' => 10,
                'conditions' => [],
            ],
            'hero-bottom' => [
                'name' => __('Hero Bottom', 'news'),
                'description' => __('Bottom of hero region', 'news'),
                'region' => 'hero',
                'priority' => 90,
                'conditions' => [],
            ],
            'rail-inline' => [
                'name' => __('Rail Inline', 'news'),
                'description' => __('Inline with rail content', 'news'),
                'region' => 'rails',
                'priority' => 5,
                'conditions' => [],
            ],
            'sidebar-top' => [
                'name' => __('Sidebar Top', 'news'),
                'description' => __('Top of sidebar', 'news'),
                'region' => 'sidebar',
                'priority' => 10,
                'conditions' => [],
            ],
            'breaking-alert' => [
                'name' => __('Breaking Alert', 'news'),
                'description' => __('Breaking news alert banner', 'news'),
                'region' => 'header',
                'priority' => 1,
                'conditions' => ['breaking_alert_active'],
            ],
        ];
        
        foreach ($default_placements as $slot_id => $config) {
            add_filter('news_register_placements', function($placements) use ($slot_id, $config) {
                $placements[$slot_id] = $config;
                return $placements;
            });
        }
    }
    
    /**
     * Get all registered placements
     *
     * @return array
     */
    public static function get_placements(): array {
        $placements = apply_filters('news_register_placements', []);
        
        // Sort by priority
        uasort($placements, function($a, $b) {
            return ($a['priority'] ?? 10) <=> ($b['priority'] ?? 10);
        });
        
        return $placements;
    }
    
    /**
     * Render a placement slot
     *
     * @param string $slot_id The slot identifier
     * @param array $context Additional context data
     */
    public static function render_slot(string $slot_id, array $context = []): void {
        $placements = self::get_placements();
        
        if (!isset($placements[$slot_id])) {
            return;
        }
        
        $slot = $placements[$slot_id];
        
        // Check conditions
        if (!self::check_conditions($slot, $context)) {
            return;
        }
        
        // Allow themes/plugins to override rendering
        $output = apply_filters("news_render_slot_{$slot_id}", '', $context);
        
        if (empty($output)) {
            do_action('news_render_slot', $slot_id, $context);
        } else {
            echo wp_kses_post($output);
        }
    }
    
    /**
     * Check if slot conditions are met
     *
     * @param array $slot Slot configuration
     * @param array $context Context data
     * @return bool
     */
    private static function check_conditions(array $slot, array $context): bool {
        $conditions = $slot['conditions'] ?? [];
        
        foreach ($conditions as $condition) {
            switch ($condition) {
                case 'breaking_alert_active':
                    $alert = Options::get_breaking_alert();
                    if (!$alert['active']) {
                        return false;
                    }
                    break;
                // Add more conditions as needed
            }
        }
        
        return true;
    }
}
