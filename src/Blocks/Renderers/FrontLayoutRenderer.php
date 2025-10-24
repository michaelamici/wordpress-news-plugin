<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Front Layout Block Renderer
 * 
 * Handles server-side rendering of the news front layout block
 */
class FrontLayoutRenderer
{
    /**
     * Render the front layout block
     * 
     * @param array    $attributes Block attributes
     * @param string   $content    Block content
     * @param \WP_Block $block     Block object
     * @return string Rendered HTML
     */
    public static function render(array $attributes, string $content, \WP_Block $block): string
    {
        // Get block attributes with defaults
        $posts_per_page = $attributes['postsPerPage'] ?? 10;
        $order_by = $attributes['orderBy'] ?? 'date';
        $order = $attributes['order'] ?? 'desc';
        $section_filter = $attributes['sectionFilter'] ?? '';
        $grid_count = $attributes['gridCount'] ?? 4;

        // Query articles
        $all_posts = self::queryArticles($posts_per_page, $order_by, $order, $section_filter);

        if (empty($all_posts)) {
            return '<div class="news-front-layout-empty">' . __('No articles found.', 'news') . '</div>';
        }

        // Organize posts by position
        $hero_post = !empty($all_posts) ? $all_posts[0] : null;
        $list_posts = array_slice($all_posts, 1); // All remaining posts as list

        // Start output with hooks
        do_action('news_before_front_layout', $attributes, $block);

        $output = '<div class="news-front-layout">';

        // Render sections
        $output .= self::renderHeroSection($hero_post, $block);
        $output .= self::renderListSection($list_posts);

        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_front_layout_output', $output, $attributes, $block);
    }

    /**
     * Query articles based on parameters
     * 
     * @param int    $posts_per_page Number of posts to retrieve
     * @param string $order_by       Order by field
     * @param string $order          Order direction
     * @param string $section_filter Section filter
     * @return array Array of post objects
     */
    private static function queryArticles(int $posts_per_page, string $order_by, string $order, string $section_filter): array
    {
        $query_args = [
            'post_type' => 'news',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'orderby' => $order_by,
            'order' => strtoupper($order)
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

        $articles_query = new \WP_Query($query_args);
        return $articles_query->posts ?? [];
    }

    /**
     * Render hero section
     * 
     * @param \WP_Post|null $hero_post Hero post object
     * @param \WP_Block     $block     Block object
     * @return string Rendered HTML
     */
    private static function renderHeroSection(?\WP_Post $hero_post, \WP_Block $block): string
    {
        if (!$hero_post) {
            return '';
        }

        $output = '<div class="news-front-layout__hero">';
        
        // Check for custom template
        $template_block = self::getCustomTemplate($block);
        if ($template_block) {
            // Set up global post data for template functions
            global $wp_query;
            $original_post = $wp_query->post ?? null;
            $wp_query->post = $hero_post;
            setup_postdata($hero_post);
            
            // Set the context AND attributes on the template block
            $template_block->context['news/postId'] = $hero_post->ID;
            $template_block->context['news/postType'] = 'news';
            $template_block->context['news/position'] = 'hero';
            
            // Also set attributes so they can be provided as context to children
            $template_block->attributes['postId'] = $hero_post->ID;
            $template_block->attributes['postType'] = 'news';
            $template_block->attributes['position'] = 'hero';
            
            // Render the template block with proper context
            $template_output = $template_block->render();
            $output .= $template_output;
            
            // Restore original post data
            if ($original_post) {
                $wp_query->post = $original_post;
                setup_postdata($original_post);
            }
        } else {
            $output .= self::renderDefaultHero($hero_post);
        }
        
        $output .= '</div>';
        return $output;
    }


    /**
     * Render list section
     * 
     * @param array $list_posts Array of post objects
     * @return string Rendered HTML
     */
    private static function renderListSection(array $list_posts): string
    {
        if (empty($list_posts)) {
            return '';
        }

        $output = '<div class="news-front-layout__list">';
        $output .= '<div class="news-list">';
        
        foreach ($list_posts as $post) {
            $output .= self::renderListItem($post);
        }
        
        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Get custom template block if it exists
     * 
     * @param \WP_Block $block Block object
     * @return \WP_Block|null Template block or null
     */
    private static function getCustomTemplate(\WP_Block $block): ?\WP_Block
    {
        if (empty($block->inner_blocks)) {
            return null;
        }

        foreach ($block->inner_blocks as $inner_block) {
            if ($inner_block->name === 'news/article-hero-post-template') {
                return $inner_block;
            }
        }

        return null;
    }

    /**
     * Render default hero markup
     * 
     * @param \WP_Post $post Post object
     * @return string Rendered HTML
     */
    private static function renderDefaultHero(\WP_Post $post): string
    {
        $hero_image = get_the_post_thumbnail($post->ID, 'large');
        $hero_url = get_permalink($post->ID);
        $hero_title = get_the_title($post->ID);
        $hero_excerpt = get_the_excerpt($post->ID);
        $hero_date = get_the_date('', $post->ID);
        
        $output = '<article class="news-hero-article">';
        
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
        
        return $output;
    }


    /**
     * Render list item
     * 
     * @param \WP_Post $post Post object
     * @return string Rendered HTML
     */
    private static function renderListItem(\WP_Post $post): string
    {
        $post_url = get_permalink($post->ID);
        $post_title = get_the_title($post->ID);
        $post_excerpt = get_the_excerpt($post->ID);
        $post_date = get_the_date('', $post->ID);
        
        $output = '<article class="news-list-item">';
        $output .= '<h4 class="news-list-title"><a href="' . esc_url($post_url) . '">' . esc_html($post_title) . '</a></h4>';
        
        if ($post_excerpt) {
            $output .= '<div class="news-list-excerpt">' . wp_kses_post($post_excerpt) . '</div>';
        }
        
        if ($post_date) {
            $output .= '<div class="news-list-date">' . esc_html($post_date) . '</div>';
        }
        
        $output .= '</article>';
        
        return $output;
    }
}
