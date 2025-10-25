<?php

declare(strict_types=1);

namespace NewsPlugin\Assets;

/**
 * Asset Manager
 * 
 * Handles CSS, JS, and other asset management
 */
class AssetManager
{
    /**
     * Asset version
     */
    private string $version;

    /**
     * Asset directory
     */
    private string $asset_dir;

    /**
     * Asset URL
     */
    private string $asset_url;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->version = defined('NEWS_PLUGIN_VERSION') ? NEWS_PLUGIN_VERSION : '1.0.0';
        $this->asset_dir = NEWS_PLUGIN_DIR . 'assets/';
        $this->asset_url = NEWS_PLUGIN_URL . 'assets/';
        
        $this->init();
    }

    /**
     * Initialize asset manager
     */
    private function init(): void
    {
        // Add asset hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockAssets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorArticleSettings']);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueueFrontendAssets(): void
    {
        // Enqueue frontend CSS
        $this->enqueueStyle(
            'news-frontend',
            'css/frontend.css',
            ['wp-block-library']
        );

        // Enqueue frontend JS
        $this->enqueueScript(
            'news-frontend',
            'js/frontend.js',
            ['jquery'],
            true
        );

        // Localize script
        wp_localize_script('news-frontend', 'newsFrontend', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('news_frontend_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'news'),
                'error' => __('An error occurred', 'news'),
            ],
        ]);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets(): void
    {
        $screen = get_current_screen();
        
        // Only enqueue on plugin pages
        if (!$this->isPluginPage($screen)) {
            return;
        }

        // Enqueue admin CSS
        $this->enqueueStyle(
            'news-admin',
            'css/admin.css',
            ['wp-admin']
        );

        // Enqueue admin JS
        $this->enqueueScript(
            'news-admin',
            'js/admin.js',
            ['jquery', 'wp-util'],
            true
        );

        // Localize script
        wp_localize_script('news-admin', 'newsAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('news_admin_nonce'),
            'strings' => [
                'confirm' => __('Are you sure?', 'news'),
                'saving' => __('Saving...', 'news'),
                'saved' => __('Saved!', 'news'),
                'error' => __('An error occurred', 'news'),
            ],
        ]);
    }

    /**
     * Enqueue block assets
     */
    public function enqueueBlockAssets(): void
    {
        // Enqueue block CSS
        $this->enqueueStyle(
            'news-blocks',
            'css/blocks.css',
            ['wp-block-library']
        );

        // Enqueue block JS
        $this->enqueueScript(
            'news-blocks',
            'js/blocks.js',
            ['wp-blocks', 'wp-element', 'wp-editor'],
            true
        );
    }

    /**
     * Enqueue Gutenberg editor Article Settings panel (news CPT only)
     */
    public function enqueueEditorArticleSettings(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $post_type = function_exists('get_post_type') ? get_post_type() : null;

        // Only load in block editor for news post type
        // Fallback checks since enqueue_block_editor_assets doesn't always have screen
        $is_news = false;
        if ($post_type === 'news') {
            $is_news = true;
        } elseif ($screen && !empty($screen->post_type) && $screen->post_type === 'news') {
            $is_news = true;
        } else {
            // Try to infer from global $post
            global $post;
            if ($post && isset($post->post_type) && $post->post_type === 'news') {
                $is_news = true;
            }
        }

        if (!$is_news) {
            return;
        }

        // Allow site to disable via existing setting
        $settings = get_option('news_settings', []);
        if (isset($settings['enable_blocks']) && !$settings['enable_blocks']) {
            return;
        }

        // Styles
        $this->enqueueStyle(
            'news-editor-article-settings',
            'css/editor-article-settings.css',
            ['wp-edit-post']
        );

        // Script
        $deps = [
            'wp-plugins',
            'wp-edit-post',
            'wp-components',
            'wp-data',
            'wp-core-data',
            'wp-element',
            'wp-i18n',
            'wp-editor'
        ];
        $this->enqueueScript(
            'news-editor-article-settings',
            'js/editor-article-settings.js',
            $deps,
            true
        );
    }

    /**
     * Enqueue a stylesheet
     */
    public function enqueueStyle(string $handle, string $file, array $deps = [], string $media = 'all'): void
    {
        $file_path = $this->asset_dir . $file;
        $file_url = $this->asset_url . $file;

        if (file_exists($file_path)) {
            wp_enqueue_style(
                $handle,
                $file_url,
                $deps,
                $this->getFileVersion($file_path)
            );
        }
    }

    /**
     * Enqueue a script
     */
    public function enqueueScript(string $handle, string $file, array $deps = [], bool $in_footer = false): void
    {
        $file_path = $this->asset_dir . $file;
        $file_url = $this->asset_url . $file;

        if (file_exists($file_path)) {
            wp_enqueue_script(
                $handle,
                $file_url,
                $deps,
                $this->getFileVersion($file_path),
                $in_footer
            );
        }
    }

    /**
     * Register a stylesheet
     */
    public function registerStyle(string $handle, string $file, array $deps = [], string $media = 'all'): void
    {
        $file_path = $this->asset_dir . $file;
        $file_url = $this->asset_url . $file;

        if (file_exists($file_path)) {
            wp_register_style(
                $handle,
                $file_url,
                $deps,
                $this->getFileVersion($file_path),
                $media
            );
        }
    }

    /**
     * Register a script
     */
    public function registerScript(string $handle, string $file, array $deps = [], bool $in_footer = false): void
    {
        $file_path = $this->asset_dir . $file;
        $file_url = $this->asset_url . $file;

        if (file_exists($file_path)) {
            wp_register_script(
                $handle,
                $file_url,
                $deps,
                $this->getFileVersion($file_path),
                $in_footer
            );
        }
    }

    /**
     * Get file version based on modification time
     */
    private function getFileVersion(string $file_path): string
    {
        if (file_exists($file_path)) {
            return (string) filemtime($file_path);
        }

        return $this->version;
    }

    /**
     * Check if current screen is a plugin page
     */
    private function isPluginPage($screen): bool
    {
        if (!$screen) {
            return false;
        }

        $plugin_pages = [
            'news-settings',
            'news-articles',
            'news-sections',
            'news-analytics',
        ];

        return in_array($screen->id, $plugin_pages) || strpos($screen->id, 'news') === 0;
    }

    /**
     * Get asset URL
     */
    public function getAssetUrl(string $file): string
    {
        return $this->asset_url . ltrim($file, '/');
    }

    /**
     * Get asset path
     */
    public function getAssetPath(string $file): string
    {
        return $this->asset_dir . ltrim($file, '/');
    }

    /**
     * Check if asset exists
     */
    public function assetExists(string $file): bool
    {
        return file_exists($this->getAssetPath($file));
    }

    /**
     * Get asset contents
     */
    public function getAssetContents(string $file): string
    {
        $path = $this->getAssetPath($file);
        
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return '';
    }

    /**
     * Minify CSS
     */
    public function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        
        // Remove unnecessary spaces
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/;\s*/', ';', $css);
        $css = preg_replace('/\s*}\s*/', '}', $css);
        $css = preg_replace('/;\s*}/', '}', $css);
        
        return trim($css);
    }

    /**
     * Minify JavaScript
     */
    public function minifyJs(string $js): string
    {
        // Remove single-line comments
        $js = preg_replace('~//[^\r\n]*~', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('~/\*.*?\*/~s', '', $js);
        
        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        $js = preg_replace('/\s*([{}();,=])\s*/', '$1', $js);
        
        return trim($js);
    }

    /**
     * Generate asset manifest
     */
    public function generateManifest(): array
    {
        $manifest = [];
        $asset_types = ['css', 'js', 'images'];

        foreach ($asset_types as $type) {
            $dir = $this->asset_dir . $type;
            if (is_dir($dir)) {
                $files = glob($dir . '/*');
                foreach ($files as $file) {
                    $relative_path = str_replace($this->asset_dir, '', $file);
                    $manifest[$relative_path] = [
                        'path' => $file,
                        'url' => $this->asset_url . $relative_path,
                        'version' => $this->getFileVersion($file),
                        'size' => filesize($file),
                    ];
                }
            }
        }

        return $manifest;
    }

    /**
     * Get asset statistics
     */
    public function getStats(): array
    {
        $manifest = $this->generateManifest();
        $total_size = 0;
        $file_count = 0;

        foreach ($manifest as $asset) {
            $total_size += $asset['size'];
            $file_count++;
        }

        return [
            'version' => $this->version,
            'asset_dir' => $this->asset_dir,
            'asset_url' => $this->asset_url,
            'file_count' => $file_count,
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2),
        ];
    }
}
