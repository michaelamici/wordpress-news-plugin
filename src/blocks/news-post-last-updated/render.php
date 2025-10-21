<?php
/**
 * Render the last updated block
 *
 * @param array    $attributes The block attributes.
 * @param string   $content    The block content.
 * @param WP_Block $block      The block object.
 * @return string The rendered block.
 */

// Get post ID from block context
$post_id = $block->context['postId'] ?? 0;

if (!$post_id) {
    return '';
}

// Verify this is a news post
$post = get_post($post_id);
if (!$post || $post->post_type !== 'news') {
    return '';
}

// Get the last updated date
$last_updated = get_post_meta($post_id, '_news_last_updated', true);

if (empty($last_updated)) {
    return '';
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Output the last updated date
echo '<div ' . $wrapper_attributes . '>';
echo '<span class="news-post-last-updated-content">';
echo esc_html__('Updated: ', 'news') . esc_html($last_updated);
echo '</span>';
echo '</div>';
