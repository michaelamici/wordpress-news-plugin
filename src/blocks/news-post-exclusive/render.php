<?php
/**
 * Render the exclusive block
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

// Get the exclusive flag
$is_exclusive = get_post_meta($post_id, '_news_exclusive', true);

if (!$is_exclusive) {
    return '';
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Output the exclusive badge
echo '<div ' . $wrapper_attributes . '>';
echo '<span class="news-post-exclusive-badge">' . esc_html__('Exclusive', 'news') . '</span>';
echo '</div>';
