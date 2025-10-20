<?php
/**
 * Home Front Implementation
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Fronts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Home front page implementation
 */
class HomeFront extends AbstractFront {
    
    /**
     * Build regions data for home front
     *
     * @return array
     */
    protected function build_regions(): array {
        $regions = [];
        
        // Hero region - featured articles
        $hero_query = $this->get_hero_query();
        $regions['hero'] = $this->build_region_from_query($hero_query, 'hero');
        
        // Rails region - latest articles
        $rails_query = $this->get_rails_query();
        $regions['rails'] = $this->build_region_from_query($rails_query, 'rails');
        
        // Sidebar region - additional content
        $sidebar_query = $this->get_sidebar_query();
        $regions['sidebar'] = $this->build_region_from_query($sidebar_query, 'sidebar');
        
        return $regions;
    }
    
    /**
     * Get hero region query
     *
     * @return array
     */
    protected function get_hero_query(): array {
        $default_query = [
            'post_type' => 'news',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'is_featured',
                    'value' => true,
                    'compare' => '=',
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $config_query = $this->config['regions']['hero']['query'] ?? [];
        $query = wp_parse_args($config_query, $default_query);
        
        return apply_filters('news_query_args', $query, 'home_hero', $this);
    }
    
    /**
     * Get rails region query
     *
     * @return array
     */
    protected function get_rails_query(): array {
        $default_query = [
            'post_type' => 'news',
            'posts_per_page' => 6,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $config_query = $this->config['regions']['rails']['query'] ?? [];
        $query = wp_parse_args($config_query, $default_query);
        
        return apply_filters('news_query_args', $query, 'home_rails', $this);
    }
    
    /**
     * Get sidebar region query
     *
     * @return array
     */
    protected function get_sidebar_query(): array {
        $default_query = [
            'post_type' => 'news',
            'posts_per_page' => 4,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $config_query = $this->config['regions']['sidebar']['query'] ?? [];
        $query = wp_parse_args($config_query, $default_query);
        
        return apply_filters('news_query_args', $query, 'home_sidebar', $this);
    }
    
    /**
     * Build region data from WP_Query
     *
     * @param array $query_args Query arguments
     * @param string $region_name Region name
     * @return array
     */
    protected function build_region_from_query(array $query_args, string $region_name): array {
        $query = new \WP_Query($query_args);
        $items = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = $this->build_item_from_post();
            }
            wp_reset_postdata();
        }
        
        return [
            'name' => $region_name,
            'items' => $items,
            'query_args' => $query_args,
            'found_posts' => $query->found_posts,
        ];
    }
    
    /**
     * Build item data from current post
     *
     * @return array
     */
    protected function build_item_from_post(): array {
        global $post;
        
        $item = [
            'id' => $post->ID,
            'title' => get_the_title(),
            'url' => get_permalink(),
            'excerpt' => get_the_excerpt(),
            'date' => get_the_date(),
            'author' => get_the_author(),
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'medium'),
            'meta' => $this->get_item_meta(),
        ];
        
        return apply_filters('news_collect_article_data', $item, $post);
    }
    
    /**
     * Get item meta information
     *
     * @return string
     */
    protected function get_item_meta(): string {
        $meta_parts = [];
        
        // Breaking news indicator
        if (get_post_meta(get_the_ID(), 'is_breaking', true)) {
            $meta_parts[] = '<span class="news-meta-breaking">' . __('Breaking', 'news') . '</span>';
        }
        
        // Exclusive indicator
        if (get_post_meta(get_the_ID(), 'is_exclusive', true)) {
            $meta_parts[] = '<span class="news-meta-exclusive">' . __('Exclusive', 'news') . '</span>';
        }
        
        // Sponsored indicator
        if (get_post_meta(get_the_ID(), 'is_sponsored', true)) {
            $meta_parts[] = '<span class="news-meta-sponsored">' . __('Sponsored', 'news') . '</span>';
        }
        
        return implode(' ', $meta_parts);
    }
}
