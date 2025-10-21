<?php

declare(strict_types=1);

namespace NewsPlugin\Frontend;

use NewsPlugin\Core\Plugin;
use NewsPlugin\Assets\AssetManager;

/**
 * Frontend class
 * 
 * Handles all frontend-related functionality
 */
class Frontend
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
        
        $this->init();
    }

    /**
     * Initialize frontend
     */
    private function init(): void
    {
        // Add frontend hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('wp_head', [$this, 'addMetaTags']);
        add_action('wp_footer', [$this, 'addFooterScripts']);
        
        // Add template hooks
        add_filter('template_include', [$this, 'templateInclude']);
        add_action('news_before_content', [$this, 'beforeContent']);
        add_action('news_after_content', [$this, 'afterContent']);
        
        // Add shortcodes
        add_shortcode('news_articles', [$this, 'renderArticlesShortcode']);
        add_shortcode('news_sections', [$this, 'renderSectionsShortcode']);
        add_shortcode('news_breaking', [$this, 'renderBreakingNewsShortcode']);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueueFrontendAssets(): void
    {
        $this->assets->enqueueFrontendAssets();
    }

    /**
     * Add meta tags
     */
    public function addMetaTags(): void
    {
        if (is_singular('news')) {
            $this->addArticleMetaTags();
        }
    }

    /**
     * Add article meta tags
     */
    private function addArticleMetaTags(): void
    {
        global $post;
        
        if (!$post || $post->post_type !== 'news') {
            return;
        }

        $meta = get_post_meta($post->ID, '_news_article_meta', true);
        
        // Add Open Graph tags
        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($post->post_title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($post->post_excerpt) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post->ID)) . '" />' . "\n";
        
        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
            if ($image) {
                echo '<meta property="og:image" content="' . esc_url($image[0]) . '" />' . "\n";
            }
        }
        
        // Add Twitter Card tags
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($post->post_title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($post->post_excerpt) . '" />' . "\n";
        
        // Add article specific meta
        if (!empty($meta['breaking'])) {
            echo '<meta name="news:breaking" content="true" />' . "\n";
        }
        
        if (!empty($meta['exclusive'])) {
            echo '<meta name="news:exclusive" content="true" />' . "\n";
        }
    }

    /**
     * Add footer scripts
     */
    public function addFooterScripts(): void
    {
        // Add analytics or other footer scripts here
        do_action('news_footer_scripts');
    }

    /**
     * Template include filter
     */
    public function templateInclude(string $template): string
    {
        if (is_singular('news')) {
            $custom_template = locate_template('single-news.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        
        if (is_tax('news_section')) {
            $custom_template = locate_template('taxonomy-news_section.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        
        return $template;
    }

    /**
     * Before content action
     */
    public function beforeContent(): void
    {
        if (is_singular('news')) {
            $this->renderBreakingNewsBanner();
        }
    }

    /**
     * After content action
     */
    public function afterContent(): void
    {
        if (is_singular('news')) {
            $this->renderRelatedArticles();
        }
    }

    /**
     * Render breaking news banner
     */
    private function renderBreakingNewsBanner(): void
    {
        global $post;
        
        $meta = get_post_meta($post->ID, '_news_article_meta', true);
        
        if (!empty($meta['breaking'])) {
            echo '<div class="news-breaking-banner">';
            echo '<span class="news-breaking-label">' . esc_html__('BREAKING', 'news') . '</span>';
            echo '<span class="news-breaking-text">' . esc_html($post->post_title) . '</span>';
            echo '</div>';
        }
    }

    /**
     * Render related articles
     */
    private function renderRelatedArticles(): void
    {
        global $post;
        
        $sections = wp_get_post_terms($post->ID, 'news_section');
        
        if (empty($sections)) {
            return;
        }
        
        $section_ids = wp_list_pluck($sections, 'term_id');
        
        $related_query = new \WP_Query([
            'post_type' => 'news',
            'posts_per_page' => 3,
            'post__not_in' => [$post->ID],
            'tax_query' => [
                [
                    'taxonomy' => 'news_section',
                    'field' => 'term_id',
                    'terms' => $section_ids,
                ],
            ],
        ]);
        
        if ($related_query->have_posts()) {
            echo '<div class="news-related-articles">';
            echo '<h3>' . esc_html__('Related Articles', 'news') . '</h3>';
            echo '<ul>';
            
            while ($related_query->have_posts()) {
                $related_query->the_post();
                echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
            }
            
            echo '</ul>';
            echo '</div>';
            
            wp_reset_postdata();
        }
    }

    /**
     * Render articles shortcode
     */
    public function renderArticlesShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'count' => 5,
            'section' => '',
            'featured' => false,
            'breaking' => false,
            'layout' => 'list',
        ], $atts);

        $query_args = [
            'post_type' => 'news',
            'posts_per_page' => (int) $atts['count'],
            'post_status' => 'publish',
        ];

        if (!empty($atts['section'])) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'news_section',
                    'field' => 'slug',
                    'terms' => $atts['section'],
                ],
            ];
        }

        if ($atts['featured']) {
            $query_args['meta_query'] = [
                [
                    'key' => '_news_article_meta',
                    'value' => 'featured',
                    'compare' => 'LIKE',
                ],
            ];
        }

        if ($atts['breaking']) {
            $query_args['meta_query'] = [
                [
                    'key' => '_news_article_meta',
                    'value' => 'breaking',
                    'compare' => 'LIKE',
                ],
            ];
        }

        $query = new \WP_Query($query_args);
        
        if (!$query->have_posts()) {
            return '<p>' . esc_html__('No articles found.', 'news') . '</p>';
        }

        ob_start();
        
        echo '<div class="news-articles-shortcode news-layout-' . esc_attr($atts['layout']) . '">';
        
        while ($query->have_posts()) {
            $query->the_post();
            $this->renderArticleItem();
        }
        
        echo '</div>';
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }

    /**
     * Render sections shortcode
     */
    public function renderSectionsShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'parent' => 0,
            'show_count' => true,
        ], $atts);

        $sections = get_terms([
            'taxonomy' => 'news_section',
            'parent' => (int) $atts['parent'],
            'hide_empty' => false,
        ]);

        if (empty($sections) || is_wp_error($sections)) {
            return '<p>' . esc_html__('No sections found.', 'news') . '</p>';
        }

        ob_start();
        
        echo '<div class="news-sections-shortcode">';
        echo '<ul class="news-sections-list">';
        
        foreach ($sections as $section) {
            echo '<li>';
            echo '<a href="' . esc_url(get_term_link($section)) . '">' . esc_html($section->name) . '</a>';
            
            if ($atts['show_count']) {
                echo ' <span class="news-section-count">(' . $section->count . ')</span>';
            }
            
            echo '</li>';
        }
        
        echo '</ul>';
        echo '</div>';
        
        return ob_get_clean();
    }

    /**
     * Render breaking news shortcode
     */
    public function renderBreakingNewsShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'count' => 3,
            'scroll' => true,
        ], $atts);

        $query = new \WP_Query([
            'post_type' => 'news',
            'posts_per_page' => (int) $atts['count'],
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
        
        echo '<div class="news-breaking-shortcode' . ($atts['scroll'] ? ' news-scrolling' : '') . '">';
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
     * Render article item
     */
    private function renderArticleItem(): void
    {
        echo '<article class="news-article-item">';
        echo '<h3><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
        
        if (has_post_thumbnail()) {
            echo '<div class="news-article-thumbnail">';
            the_post_thumbnail('medium');
            echo '</div>';
        }
        
        echo '<div class="news-article-excerpt">';
        the_excerpt();
        echo '</div>';
        
        echo '<div class="news-article-meta">';
        echo '<span class="news-article-date">' . get_the_date() . '</span>';
        echo '<span class="news-article-author">' . get_the_author() . '</span>';
        echo '</div>';
        
        echo '</article>';
    }

    /**
     * Get article data for JSON-LD
     */
    public function getArticleJsonLd(): array
    {
        global $post;
        
        if (!$post || $post->post_type !== 'news') {
            return [];
        }

        $meta = get_post_meta($post->ID, '_news_article_meta', true);
        $sections = wp_get_post_terms($post->ID, 'news_section');
        
        $json_ld = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $post->post_title,
            'description' => $post->post_excerpt,
            'url' => get_permalink($post->ID),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author(),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'url' => home_url(),
            ],
        ];

        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
            if ($image) {
                $json_ld['image'] = [
                    '@type' => 'ImageObject',
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                ];
            }
        }

        if (!empty($sections)) {
            $json_ld['articleSection'] = wp_list_pluck($sections, 'name');
        }

        if (!empty($meta['breaking'])) {
            $json_ld['breakingNews'] = true;
        }

        return $json_ld;
    }
}
