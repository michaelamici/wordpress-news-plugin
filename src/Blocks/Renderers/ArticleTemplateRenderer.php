<?php

declare(strict_types=1);

namespace NewsPlugin\Blocks\Renderers;

/**
 * Article Template Block Renderer
 * 
 * Handles server-side rendering of the news article template block
 */
class ArticleTemplateRenderer
{
    /**
     * Render the article template block
     * 
     * @param array    $attributes Block attributes
     * @param string   $content    Block content
     * @param \WP_Block $block     Block object
     * @return string Rendered HTML
     */
    public static function render(array $attributes, string $content, \WP_Block $block): string
    {
        // Get context from parent block
        $post_id = $block->context['news/postId'] ?? $attributes['postId'] ?? null;
        $post_type = $block->context['news/postType'] ?? $attributes['postType'] ?? 'news';
        $position = $block->context['news/position'] ?? $attributes['position'] ?? 'hero';


        if (!$post_id) {
            return '<div class="news-article-template-empty">' . __('No post context available.', 'news') . '</div>';
        }

        // Get the post object
        $post = get_post($post_id);
        if (!$post) {
            return '<div class="news-article-template-empty">' . __('Post not found.', 'news') . '</div>';
        }

        // Start output with hooks
        do_action('news_before_article_template', $attributes, $block, $post);

        $output = '<div class="news-article-template news-article-template--' . esc_attr($position) . '">';

        // Render inner blocks with post context
        if (!empty($block->inner_blocks)) {
            // Recursively set context on all inner blocks
            self::setBlockContext($block->inner_blocks, $post_id, $post_type, $position);
            
            // Use a custom rendering approach that doesn't rely on global post data
            $inner_blocks_array = [];
            foreach ($block->inner_blocks as $inner_block) {
                $inner_blocks_array[] = $inner_block;
            }
            $output .= self::renderCustomTemplate($post, $inner_blocks_array, $position);
        } else {
            // Render default template if no inner blocks
            $output .= self::renderDefaultTemplate($post, $position);
        }

        $output .= '</div>';

        // Apply filters and return
        return apply_filters('news_article_template_output', $output, $attributes, $block, $post);
    }

    /**
     * Render default template when no inner blocks are present
     * 
     * @param \WP_Post $post Post object
     * @param string   $position Position context (hero, grid, list)
     * @return string Rendered HTML
     */
    private static function renderDefaultTemplate(\WP_Post $post, string $position): string
    {
        $post_url = get_permalink($post->ID);
        $post_title = get_the_title($post->ID);
        $post_excerpt = get_the_excerpt($post->ID);
        $post_date = get_the_date('', $post->ID);
        $post_image = get_the_post_thumbnail($post->ID, 'large');
        
        $output = '<article class="news-article news-article--' . esc_attr($position) . '">';
        
        // Featured image
        if ($post_image) {
            $output .= '<div class="news-article-image">';
            $output .= '<a href="' . esc_url($post_url) . '">' . $post_image . '</a>';
            $output .= '</div>';
        }
        
        // Content
        $output .= '<div class="news-article-content">';
        $output .= '<h2 class="news-article-title"><a href="' . esc_url($post_url) . '">' . esc_html($post_title) . '</a></h2>';
        
        if ($post_excerpt) {
            $output .= '<div class="news-article-excerpt">' . wp_kses_post($post_excerpt) . '</div>';
        }
        
        if ($post_date) {
            $output .= '<div class="news-article-date">' . esc_html($post_date) . '</div>';
        }
        
        $output .= '</div>';
        $output .= '</article>';
        
        return $output;
    }

    /**
     * Render custom template using post data directly
     * 
     * @param \WP_Post $post Post object
     * @param array $inner_blocks Inner blocks to render
     * @param string $position Position context
     * @return string Rendered HTML
     */
    private static function renderCustomTemplate(\WP_Post $post, array $inner_blocks, string $position): string
    {
        $output = '';
        
        foreach ($inner_blocks as $inner_block) {
            // Handle different block types with custom rendering
            $block_name = $inner_block->name;
            
            switch ($block_name) {
                case 'core/post-title':
                    $output .= self::renderPostTitle($post, $inner_block);
                    break;
                    
                case 'core/post-featured-image':
                    $output .= self::renderPostFeaturedImage($post, $inner_block);
                    break;
                    
                case 'core/post-excerpt':
                    $output .= self::renderPostExcerpt($post, $inner_block);
                    break;
                    
                case 'core/post-date':
                    $output .= self::renderPostDate($post, $inner_block);
                    break;
                    
                case 'core/post-author':
                    $output .= self::renderPostAuthor($post, $inner_block);
                    break;
                    
                case 'news/article-title':
                    $output .= self::renderNewsArticleTitle($post, $inner_block);
                    break;
                    
                case 'news/article-byline':
                    $output .= self::renderNewsArticleByline($post, $inner_block);
                    break;
                    
                case 'core/group':
                    $output .= self::renderGroup($post, $inner_block);
                    break;
                    
                default:
                    // For other blocks, try to render normally but with isolated post data
                    $output .= self::renderBlockWithIsolatedPostData($post, $inner_block);
                    break;
            }
        }
        
        return $output;
    }

    /**
     * Render post title
     */
    private static function renderPostTitle(\WP_Post $post, \WP_Block $block): string
    {
        $attributes = $block->attributes;
        $level = $attributes['level'] ?? 2;
        $text_align = $attributes['textAlign'] ?? '';
        $font_size = $attributes['fontSize'] ?? '';
        
        $classes = ['wp-block-post-title'];
        if ($text_align) $classes[] = "has-text-align-{$text_align}";
        if ($font_size) $classes[] = "has-{$font_size}-font-size";
        
        $title = get_the_title($post->ID);
        $permalink = get_permalink($post->ID);
        
        return sprintf(
            '<h%d class="%s"><a href="%s">%s</a></h%d>',
            $level,
            esc_attr(implode(' ', $classes)),
            esc_url($permalink),
            esc_html($title),
            $level
        );
    }

    /**
     * Render post featured image
     */
    private static function renderPostFeaturedImage(\WP_Post $post, \WP_Block $block): string
    {
        $attributes = $block->attributes;
        $size = $attributes['sizeSlug'] ?? 'large';
        $align = $attributes['align'] ?? '';
        
        $classes = ['wp-block-post-featured-image'];
        if ($align) $classes[] = "align{$align}";
        
        $image = get_the_post_thumbnail($post->ID, $size);
        if (!$image) return '';
        
        $permalink = get_permalink($post->ID);
        
        return sprintf(
            '<figure class="%s"><a href="%s">%s</a></figure>',
            esc_attr(implode(' ', $classes)),
            esc_url($permalink),
            $image
        );
    }

    /**
     * Render post excerpt
     */
    private static function renderPostExcerpt(\WP_Post $post, \WP_Block $block): string
    {
        $attributes = $block->attributes;
        $more_text = $attributes['moreText'] ?? __('Read more', 'news');
        $excerpt_length = $attributes['excerptLength'] ?? 55;
        
        $excerpt = get_the_excerpt($post->ID);
        if (!$excerpt) {
            $excerpt = wp_trim_words($post->post_content, $excerpt_length);
        }
        
        return sprintf(
            '<div class="wp-block-post-excerpt">%s</div>',
            wp_kses_post($excerpt)
        );
    }

    /**
     * Render post date
     */
    private static function renderPostDate(\WP_Post $post, \WP_Block $block): string
    {
        $attributes = $block->attributes;
        $format = $attributes['format'] ?? get_option('date_format');
        
        $date = get_the_date($format, $post->ID);
        
        return sprintf(
            '<div class="wp-block-post-date">%s</div>',
            esc_html($date)
        );
    }

    /**
     * Render post author
     */
    private static function renderPostAuthor(\WP_Post $post, \WP_Block $block): string
    {
        $author = get_the_author_meta('display_name', $post->post_author);
        
        return sprintf(
            '<div class="wp-block-post-author">%s</div>',
            esc_html($author)
        );
    }

    /**
     * Render news article title
     */
    private static function renderNewsArticleTitle(\WP_Post $post, \WP_Block $block): string
    {
        $attributes = $block->attributes;
        $level = $attributes['level'] ?? 2;
        
        $title = get_the_title($post->ID);
        $permalink = get_permalink($post->ID);
        
        return sprintf(
            '<h%d class="wp-block-news-article-title"><a href="%s">%s</a></h%d>',
            $level,
            esc_url($permalink),
            esc_html($title),
            $level
        );
    }

    /**
     * Render news article byline
     */
    private static function renderNewsArticleByline(\WP_Post $post, \WP_Block $block): string
    {
        $author = get_the_author_meta('display_name', $post->post_author);
        
        return sprintf(
            '<div class="wp-block-news-article-byline">%s</div>',
            esc_html($author)
        );
    }

    /**
     * Render group block
     */
    private static function renderGroup(\WP_Post $post, \WP_Block $block): string
    {
        $attributes = $block->attributes;
        $className = $attributes['className'] ?? '';
        
        $output = '<div class="wp-block-group';
        if ($className) $output .= ' ' . esc_attr($className);
        $output .= '">';
        
        // Render inner blocks recursively
        if (!empty($block->inner_blocks)) {
            foreach ($block->inner_blocks as $inner_block) {
                $output .= self::renderCustomTemplate($post, [$inner_block], 'list');
            }
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render block with isolated post data (fallback)
     */
    private static function renderBlockWithIsolatedPostData(\WP_Post $post, \WP_Block $block): string
    {
        // Use output buffering to isolate each article's rendering
        ob_start();
        
        // Set up global post data for template functions
        global $wp_query;
        $original_post = $wp_query->post ?? null;
        $wp_query->post = $post;
        setup_postdata($post);
        
        // Render the block
        echo $block->render();
        
        // Restore original post data immediately after rendering
        if ($original_post) {
            $wp_query->post = $original_post;
            setup_postdata($original_post);
        }
        
        return ob_get_clean();
    }
    
    /**
     * Recursively set context on blocks and their children
     * 
     * @param \WP_Block_List|array $blocks Array or WP_Block_List of WP_Block objects
     * @param int   $post_id Post ID
     * @param string $post_type Post type
     * @param string $position Position context
     */
    private static function setBlockContext($blocks, int $post_id, string $post_type, string $position): void
    {
        foreach ($blocks as $block) {
            // Set context on this block
            $block->context['news/postId'] = $post_id;
            $block->context['news/postType'] = $post_type;
            $block->context['news/position'] = $position;
            
            // Recursively set context on inner blocks
            if (!empty($block->inner_blocks)) {
                self::setBlockContext($block->inner_blocks, $post_id, $post_type, $position);
            }
        }
    }
}
