<?php
/**
 * News Article Byline Block
 * 
 * Server-side render for the news article byline block.
 * Displays the _news_byline post meta field with fallback to author display name.
 */

// Get the post ID from context
$post_id = $block->context['postId'] ?? null;
$post_type = $block->context['postType'] ?? 'news';

// Get attributes
$text_align = $attributes['textAlign'] ?? '';
$is_link = $attributes['isLink'] ?? false;
$link_target = $attributes['linkTarget'] ?? '_self';

// Get the post
$post = null;
if ($post_id) {
    $post = get_post($post_id);
}

// If no post found, return empty
if (!$post) {
    return '';
}

// Get the byline from post meta
$byline = get_post_meta($post_id, '_news_byline', true);

// Fallback to author display name if no byline
if (empty($byline)) {
    $author = get_userdata($post->post_author);
    $byline = $author ? $author->display_name : '';
}

// If still no byline, return empty
if (empty($byline)) {
    return '';
}

// Build the content
$content = $byline;

// Make it a link if requested
if ($is_link) {
    $link_attributes = [
        'href' => get_permalink($post_id),
        'target' => $link_target,
    ];
    
    $link_attrs = '';
    foreach ($link_attributes as $attr => $value) {
        $link_attrs .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
    }
    
    $content = '<a' . $link_attrs . '>' . esc_html($byline) . '</a>';
} else {
    $content = esc_html($byline);
}

// Build the wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'wp-block-news-article-byline',
    'style' => !empty($text_align) ? 'text-align: ' . esc_attr($text_align) . ';' : '',
]);

// Output the byline
echo '<div ' . $wrapper_attributes . '>' . $content . '</div>';
