<?php
/**
 * Render the byline block
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

// Get the byline meta field
$byline = get_post_meta($post_id, '_news_byline', true);

if (empty($byline)) {
    return '';
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Output the byline
echo '<div ' . $wrapper_attributes . '>' . esc_html($byline) . '</div>';
