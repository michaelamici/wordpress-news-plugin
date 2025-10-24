<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Sponsored Block Renderer
 * 
 * Handles server-side rendering of the news sponsored block
 */
class SponsoredRenderer
{
    /**
     * Render the sponsored block
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
            return '<div class="news-sponsored-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the sponsored flag from post meta
        $is_sponsored = get_post_meta($post_id, '_news_sponsored', true);

        if (!$is_sponsored) {
            return '';
        }

        // Start output with hooks
        do_action('news_before_sponsored', $attributes, $block, $post_id);

        $output = '<div class="news-sponsored">';
        $output .= '<span class="news-sponsored-badge">' . __('Sponsored', 'news') . '</span>';
        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_sponsored_output', $output, $attributes, $block, $post_id);
    }
}
