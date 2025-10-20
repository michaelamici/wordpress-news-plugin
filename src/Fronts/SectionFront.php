<?php
/**
 * Section Front Implementation
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Fronts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Section-specific front implementation
 */
class SectionFront extends AbstractFront {
    
    /**
     * Section term object
     *
     * @var \WP_Term|null
     */
    protected ?\WP_Term $section = null;
    
    /**
     * Constructor
     *
     * @param string $id Front identifier
     * @param array $config Front configuration
     * @param \WP_Term|null $section Section term
     */
    public function __construct(string $id, array $config = [], ?\WP_Term $section = null) {
        parent::__construct($id, $config);
        $this->section = $section;
    }
    
    /**
     * Get section term
     *
     * @return \WP_Term|null
     */
    public function get_section(): ?\WP_Term {
        return $this->section;
    }
    
    /**
     * Build regions data for section front
     *
     * @return array
     */
    protected function build_regions(): array {
        $regions = [];
        
        if (!$this->section) {
            return $regions;
        }
        
        // Hero region - featured articles from this section
        $hero_query = $this->get_hero_query();
        $regions['hero'] = $this->build_region_from_query($hero_query, 'hero');
        
        // Rails region - latest articles from this section
        $rails_query = $this->get_rails_query();
        $regions['rails'] = $this->build_region_from_query($rails_query, 'rails');
        
        // Sub-sections region - child sections
        $subsections_query = $this->get_subsections_query();
        $regions['subsections'] = $this->build_subsections_region($subsections_query);
        
        return $regions;
    }
    
    /**
     * Get hero region query for section
     *
     * @return array
     */
    protected function get_hero_query(): array {
        $default_query = [
            'post_type' => 'news',
            'posts_per_page' => 1,
            'tax_query' => [
                [
                    'taxonomy' => 'news_section',
                    'field' => 'term_id',
                    'terms' => $this->section->term_id,
                ],
            ],
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
        
        return apply_filters('news_query_args', $query, 'section_hero', $this);
    }
    
    /**
     * Get rails region query for section
     *
     * @return array
     */
    protected function get_rails_query(): array {
        $default_query = [
            'post_type' => 'news',
            'posts_per_page' => 8,
            'tax_query' => [
                [
                    'taxonomy' => 'news_section',
                    'field' => 'term_id',
                    'terms' => $this->section->term_id,
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $config_query = $this->config['regions']['rails']['query'] ?? [];
        $query = wp_parse_args($config_query, $default_query);
        
        return apply_filters('news_query_args', $query, 'section_rails', $this);
    }
    
    /**
     * Get subsections query
     *
     * @return array
     */
    protected function get_subsections_query(): array {
        return [
            'taxonomy' => 'news_section',
            'parent' => $this->section->term_id,
            'hide_empty' => true,
            'orderby' => 'meta_value_num',
            'meta_key' => 'order',
            'order' => 'ASC',
        ];
    }
    
    /**
     * Build subsections region
     *
     * @param array $query_args Query arguments
     * @return array
     */
    protected function build_subsections_region(array $query_args): array {
        $subsections = get_terms($query_args);
        $items = [];
        
        if (!is_wp_error($subsections) && !empty($subsections)) {
            foreach ($subsections as $subsection) {
                $items[] = [
                    'id' => $subsection->term_id,
                    'name' => $subsection->name,
                    'url' => get_term_link($subsection),
                    'description' => $subsection->description,
                    'count' => $subsection->count,
                ];
            }
        }
        
        return [
            'name' => 'subsections',
            'items' => $items,
            'query_args' => $query_args,
        ];
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
