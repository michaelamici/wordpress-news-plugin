<?php
/**
 * Placement Block
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Blocks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gutenberg block for placement slots
 */
class PlacementBlock {
    
    /**
     * Block name
     */
    public const BLOCK_NAME = 'news/placement';
    
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
        register_block_type(self::BLOCK_NAME, [
            'title' => __('News Placement', 'news'),
            'description' => __('Display a placement slot for ads or promos', 'news'),
            'category' => 'news',
            'icon' => 'money-alt',
            'supports' => [
                'html' => false,
            ],
            'attributes' => [
                'placementId' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'content' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'backgroundColor' => [
                    'type' => 'string',
                    'default' => '#f0f0f0',
                ],
                'textColor' => [
                    'type' => 'string',
                    'default' => '#333333',
                ],
            ],
            'render_callback' => [$this, 'render_block'],
            'editor_script' => 'news-blocks',
        ]);
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
            'placements' => \NewsPlugin\Includes\PlacementsRegistry::get_placements(),
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
        $placement_id = $attributes['placementId'] ?? '';
        $content = $attributes['content'] ?? '';
        $background_color = $attributes['backgroundColor'] ?? '#f0f0f0';
        $text_color = $attributes['textColor'] ?? '#333333';
        
        if (empty($placement_id)) {
            return '<div class="news-placement-error">' . 
                   __('No placement ID specified', 'news') . 
                   '</div>';
        }
        
        $placements = \NewsPlugin\Includes\PlacementsRegistry::get_placements();
        
        if (!isset($placements[$placement_id])) {
            return '<div class="news-placement-error">' . 
                   __('Invalid placement ID', 'news') . 
                   '</div>';
        }
        
        $placement = $placements[$placement_id];
        
        $style = sprintf(
            'background-color: %s; color: %s;',
            esc_attr($background_color),
            esc_attr($text_color)
        );
        
        ob_start();
        ?>
        <div class="news-placement news-placement--<?php echo esc_attr($placement_id); ?>" 
             style="<?php echo esc_attr($style); ?>"
             data-placement="<?php echo esc_attr($placement_id); ?>">
            <div class="news-placement-content">
                <?php if (!empty($content)): ?>
                    <?php echo wp_kses_post($content); ?>
                <?php else: ?>
                    <p><?php echo esc_html($placement['description']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
}
