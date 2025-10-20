<?php
/**
 * Front Manager
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Fronts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages front instances and provides factory methods
 */
class FrontManager {
    
    /**
     * Front instances cache
     *
     * @var array
     */
    private static array $instances = [];
    
    /**
     * Get front instance
     *
     * @param string $front_id Front identifier
     * @return AbstractFront|null
     */
    public static function get_front(string $front_id): ?AbstractFront {
        if (isset(self::$instances[$front_id])) {
            return self::$instances[$front_id];
        }
        
        $fronts_config = \NewsPlugin\Includes\Options::get_fronts();
        
        if (!isset($fronts_config[$front_id])) {
            return null;
        }
        
        $config = $fronts_config[$front_id];
        $front = self::create_front($front_id, $config);
        
        if ($front) {
            self::$instances[$front_id] = $front;
        }
        
        return $front;
    }
    
    /**
     * Create front instance
     *
     * @param string $front_id Front identifier
     * @param array $config Front configuration
     * @return AbstractFront|null
     */
    private static function create_front(string $front_id, array $config): ?AbstractFront {
        $type = $config['type'] ?? '';
        
        switch ($type) {
            case 'HomeFront':
                return new HomeFront($front_id, $config);
                
            case 'SectionFront':
                $section = self::get_section_for_front($front_id, $config);
                return new SectionFront($front_id, $config, $section);
                
            default:
                return null;
        }
    }
    
    /**
     * Get section term for section front
     *
     * @param string $front_id Front identifier
     * @param array $config Front configuration
     * @return \WP_Term|null
     */
    private static function get_section_for_front(string $front_id, array $config): ?\WP_Term {
        $section_slug = $config['section_slug'] ?? $front_id;
        
        $section = get_term_by('slug', $section_slug, 'news_section');
        
        return $section instanceof \WP_Term ? $section : null;
    }
    
    /**
     * Get all available fronts
     *
     * @return array
     */
    public static function get_all_fronts(): array {
        $fronts_config = \NewsPlugin\Includes\Options::get_fronts();
        $fronts = [];
        
        foreach ($fronts_config as $front_id => $config) {
            $front = self::get_front($front_id);
            if ($front) {
                $fronts[$front_id] = $front;
            }
        }
        
        return $fronts;
    }
    
    /**
     * Clear all front caches
     */
    public static function clear_all_caches(): void {
        $fronts = self::get_all_fronts();
        
        foreach ($fronts as $front) {
            $front->clear_cache();
        }
        
        // Clear instances cache
        self::$instances = [];
    }
    
    /**
     * Clear cache for specific front
     *
     * @param string $front_id Front identifier
     */
    public static function clear_front_cache(string $front_id): void {
        $front = self::get_front($front_id);
        
        if ($front) {
            $front->clear_cache();
        }
        
        // Remove from instances cache
        unset(self::$instances[$front_id]);
    }
}
