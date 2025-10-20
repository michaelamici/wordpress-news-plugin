<?php
/**
 * Options Management for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles options for fronts and breaking alerts
 */
class Options {
    
    /**
     * Option names
     */
    public const FRONTS_OPTION = 'news_fronts';
    public const BREAKING_ALERT_OPTION = 'news_breaking_alert';
    
    /**
     * Initialize options management
     */
    public function __construct() {
        $this->register_options();
    }
    
    /**
     * Register default options
     */
    public function register_options(): void {
        $default_fronts = [
            'home' => [
                'type' => 'HomeFront',
                'regions' => [
                    'hero' => [
                        'query' => [
                            'post_type' => 'news',
                            'posts_per_page' => 1,
                            'meta_query' => [
                                [
                                    'key' => 'is_featured',
                                    'value' => true,
                                    'compare' => '=',
                                ],
                            ],
                        ],
                    ],
                    'rails' => [
                        'query' => [
                            'post_type' => 'news',
                            'posts_per_page' => 6,
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ],
                    ],
                ],
                'placements' => [
                    'hero-top' => [
                        'region' => 'hero',
                        'priority' => 10,
                    ],
                    'rail-inline' => [
                        'region' => 'rails',
                        'priority' => 5,
                    ],
                ],
            ],
        ];
        
        if (false === get_option(self::FRONTS_OPTION)) {
            add_option(self::FRONTS_OPTION, $default_fronts);
        }
        
        if (false === get_option(self::BREAKING_ALERT_OPTION)) {
            add_option(self::BREAKING_ALERT_OPTION, [
                'active' => false,
                'headline' => '',
                'link' => '',
                'severity' => 'normal',
                'start_time' => '',
                'end_time' => '',
            ]);
        }
    }
    
    /**
     * Get fronts configuration
     *
     * @return array
     */
    public static function get_fronts(): array {
        return get_option(self::FRONTS_OPTION, []);
    }
    
    /**
     * Update fronts configuration
     *
     * @param array $fronts Fronts configuration
     * @return bool
     */
    public static function update_fronts(array $fronts): bool {
        return update_option(self::FRONTS_OPTION, $fronts);
    }
    
    /**
     * Get breaking alert
     *
     * @return array
     */
    public static function get_breaking_alert(): array {
        return get_option(self::BREAKING_ALERT_OPTION, [
            'active' => false,
            'headline' => '',
            'link' => '',
            'severity' => 'normal',
            'start_time' => '',
            'end_time' => '',
        ]);
    }
    
    /**
     * Update breaking alert
     *
     * @param array $alert Breaking alert data
     * @return bool
     */
    public static function update_breaking_alert(array $alert): bool {
        return update_option(self::BREAKING_ALERT_OPTION, $alert);
    }
}
