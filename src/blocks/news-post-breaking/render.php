<?php
/**
 * Render the breaking block
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

// Get the breaking flag
$is_breaking = get_post_meta($post_id, '_news_breaking', true);

if (!$is_breaking) {
    return '';
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Output the breaking badge
echo '<div ' . $wrapper_attributes . '>';
echo '<span class="news-post-breaking-badge">' . esc_html__('Breaking', 'news') . '</span>';
echo '</div>';
