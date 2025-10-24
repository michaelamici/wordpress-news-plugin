<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Last Updated Block Renderer
 * 
 * Handles server-side rendering of the news last updated block
 */
class LastUpdatedRenderer
{
    /**
     * Render the last updated block
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
            return '<div class="news-last-updated-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the post object
        $post = get_post($post_id);
        if (!$post) {
            return '<div class="news-last-updated-empty">' . __('Post not found.', 'news') . '</div>';
        }

        // Get the last modified date
        $last_updated = get_the_modified_date('', $post->ID);
        $last_updated_time = get_the_modified_time('', $post->ID);

        if (!$last_updated) {
            return '';
        }

        // Start output with hooks
        do_action('news_before_last_updated', $attributes, $block, $post_id);

        $output = '<div class="news-last-updated">';
        $output .= '<span class="news-last-updated-label">' . __('Last updated:', 'news') . ' </span>';
        $output .= '<time class="news-last-updated-date" datetime="' . esc_attr($last_updated_time) . '">';
        $output .= esc_html($last_updated);
        $output .= '</time>';
        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_last_updated_output', $output, $attributes, $block, $post_id);
    }
}
