<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks;

use NewsPlugin\Core\Plugin;
use NewsPlugin\Assets\AssetManager;

/**
 * Block Manager
 * 
 * Handles Gutenberg block registration and management
 */
class BlockManager
{
    /**
     * Plugin instance
     */
    private Plugin $plugin;

    /**
     * Asset manager
     */
    private AssetManager $assets;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin = Plugin::instance();
        $this->assets = $this->plugin->getAssetManager();
        
        // Don't initialize immediately - wait for init hook
    }

    /**
     * Initialize block manager
     */
    public function init(): void
    {
        // Register blocks immediately since we're already on the init hook
        $this->registerBlocks();
        
        // Add block hooks
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueBlockStyles']);
        
        // Add block filters
        add_filter('block_categories_all', [$this, 'addBlockCategory'], 10, 2);
    }

    /**
     * Register all blocks
     */
    public function registerBlocks(): void
    {
        $this->registerNewsPostBylineBlock();
        $this->registerNewsPostTemplateBlock();
    }

    /**
     * Register news post byline block
     */
    private function registerNewsPostBylineBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-post-byline/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        register_block_type($block_json_path);
    }

    /**
     * Register news post template block
     */
    private function registerNewsPostTemplateBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-post-template/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Post Template Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        register_block_type($block_json_path);
    }

    /**
     * Enqueue block assets
     */
    public function enqueueBlockAssets(): void
    {
        // Block assets are now handled by block.json
        // Only enqueue if we have custom block assets
    }

    /**
     * Enqueue block styles
     */
    public function enqueueBlockStyles(): void
    {
        // Block styles are now handled by block.json
        // Only enqueue if we have custom block styles
    }

    /**
     * Add block category
     */
    public function addBlockCategory(array $categories, $post): array
    {
        return array_merge($categories, [
            [
                'slug' => 'news',
                'title' => __('News', 'news'),
                'icon' => 'megaphone',
            ],
        ]);
    }

    /**
     * Get registered blocks
     */
    public function getBlocks(): array
    {
        return ['news-post-byline'];
    }

    /**
     * Get block by name
     */
    public function getBlock(string $name): ?array
    {
        return $name === 'news-post-byline' ? ['name' => 'news-post-byline'] : null;
    }
}
