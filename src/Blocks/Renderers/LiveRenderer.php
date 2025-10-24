<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Live Block Renderer
 * 
 * Handles server-side rendering of the news live block
 */
class LiveRenderer
{
    /**
     * Render the live block
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
            return '<div class="news-live-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the live flag from post meta
        $is_live = get_post_meta($post_id, '_news_is_live', true);

        if (!$is_live) {
            return '';
        }

        // Start output with hooks
        do_action('news_before_live', $attributes, $block, $post_id);

        $output = '<div class="news-live">';
        $output .= '<span class="news-live-badge">' . __('Live', 'news') . '</span>';
        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_live_output', $output, $attributes, $block, $post_id);
    }
}
