<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Featured Block Renderer
 * 
 * Handles server-side rendering of the news featured block
 */
class FeaturedRenderer
{
    /**
     * Render the featured block
     * 
     * @param array    $attributes Block attributes
     * @param string   $content    Block content
     * @param \WP_Block $block     Block object
     * @return string Rendered HTML
     */
    public static function render(array $attributes, string $content, \WP_Block $block): string
    {
        // Get context from parent block
        $post_id = $block->context['news/postId'] ?? null;
        $post_type = $block->context['news/postType'] ?? 'news';

        if (!$post_id) {
            return '<div class="news-featured-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the featured flag from post meta
        $is_featured = get_post_meta($post_id, '_news_featured', true);

        if (!$is_featured) {
            return '';
        }

        // Start output with hooks
        do_action('news_before_featured', $attributes, $block, $post_id);

        $output = '<div class="news-featured">';
        $output .= '<span class="news-featured-badge">' . __('Featured', 'news') . '</span>';
        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_featured_output', $output, $attributes, $block, $post_id);
    }
}
