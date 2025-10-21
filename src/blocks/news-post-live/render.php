<?php
/**
 * Render the live block
 *
 * @param array    $attributes The block attributes.
 * @param string   $content    The block content.
 * @param WP_Block $block      The block object.
 * @return string The rendered block.
 */

// Force no caching for this file
if (!headers_sent()) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

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

// Get the live flag
$is_live = get_post_meta($post_id, '_news_is_live', true);

if (!$is_live) {
    return '';
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Output the live badge
echo '<div ' . $wrapper_attributes . '>';
echo '<span class="news-post-live-badge">' . esc_html__('queef', 'news') . '</span>';
echo '</div>';
