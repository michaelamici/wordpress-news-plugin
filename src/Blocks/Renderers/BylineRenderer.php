<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Byline Block Renderer
 * 
 * Handles server-side rendering of the news byline block
 */
class BylineRenderer
{
    /**
     * Render the byline block
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
            return '<div class="news-byline-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the byline from post meta
        $byline = get_post_meta($post_id, '_news_byline', true);

        if (empty($byline)) {
            return '';
        }

        // Start output with hooks
        do_action('news_before_byline', $attributes, $block, $post_id);

        $output = '<div class="news-byline">';
        $output .= '<span class="news-byline-text">' . esc_html($byline) . '</span>';
        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_byline_output', $output, $attributes, $block, $post_id);
    }
}
