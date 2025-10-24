<?php
/**
 * News Article Title Block
 * 
 * Server-side render for the news article title block.
 * Similar to core Post Title block but specifically for news articles.
 */

// Get the post ID from context
$post_id = $block->context['postId'] ?? null;
$post_type = $block->context['postType'] ?? 'news';

// Get attributes
$level = $attributes['level'] ?? 2;
$text_align = $attributes['textAlign'] ?? '';
$is_link = $attributes['isLink'] ?? true;
$link_target = $attributes['linkTarget'] ?? '_self';
$rel = $attributes['rel'] ?? '';

// Get the post
$post = null;
if ($post_id) {
    $post = get_post($post_id);
}

// If no post found, return empty
if (!$post) {
    return '';
}

// Get the post title
$title = get_the_title($post_id);

// If no title, return empty
if (empty($title)) {
    return '';
}

// Build the heading tag
$tag_name = 'h' . $level;

// Build the content
$content = $title;

// Make it a link if requested
if ($is_link) {
    $link_attributes = [
        'href' => get_permalink($post_id),
        'target' => $link_target,
    ];
    
    if (!empty($rel)) {
        $link_attributes['rel'] = $rel;
    }
    
    $link_attrs = '';
    foreach ($link_attributes as $attr => $value) {
        $link_attrs .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
    }
    
    $content = '<a' . $link_attrs . '>' . esc_html($title) . '</a>';
} else {
    $content = esc_html($title);
}

// Build the wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'wp-block-news-article-title',
    'style' => !empty($text_align) ? 'text-align: ' . esc_attr($text_align) . ';' : '',
]);

// Output the title
echo '<' . $tag_name . ' ' . $wrapper_attributes . '>' . $content . '</' . $tag_name . '>';
