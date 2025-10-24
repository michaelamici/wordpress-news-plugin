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
        $this->registerNewsPostFeaturedBlock();
        $this->registerNewsPostBreakingBlock();
        $this->registerNewsPostExclusiveBlock();
        $this->registerNewsPostSponsoredBlock();
        $this->registerNewsPostLiveBlock();
        $this->registerNewsPostLastUpdatedBlock();
        $this->registerNewsArticleLayoutBlock();
        $this->registerNewsArticlePostTemplateBlock();
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
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\BylineRenderer::class, 'render'];
        }
    }

    /**
     * Register news post featured block
     */
    private function registerNewsPostFeaturedBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-post-featured/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Featured Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\FeaturedRenderer::class, 'render'];
        }
    }

    /**
     * Register news post breaking block
     */
    private function registerNewsPostBreakingBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-post-breaking/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Breaking Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\BreakingRenderer::class, 'render'];
        }
    }

    /**
     * Register news post exclusive block
     */
    private function registerNewsPostExclusiveBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-post-exclusive/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Exclusive Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\ExclusiveRenderer::class, 'render'];
        }
    }

    /**
     * Register news post sponsored block
     */
    private function registerNewsPostSponsoredBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-post-sponsored/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Sponsored Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\SponsoredRenderer::class, 'render'];
        }
    }

    /**
     * Register news post live block
     */
    private function registerNewsPostLiveBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-post-live/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Live Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\LiveRenderer::class, 'render'];
        }
    }

    /**
     * Register news post last updated block
     */
    private function registerNewsPostLastUpdatedBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-post-last-updated/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Last Updated Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\LastUpdatedRenderer::class, 'render'];
        }
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
     * Register news article layout block
     */
    private function registerNewsArticleLayoutBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-article-layout/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Article Layout Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\FrontLayoutRenderer::class, 'render'];
        }
    }


    /**
     * Register news article post template block
     */
    private function registerNewsArticlePostTemplateBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-front-layout-template/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Article Post Template Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // Override the render callback to use our clean renderer
        if ($block_type) {
            $block_type->render_callback = [\NewsPlugin\Blocks\Renderers\ArticleTemplateRenderer::class, 'render'];
        }
    }

    /**
     * Get block by name
     */
    public function getBlock(string $name): ?array
    {
        return $name === 'news-post-byline' ? ['name' => 'news-post-byline'] : null;
    }
}
