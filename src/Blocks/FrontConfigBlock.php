<?php
/**
 * Front Configuration Block
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Blocks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gutenberg block for front configuration
 */
class FrontConfigBlock {
    
    /**
     * Block name
     */
    public const BLOCK_NAME = 'news/front-config';
    
    /**
     * Initialize the block
     */
    public function __construct() {
        add_action('init', [$this, 'register_block']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
    }
    
    /**
     * Register the block
     */
    public function register_block(): void {
        // Register block category first
        add_filter('block_categories_all', [$this, 'add_block_category'], 10, 2);
        
        register_block_type(self::BLOCK_NAME, [
            'title' => __('Front Configuration', 'news'),
            'description' => __('Configure news front regions and queries', 'news'),
            'category' => 'news',
            'icon' => 'admin-site',
            'supports' => [
                'html' => false,
            ],
            'attributes' => [
                'frontId' => [
                    'type' => 'string',
                    'default' => 'home',
                ],
                'regions' => [
                    'type' => 'object',
                    'default' => [],
                ],
                'placements' => [
                    'type' => 'object',
                    'default' => [],
                ],
            ],
            'render_callback' => [$this, 'render_block'],
            'editor_script' => 'news-blocks',
        ]);
    }
    
    /**
     * Add block category
     *
     * @param array $categories Block categories
     * @param \WP_Block_Editor_Context $context Block editor context
     * @return array
     */
    public function add_block_category(array $categories, \WP_Block_Editor_Context $context): array {
        $categories[] = [
            'slug' => 'news',
            'title' => __('News', 'news'),
            'icon' => 'admin-site',
        ];
        
        return $categories;
    }
    
    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets(): void {
        wp_enqueue_script(
            'news-blocks',
            NEWS_PLUGIN_URL . 'src/Assets/js/blocks.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
            NEWS_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('news-blocks', 'newsBlocks', [
            'fronts' => \NewsPlugin\Includes\Options::get_fronts(),
            'placements' => \NewsPlugin\Includes\PlacementsRegistry::get_placements(),
            'apiUrl' => rest_url('news/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
    
    /**
     * Render the block
     *
     * @param array $attributes Block attributes
     * @param string $content Block content
     * @return string
     */
    public function render_block(array $attributes, string $content): string {
        $front_id = $attributes['frontId'] ?? 'home';
        $front = \NewsPlugin\Fronts\FrontManager::get_front($front_id);
        
        if (!$front) {
            return '<div class="news-front-config-error">' . 
                   __('Front not found', 'news') . 
                   '</div>';
        }
        
        $regions = $front->get_regions();
        $placements = $front->get_placements();
        
        ob_start();
        ?>
        <div class="news-front-config" data-front-id="<?php echo esc_attr($front_id); ?>">
            <div class="news-front-regions">
                <?php foreach ($regions as $region_name => $region): ?>
                    <div class="news-front-region" data-region="<?php echo esc_attr($region_name); ?>">
                        <h3><?php echo esc_html(ucfirst($region_name)); ?></h3>
                        <div class="news-front-items">
                            <?php if (!empty($region['items'])): ?>
                                <?php foreach ($region['items'] as $item): ?>
                                    <div class="news-front-item">
                                        <h4><a href="<?php echo esc_url($item['url']); ?>">
                                            <?php echo esc_html($item['title']); ?>
                                        </a></h4>
                                        <?php if (!empty($item['excerpt'])): ?>
                                            <p><?php echo wp_kses_post($item['excerpt']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p><?php _e('No items found', 'news'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($placements)): ?>
                <div class="news-front-placements">
                    <h3><?php _e('Placements', 'news'); ?></h3>
                    <?php foreach ($placements as $placement_id => $placement): ?>
                        <div class="news-placement" data-placement="<?php echo esc_attr($placement_id); ?>">
                            <strong><?php echo esc_html($placement['name']); ?></strong>
                            <span class="news-placement-region"><?php echo esc_html($placement['region']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
}
