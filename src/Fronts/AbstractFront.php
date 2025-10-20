<?php
/**
 * Abstract Front Base Class
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Fronts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for all front types
 */
abstract class AbstractFront {
    
    /**
     * Front configuration
     *
     * @var array
     */
    protected array $config;
    
    /**
     * Front identifier
     *
     * @var string
     */
    protected string $id;
    
    /**
     * Constructor
     *
     * @param string $id Front identifier
     * @param array $config Front configuration
     */
    public function __construct(string $id, array $config = []) {
        $this->id = $id;
        $this->config = wp_parse_args($config, $this->get_default_config());
    }
    
    /**
     * Get default configuration
     *
     * @return array
     */
    protected function get_default_config(): array {
        return [
            'type' => static::class,
            'regions' => [],
            'placements' => [],
            'cache_ttl' => 300, // 5 minutes
        ];
    }
    
    /**
     * Get front identifier
     *
     * @return string
     */
    public function get_id(): string {
        return $this->id;
    }
    
    /**
     * Get front configuration
     *
     * @return array
     */
    public function get_config(): array {
        return $this->config;
    }
    
    /**
     * Get regions data
     *
     * @return array
     */
    public function get_regions(): array {
        $cache_key = $this->get_cache_key('regions');
        $cached = get_transient($cache_key);
        
        if (false !== $cached) {
            return $cached;
        }
        
        $regions = $this->build_regions();
        
        // Allow filtering
        $regions = apply_filters('news_front_regions', $regions, $this->id, $this);
        
        // Cache the result
        set_transient($cache_key, $regions, $this->config['cache_ttl']);
        
        return $regions;
    }
    
    /**
     * Build regions data (to be implemented by subclasses)
     *
     * @return array
     */
    abstract protected function build_regions(): array;
    
    /**
     * Get placements for this front
     *
     * @return array
     */
    public function get_placements(): array {
        return $this->config['placements'] ?? [];
    }
    
    /**
     * Render a specific region
     *
     * @param string $region_name Region name
     * @param array $context Additional context
     */
    public function render_region(string $region_name, array $context = []): void {
        $regions = $this->get_regions();
        
        if (!isset($regions[$region_name])) {
            return;
        }
        
        $region = $regions[$region_name];
        
        // Allow theme override
        $template_path = locate_template("news/fronts/{$this->id}-{$region_name}.php");
        
        if ($template_path) {
            $this->load_template($template_path, $region, $context);
        } else {
            $this->render_region_default($region_name, $region, $context);
        }
    }
    
    /**
     * Load template file
     *
     * @param string $template_path Template file path
     * @param array $region Region data
     * @param array $context Context data
     */
    protected function load_template(string $template_path, array $region, array $context): void {
        $region_data = $region;
        $front = $this;
        
        // Make variables available to template
        extract([
            'region' => $region_data,
            'front' => $front,
            'context' => $context,
        ]);
        
        include $template_path;
    }
    
    /**
     * Render region with default markup
     *
     * @param string $region_name Region name
     * @param array $region Region data
     * @param array $context Context data
     */
    protected function render_region_default(string $region_name, array $region, array $context): void {
        echo '<div class="news-front-region news-front-region--' . esc_attr($region_name) . '">';
        
        if (!empty($region['items'])) {
            echo '<div class="news-front-items">';
            foreach ($region['items'] as $item) {
                $this->render_item($item, $context);
            }
            echo '</div>';
        }
        
        // Render placements for this region
        $this->render_region_placements($region_name, $context);
        
        echo '</div>';
    }
    
    /**
     * Render a single item
     *
     * @param array $item Item data
     * @param array $context Context data
     */
    protected function render_item(array $item, array $context): void {
        $item = apply_filters('news_render_item', $item, $context);
        
        echo '<article class="news-item">';
        echo '<h2><a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a></h2>';
        
        if (!empty($item['excerpt'])) {
            echo '<div class="news-item-excerpt">' . wp_kses_post($item['excerpt']) . '</div>';
        }
        
        if (!empty($item['meta'])) {
            echo '<div class="news-item-meta">' . wp_kses_post($item['meta']) . '</div>';
        }
        
        echo '</article>';
    }
    
    /**
     * Render placements for a region
     *
     * @param string $region_name Region name
     * @param array $context Context data
     */
    protected function render_region_placements(string $region_name, array $context): void {
        $placements = $this->get_placements();
        
        foreach ($placements as $placement_id => $placement) {
            if (($placement['region'] ?? '') === $region_name) {
                \NewsPlugin\Includes\PlacementsRegistry::render_slot($placement_id, $context);
            }
        }
    }
    
    /**
     * Get cache key for a specific data type
     *
     * @param string $type Data type
     * @return string
     */
    protected function get_cache_key(string $type): string {
        $hash = md5(serialize($this->config));
        return "news_front_{$this->id}_{$type}_{$hash}";
    }
    
    /**
     * Clear front cache
     */
    public function clear_cache(): void {
        $types = ['regions', 'placements'];
        
        foreach ($types as $type) {
            delete_transient($this->get_cache_key($type));
        }
    }
    
    /**
     * Get front as JSON for REST API
     *
     * @return array
     */
    public function to_json(): array {
        return [
            'id' => $this->id,
            'type' => $this->config['type'],
            'regions' => $this->get_regions(),
            'placements' => $this->get_placements(),
        ];
    }
}