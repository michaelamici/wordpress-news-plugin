<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Breaking Block Renderer
 * 
 * Handles server-side rendering of the news breaking block
 */
class BreakingRenderer
{
    /**
     * Render the breaking block
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
            return '<div class="news-breaking-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the breaking flag from post meta
        $is_breaking = get_post_meta($post_id, '_news_breaking', true);

        if (!$is_breaking) {
            return '';
        }

        // Start output with hooks
        do_action('news_before_breaking', $attributes, $block, $post_id);

        $output = '<div class="news-breaking">';
        $output .= '<span class="news-breaking-badge">' . __('Breaking', 'news') . '</span>';
        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_breaking_output', $output, $attributes, $block, $post_id);
    }
}
