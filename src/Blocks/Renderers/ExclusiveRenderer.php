<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Exclusive Block Renderer
 * 
 * Handles server-side rendering of the news exclusive block
 */
class ExclusiveRenderer
{
    /**
     * Render the exclusive block
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
            return '<div class="news-exclusive-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the exclusive flag from post meta
        $is_exclusive = get_post_meta($post_id, '_news_exclusive', true);

        if (!$is_exclusive) {
            return '';
        }

        // Start output with hooks
        do_action('news_before_exclusive', $attributes, $block, $post_id);

        $output = '<div class="news-exclusive">';
        $output .= '<span class="news-exclusive-badge">' . __('Exclusive', 'news') . '</span>';
        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_exclusive_output', $output, $attributes, $block, $post_id);
    }
}
