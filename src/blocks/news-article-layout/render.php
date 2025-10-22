<?php
/**
 * News Article Layout Block
 * 
 * Server-side render for the news article layout block.
 * Displays articles in hero-grid-list layout with featured article as hero.
 */

// Debug: Make sure this file is being called
error_log('News Article Layout Block: render.php called');

// Get block attributes
$grid_count = $attributes['gridCount'] ?? 3;
$section_filter = $attributes['sectionFilter'] ?? '';
$show_excerpt = $attributes['showExcerpt'] ?? true;
$show_date = $attributes['showDate'] ?? true;

// Build query args
$query_args = [
    'post_type' => 'news',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'modified',
    'order' => 'DESC',
    'meta_query' => []
];

// Add section filter if specified
if (!empty($section_filter)) {
    $query_args['tax_query'] = [
        [
            'taxonomy' => 'news_section',
            'field' => 'slug',
            'terms' => $section_filter
        ]
    ];
}

// Query all articles
$articles_query = new WP_Query($query_args);
$all_posts = $articles_query->posts ?? [];

if (empty($all_posts)) {
    // Debug: Check what post types exist and try fallback
    $all_post_types = get_post_types(['public' => true], 'names');
    $fallback_query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    $debug_info = 'No news posts found. Available post types: ' . implode(', ', $all_post_types) . '. Fallback posts: ' . count($fallback_query->posts);
    
    if (!empty($fallback_query->posts)) {
        $all_posts = $fallback_query->posts;
        $debug_info .= ' (Using fallback posts)';
    } else {
        return '<div class="news-article-layout-empty">' . __('No articles found.', 'news') . '<br><small>Debug: ' . $debug_info . '</small></div>';
    }
}

// Separate featured and regular articles
$featured_posts = [];
$regular_posts = [];

foreach ($all_posts as $post) {
    $is_featured = get_post_meta($post->ID, '_news_featured', true);
    if ($is_featured) {
        $featured_posts[] = $post;
    } else {
        $regular_posts[] = $post;
    }
}

// Sort featured posts by modified date (most recent first)
usort($featured_posts, function($a, $b) {
    return strtotime($b->post_modified) - strtotime($a->post_modified);
});

// Sort regular posts by modified date
usort($regular_posts, function($a, $b) {
    return strtotime($b->post_modified) - strtotime($a->post_modified);
});

// Get hero article (most recent featured)
$hero_post = !empty($featured_posts) ? $featured_posts[0] : null;

// Get grid articles (next articles up to grid_count)
$grid_posts = array_slice($regular_posts, 0, $grid_count);

// Get list articles (remaining)
$list_posts = array_slice($regular_posts, $grid_count);

// If no hero, use first regular post as hero
if (!$hero_post && !empty($regular_posts)) {
    $hero_post = $regular_posts[0];
    $grid_posts = array_slice($regular_posts, 1, $grid_count);
    $list_posts = array_slice($regular_posts, $grid_count + 1);
}

// Start output
$output = '<div class="news-article-layout">';

// Hero section
if ($hero_post) {
    $hero_image = get_the_post_thumbnail($hero_post->ID, 'large');
    $hero_url = get_permalink($hero_post->ID);
    $hero_title = get_the_title($hero_post->ID);
    $hero_excerpt = $show_excerpt ? get_the_excerpt($hero_post->ID) : '';
    $hero_date = $show_date ? get_the_date('', $hero_post->ID) : '';
    
    $output .= '<div class="news-hero">';
    $output .= '<article class="news-hero-article">';
    
    if ($hero_image) {
        $output .= '<div class="news-hero-image">';
        $output .= '<a href="' . esc_url($hero_url) . '">' . $hero_image . '</a>';
        $output .= '</div>';
    }
    
    $output .= '<div class="news-hero-content">';
    $output .= '<h2 class="news-hero-title"><a href="' . esc_url($hero_url) . '">' . esc_html($hero_title) . '</a></h2>';
    
    if ($hero_excerpt) {
        $output .= '<div class="news-hero-excerpt">' . wp_kses_post($hero_excerpt) . '</div>';
    }
    
    if ($hero_date) {
        $output .= '<div class="news-hero-date">' . esc_html($hero_date) . '</div>';
    }
    
    $output .= '</div>';
    $output .= '</article>';
    $output .= '</div>';
}

// Grid section
if (!empty($grid_posts)) {
    $output .= '<div class="news-grid">';
    foreach ($grid_posts as $post) {
        $post_image = get_the_post_thumbnail($post->ID, 'medium');
        $post_url = get_permalink($post->ID);
        $post_title = get_the_title($post->ID);
        $post_excerpt = $show_excerpt ? get_the_excerpt($post->ID) : '';
        $post_date = $show_date ? get_the_date('', $post->ID) : '';
        
        $output .= '<article class="news-grid-item">';
        
        if ($post_image) {
            $output .= '<div class="news-grid-image">';
            $output .= '<a href="' . esc_url($post_url) . '">' . $post_image . '</a>';
            $output .= '</div>';
        }
        
        $output .= '<div class="news-grid-content">';
        $output .= '<h3 class="news-grid-title"><a href="' . esc_url($post_url) . '">' . esc_html($post_title) . '</a></h3>';
        
        if ($post_excerpt) {
            $output .= '<div class="news-grid-excerpt">' . wp_kses_post($post_excerpt) . '</div>';
        }
        
        if ($post_date) {
            $output .= '<div class="news-grid-date">' . esc_html($post_date) . '</div>';
        }
        
        $output .= '</div>';
        $output .= '</article>';
    }
    $output .= '</div>';
}

// List section
if (!empty($list_posts)) {
    $output .= '<div class="news-list">';
    foreach ($list_posts as $post) {
        $post_url = get_permalink($post->ID);
        $post_title = get_the_title($post->ID);
        $post_excerpt = $show_excerpt ? get_the_excerpt($post->ID) : '';
        $post_date = $show_date ? get_the_date('', $post->ID) : '';
        
        $output .= '<article class="news-list-item">';
        $output .= '<h4 class="news-list-title"><a href="' . esc_url($post_url) . '">' . esc_html($post_title) . '</a></h4>';
        
        if ($post_excerpt) {
            $output .= '<div class="news-list-excerpt">' . wp_kses_post($post_excerpt) . '</div>';
        }
        
        if ($post_date) {
            $output .= '<div class="news-list-date">' . esc_html($post_date) . '</div>';
        }
        
        $output .= '</article>';
    }
    $output .= '</div>';
}

$output .= '</div>';

return $output;