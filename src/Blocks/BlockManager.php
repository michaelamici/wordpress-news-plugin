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
        $this->registerNewsArticleLayoutBlock();
        $this->registerNewsArticlePostTemplateBlock();
        $this->registerNewsArticleListPostTemplateBlock();
        $this->registerNewsArticleTitleBlock();
        $this->registerNewsArticleBylineBlock();
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
        return ['news/front-layout', 'news/article-hero-post-template', 'news/article-list-post-template', 'news/article-title', 'news/article-byline'];
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
     * Register news article hero post template block
     */
    private function registerNewsArticlePostTemplateBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-article-hero-post-template/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Article Hero Post Template Block JSON file not found at: ' . $block_json_path);
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
     * Register news article list post template block
     */
    private function registerNewsArticleListPostTemplateBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-article-list-post-template/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Article List Post Template Block JSON file not found at: ' . $block_json_path);
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
     * Register news article title block
     */
    private function registerNewsArticleTitleBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-article-title/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Article Title Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // This block uses server-side rendering via render.php
        // No custom render callback needed
    }

    /**
     * Register news article byline block
     */
    private function registerNewsArticleBylineBlock(): void
    {
        $block_json_path = plugin_dir_path(__FILE__) . '../../build/blocks/news-article-byline/block.json';
        
        if (!file_exists($block_json_path)) {
            error_log('News Plugin: Article Byline Block JSON file not found at: ' . $block_json_path);
            return;
        }
        
        // Register block using block.json for editor support
        $block_type = register_block_type($block_json_path);
        
        // This block uses server-side rendering via render.php
        // No custom render callback needed
    }

    /**
     * Get block by name
     */
    public function getBlock(string $name): ?array
    {
        $blocks = ['news/front-layout', 'news/article-hero-post-template', 'news/article-list-post-template', 'news/article-title', 'news/article-byline'];
        return in_array($name, $blocks) ? ['name' => $name] : null;
    }
}
