<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Article Template Block Renderer
 * 
 * Handles server-side rendering of the news article template block
 */
class ArticleTemplateRenderer
{
    /**
     * Render the article template block
     * 
     * @param array    $attributes Block attributes
     * @param string   $content    Block content
     * @param \WP_Block $block     Block object
     * @return string Rendered HTML
     */
    public static function render(array $attributes, string $content, \WP_Block $block): string
    {
        // Get context from parent block
        $post_id = $block->context['news/postId'] ?? $attributes['postId'] ?? null;
        $post_type = $block->context['news/postType'] ?? $attributes['postType'] ?? 'news';
        $position = $block->context['news/position'] ?? $attributes['position'] ?? 'hero';

        if (!$post_id) {
            return '<div class="news-article-template-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the post object
        $post = get_post($post_id);
        if (!$post) {
            return '<div class="news-article-template-empty">' . __('Post not found.', 'news') . '</div>';
        }

        // Set up global post data for template functions
        global $wp_query;
        $original_post = $wp_query->post ?? null;
        $wp_query->post = $post;
        setup_postdata($post);

        // Start output with hooks
        do_action('news_before_article_template', $attributes, $block, $post);

        $output = '<div class="news-article-template news-article-template--' . esc_attr($position) . '">';

        // Render inner blocks with post context
        if (!empty($block->inner_blocks)) {
            // Recursively set context on all inner blocks
            self::setBlockContext($block->inner_blocks, $post_id, $post_type, $position);
            
            foreach ($block->inner_blocks as $inner_block) {
                // Render each inner block
                $output .= $inner_block->render();
            }
        } else {
            // Render default template if no inner blocks
            $output .= self::renderDefaultTemplate($post, $position);
        }

        $output .= '</div>';

        // Restore original post data
        if ($original_post) {
            $wp_query->post = $original_post;
            setup_postdata($original_post);
        }

        // Apply filters and return
        return apply_filters('news_article_template_output', $output, $attributes, $block, $post);
    }

    /**
     * Render default template when no inner blocks are present
     * 
     * @param \WP_Post $post Post object
     * @param string   $position Position context (hero, grid, list)
     * @return string Rendered HTML
     */
    private static function renderDefaultTemplate(\WP_Post $post, string $position): string
    {
        $post_url = get_permalink($post->ID);
        $post_title = get_the_title($post->ID);
        $post_excerpt = get_the_excerpt($post->ID);
        $post_date = get_the_date('', $post->ID);
        $post_image = get_the_post_thumbnail($post->ID, 'large');
        
        $output = '<article class="news-article news-article--' . esc_attr($position) . '">';
        
        // Featured image
        if ($post_image) {
            $output .= '<div class="news-article-image">';
            $output .= '<a href="' . esc_url($post_url) . '">' . $post_image . '</a>';
            $output .= '</div>';
        }
        
        // Content
        $output .= '<div class="news-article-content">';
        $output .= '<h2 class="news-article-title"><a href="' . esc_url($post_url) . '">' . esc_html($post_title) . '</a></h2>';
        
        if ($post_excerpt) {
            $output .= '<div class="news-article-excerpt">' . wp_kses_post($post_excerpt) . '</div>';
        }
        
        if ($post_date) {
            $output .= '<div class="news-article-date">' . esc_html($post_date) . '</div>';
        }
        
        $output .= '</div>';
        $output .= '</article>';
        
        return $output;
    }
    
    /**
     * Recursively set context on blocks and their children
     * 
     * @param \WP_Block_List|array $blocks Array or WP_Block_List of WP_Block objects
     * @param int   $post_id Post ID
     * @param string $post_type Post type
     * @param string $position Position context
     */
    private static function setBlockContext($blocks, int $post_id, string $post_type, string $position): void
    {
        foreach ($blocks as $block) {
            // Set context on this block
            $block->context['news/postId'] = $post_id;
            $block->context['news/postType'] = $post_type;
            $block->context['news/position'] = $position;
            
            // Recursively set context on inner blocks
            if (!empty($block->inner_blocks)) {
                self::setBlockContext($block->inner_blocks, $post_id, $post_type, $position);
            }
        }
    }
}
