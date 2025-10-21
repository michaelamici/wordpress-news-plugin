<?php
/**
 * Render the sponsored block
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

// Get the sponsored flag
$is_sponsored = get_post_meta($post_id, '_news_sponsored', true);

if (!$is_sponsored) {
    return '';
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Output the sponsored badge
echo '<div ' . $wrapper_attributes . '>';
echo '<span class="news-post-sponsored-badge">' . esc_html__('Sponsored', 'news') . '</span>';
echo '</div>';
