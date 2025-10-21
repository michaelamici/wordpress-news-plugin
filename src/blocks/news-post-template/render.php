<?php
/**
 * Render the news post template block
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

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Get the byline meta field
$byline = get_post_meta($post_id, '_news_byline', true);

// Get the featured image
$featured_image = get_the_post_thumbnail($post_id, 'large');

// Get post sections
$sections = wp_get_post_terms($post_id, 'news_section');

// Get post beats
$beats = wp_get_post_terms($post_id, 'news_beat');

// Start output
$output = '<div ' . $wrapper_attributes . '>';
$output .= '<article class="news-post-template">';

// Byline
if (!empty($byline)) {
    $output .= '<div class="news-post-byline">' . esc_html($byline) . '</div>';
}

// Featured image
if ($featured_image) {
    $output .= '<div class="news-post-featured-image">' . $featured_image . '</div>';
}

// Post title
$output .= '<header class="news-post-header">';
$output .= '<h1 class="news-post-title">' . get_the_title($post_id) . '</h1>';

// Post meta
$output .= '<div class="news-post-meta">';
$output .= '<time class="news-post-date" datetime="' . get_the_date('c', $post_id) . '">' . get_the_date('', $post_id) . '</time>';

if (!empty($sections) && !is_wp_error($sections)) {
    $output .= '<div class="news-post-sections">';
    $output .= '<span class="news-post-section-label">' . __('Section:', 'news') . ' </span>';
    $section_names = array_map(function($section) {
        return $section->name;
    }, $sections);
    $output .= '<span class="news-post-section-names">' . implode(', ', $section_names) . '</span>';
    $output .= '</div>';
}

if (!empty($beats) && !is_wp_error($beats)) {
    $output .= '<div class="news-post-beats">';
    $output .= '<span class="news-post-beat-label">' . __('Beats:', 'news') . ' </span>';
    $beat_names = array_map(function($beat) {
        return $beat->name;
    }, $beats);
    $output .= '<span class="news-post-beat-names">' . implode(', ', $beat_names) . '</span>';
    $output .= '</div>';
}

$output .= '</div>'; // news-post-meta
$output .= '</header>'; // news-post-header

// Post content
$output .= '<div class="news-post-content">';
$output .= apply_filters('the_content', get_post_field('post_content', $post_id));
$output .= '</div>';

$output .= '</article>'; // news-post-template
$output .= '</div>'; // wrapper

echo $output;
