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
     * Registered blocks
     */
    private array $blocks = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin = Plugin::instance();
        $this->assets = $this->plugin->getAssetManager();
        
        $this->init();
    }

    /**
     * Initialize block manager
     */
    private function init(): void
    {
        // Add block hooks
        add_action('init', [$this, 'registerBlocks']);
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
        $this->registerNewsArticleBlock();
        $this->registerNewsSectionBlock();
        $this->registerBreakingNewsBlock();
        $this->registerNewsGridBlock();
    }

    /**
     * Register news article block
     */
    private function registerNewsArticleBlock(): void
    {
        $this->blocks['news-article'] = [
            'name' => 'news-article',
            'title' => __('News Article', 'news'),
            'description' => __('Display a single news article', 'news'),
            'icon' => 'megaphone',
            'category' => 'news',
            'keywords' => ['news', 'article', 'post'],
            'supports' => [
                'align' => ['wide', 'full'],
                'html' => false,
            ],
            'attributes' => [
                'articleId' => [
                    'type' => 'number',
                    'default' => 0,
                ],
                'showExcerpt' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'showMeta' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'showImage' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
        ];

        register_block_type('news/article', [
            'attributes' => $this->blocks['news-article']['attributes'],
            'render_callback' => [$this, 'renderNewsArticleBlock'],
            'editor_script' => 'news-blocks',
            'editor_style' => 'news-blocks',
            'style' => 'news-blocks',
        ]);
    }

    /**
     * Register news section block
     */
    private function registerNewsSectionBlock(): void
    {
        $this->blocks['news-section'] = [
            'name' => 'news-section',
            'title' => __('News Section', 'news'),
            'description' => __('Display articles from a specific news section', 'news'),
            'icon' => 'category',
            'category' => 'news',
            'keywords' => ['news', 'section', 'category'],
            'supports' => [
                'align' => ['wide', 'full'],
                'html' => false,
            ],
            'attributes' => [
                'sectionId' => [
                    'type' => 'number',
                    'default' => 0,
                ],
                'postsPerPage' => [
                    'type' => 'number',
                    'default' => 5,
                ],
                'layout' => [
                    'type' => 'string',
                    'default' => 'list',
                ],
                'showExcerpt' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
        ];

        register_block_type('news/section', [
            'attributes' => $this->blocks['news-section']['attributes'],
            'render_callback' => [$this, 'renderNewsSectionBlock'],
            'editor_script' => 'news-blocks',
            'editor_style' => 'news-blocks',
            'style' => 'news-blocks',
        ]);
    }

    /**
     * Register breaking news block
     */
    private function registerBreakingNewsBlock(): void
    {
        $this->blocks['breaking-news'] = [
            'name' => 'breaking-news',
            'title' => __('Breaking News', 'news'),
            'description' => __('Display breaking news ticker', 'news'),
            'icon' => 'warning',
            'category' => 'news',
            'keywords' => ['news', 'breaking', 'ticker'],
            'supports' => [
                'align' => ['wide', 'full'],
                'html' => false,
            ],
            'attributes' => [
                'count' => [
                    'type' => 'number',
                    'default' => 3,
                ],
                'scroll' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'speed' => [
                    'type' => 'number',
                    'default' => 50,
                ],
            ],
        ];

        register_block_type('news/breaking-news', [
            'attributes' => $this->blocks['breaking-news']['attributes'],
            'render_callback' => [$this, 'renderBreakingNewsBlock'],
            'editor_script' => 'news-blocks',
            'editor_style' => 'news-blocks',
            'style' => 'news-blocks',
        ]);
    }

    /**
     * Register news grid block
     */
    private function registerNewsGridBlock(): void
    {
        $this->blocks['news-grid'] = [
            'name' => 'news-grid',
            'title' => __('News Grid', 'news'),
            'description' => __('Display news articles in a grid layout', 'news'),
            'icon' => 'grid-view',
            'category' => 'news',
            'keywords' => ['news', 'grid', 'articles'],
            'supports' => [
                'align' => ['wide', 'full'],
                'html' => false,
            ],
            'attributes' => [
                'postsPerPage' => [
                    'type' => 'number',
                    'default' => 6,
                ],
                'columns' => [
                    'type' => 'number',
                    'default' => 3,
                ],
                'sectionId' => [
                    'type' => 'number',
                    'default' => 0,
                ],
                'featured' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ];

        register_block_type('news/grid', [
            'attributes' => $this->blocks['news-grid']['attributes'],
            'render_callback' => [$this, 'renderNewsGridBlock'],
            'editor_script' => 'news-blocks',
            'editor_style' => 'news-blocks',
            'style' => 'news-blocks',
        ]);
    }

    /**
     * Render news article block
     */
    public function renderNewsArticleBlock(array $attributes): string
    {
        $article_id = $attributes['articleId'] ?? 0;
        
        if (!$article_id) {
            return '<p>' . esc_html__('Please select an article.', 'news') . '</p>';
        }

        $post = get_post($article_id);
        
        if (!$post || $post->post_type !== 'news') {
            return '<p>' . esc_html__('Article not found.', 'news') . '</p>';
        }

        ob_start();
        
        echo '<div class="news-article-block">';
        
        if ($attributes['showImage'] && has_post_thumbnail($article_id)) {
            echo '<div class="news-article-image">';
            echo get_the_post_thumbnail($article_id, 'large');
            echo '</div>';
        }
        
        echo '<h3 class="news-article-title">';
        echo '<a href="' . esc_url(get_permalink($article_id)) . '">';
        echo esc_html($post->post_title);
        echo '</a>';
        echo '</h3>';
        
        if ($attributes['showExcerpt']) {
            echo '<div class="news-article-excerpt">';
            echo wp_kses_post($post->post_excerpt);
            echo '</div>';
        }
        
        if ($attributes['showMeta']) {
            echo '<div class="news-article-meta">';
            echo '<span class="news-article-date">' . get_the_date('', $article_id) . '</span>';
            echo '<span class="news-article-author">' . get_the_author_meta('display_name', $post->post_author) . '</span>';
            echo '</div>';
        }
        
        echo '</div>';
        
        return ob_get_clean();
    }

    /**
     * Render news section block
     */
    public function renderNewsSectionBlock(array $attributes): string
    {
        $section_id = $attributes['sectionId'] ?? 0;
        $posts_per_page = $attributes['postsPerPage'] ?? 5;
        $layout = $attributes['layout'] ?? 'list';
        
        $query_args = [
            'post_type' => 'news',
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
        ];
        
        if ($section_id) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'news_section',
                    'field' => 'term_id',
                    'terms' => $section_id,
                ],
            ];
        }
        
        $query = new \WP_Query($query_args);
        
        if (!$query->have_posts()) {
            return '<p>' . esc_html__('No articles found.', 'news') . '</p>';
        }

        ob_start();
        
        echo '<div class="news-section-block news-layout-' . esc_attr($layout) . '">';
        
        while ($query->have_posts()) {
            $query->the_post();
            $this->renderArticleItem($attributes);
        }
        
        echo '</div>';
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }

    /**
     * Render breaking news block
     */
    public function renderBreakingNewsBlock(array $attributes): string
    {
        $count = $attributes['count'] ?? 3;
        $scroll = $attributes['scroll'] ?? true;
        $speed = $attributes['speed'] ?? 50;
        
        $query = new \WP_Query([
            'post_type' => 'news',
            'posts_per_page' => $count,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_news_article_meta',
                    'value' => 'breaking',
                    'compare' => 'LIKE',
                ],
            ],
        ]);
        
        if (!$query->have_posts()) {
            return '';
        }

        ob_start();
        
        echo '<div class="news-breaking-block' . ($scroll ? ' news-scrolling' : '') . '" data-speed="' . esc_attr($speed) . '">';
        echo '<div class="news-breaking-content">';
        
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="news-breaking-item">';
            echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }

    /**
     * Render news grid block
     */
    public function renderNewsGridBlock(array $attributes): string
    {
        $posts_per_page = $attributes['postsPerPage'] ?? 6;
        $columns = $attributes['columns'] ?? 3;
        $section_id = $attributes['sectionId'] ?? 0;
        $featured = $attributes['featured'] ?? false;
        
        $query_args = [
            'post_type' => 'news',
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
        ];
        
        if ($section_id) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'news_section',
                    'field' => 'term_id',
                    'terms' => $section_id,
                ],
            ];
        }
        
        if ($featured) {
            $query_args['meta_query'] = [
                [
                    'key' => '_news_article_meta',
                    'value' => 'featured',
                    'compare' => 'LIKE',
                ],
            ];
        }
        
        $query = new \WP_Query($query_args);
        
        if (!$query->have_posts()) {
            return '<p>' . esc_html__('No articles found.', 'news') . '</p>';
        }

        ob_start();
        
        echo '<div class="news-grid-block news-columns-' . esc_attr($columns) . '">';
        
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="news-grid-item">';
            $this->renderArticleItem($attributes);
            echo '</div>';
        }
        
        echo '</div>';
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }

    /**
     * Render article item
     */
    private function renderArticleItem(array $attributes): void
    {
        echo '<article class="news-article-item">';
        
        if ($attributes['showImage'] && has_post_thumbnail()) {
            echo '<div class="news-article-thumbnail">';
            the_post_thumbnail('medium');
            echo '</div>';
        }
        
        echo '<h3 class="news-article-title">';
        echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
        echo '</h3>';
        
        if ($attributes['showExcerpt']) {
            echo '<div class="news-article-excerpt">';
            the_excerpt();
            echo '</div>';
        }
        
        if ($attributes['showMeta']) {
            echo '<div class="news-article-meta">';
            echo '<span class="news-article-date">' . get_the_date() . '</span>';
            echo '<span class="news-article-author">' . get_the_author() . '</span>';
            echo '</div>';
        }
        
        echo '</article>';
    }

    /**
     * Enqueue block assets
     */
    public function enqueueBlockAssets(): void
    {
        $this->assets->enqueueBlockAssets();
    }

    /**
     * Enqueue block styles
     */
    public function enqueueBlockStyles(): void
    {
        $this->assets->enqueueStyle('news-blocks', 'css/blocks.css');
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
        return $this->blocks;
    }

    /**
     * Get block by name
     */
    public function getBlock(string $name): ?array
    {
        return $this->blocks[$name] ?? null;
    }
}
