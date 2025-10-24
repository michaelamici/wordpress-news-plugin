<?php

declare(strict_types=1);

namespace NewsPlugin\Api;

use NewsPlugin\Core\Plugin;
use NewsPlugin\Security\SecurityManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API Manager
 * 
 * Handles REST API endpoints and controllers
 */
class RestApi
{
    /**
     * Plugin instance
     */
    private Plugin $plugin;

    /**
     * Security manager
     */
    private SecurityManager $security;

    /**
     * API namespace
     */
    private const NAMESPACE = 'news/v1';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin = Plugin::instance();
        $this->security = $this->plugin->getSecurityManager();
        
        $this->init();
    }

    /**
     * Initialize REST API
     */
    private function init(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
        // Also register routes immediately for testing
        $this->registerRoutes();
    }



    /**
     * Register REST API routes
     */
    public function registerRoutes(): void
    {
        // Articles endpoints
        register_rest_route(self::NAMESPACE, '/articles', [
            'methods' => 'GET',
            'callback' => [$this, 'getArticles'],
            'permission_callback' => '__return_true',
            'args' => [
                'page' => [
                    'description' => 'Current page of the collection.',
                    'type' => 'integer',
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'description' => 'Maximum number of items to be returned in result set.',
                    'type' => 'integer',
                    'default' => 10,
                    'maximum' => 100,
                    'sanitize_callback' => 'absint',
                ],
                'section' => [
                    'description' => 'Filter by news section slug.',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'featured' => [
                    'description' => 'Filter by featured articles.',
                    'type' => 'boolean',
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'breaking' => [
                    'description' => 'Filter by breaking news.',
                    'type' => 'boolean',
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/articles/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getArticle'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'description' => 'Unique identifier for the article.',
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Sections endpoints
        register_rest_route(self::NAMESPACE, '/sections', [
            'methods' => 'GET',
            'callback' => [$this, 'getSections'],
            'permission_callback' => '__return_true',
            'args' => [
                'parent' => [
                    'description' => 'Filter by parent section ID.',
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'hide_empty' => [
                    'description' => 'Whether to hide sections with no articles.',
                    'type' => 'boolean',
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/sections/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getSection'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'description' => 'Unique identifier for the section.',
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Analytics endpoints
        register_rest_route(self::NAMESPACE, '/analytics', [
            'methods' => 'GET',
            'callback' => [$this, 'getAnalytics'],
            'permission_callback' => [$this, 'checkAnalyticsPermission'],
        ]);

        // Search endpoints
        register_rest_route(self::NAMESPACE, '/search', [
            'methods' => 'GET',
            'callback' => [$this, 'searchArticles'],
            'permission_callback' => '__return_true',
            'args' => [
                'q' => [
                    'description' => 'Search query.',
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'page' => [
                    'description' => 'Current page of the collection.',
                    'type' => 'integer',
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'description' => 'Maximum number of items to be returned in result set.',
                    'type' => 'integer',
                    'default' => 10,
                    'maximum' => 100,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // News articles endpoint for layout block
        register_rest_route(self::NAMESPACE, '/layout', [
            'methods' => 'GET',
            'callback' => [$this, 'getArticlesLayout'],
            'permission_callback' => '__return_true',
            'args' => [
                'grid_count' => [
                    'type' => 'integer',
                    'default' => 3,
                    'minimum' => 1,
                    'maximum' => 6,
                ],
                'section_filter' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'show_excerpt' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'show_date' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
        ]);

        // Article layout endpoint for custom post template
        register_rest_route(self::NAMESPACE, '/article-layout', [
            'methods' => 'GET',
            'callback' => [$this, 'getArticleLayout'],
            'permission_callback' => '__return_true',
            'args' => [
                'per_page' => [
                    'type' => 'integer',
                    'default' => 10,
                    'maximum' => 100,
                    'sanitize_callback' => 'absint',
                ],
                'offset' => [
                    'type' => 'integer',
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                ],
                'section' => [
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'orderby' => [
                    'type' => 'string',
                    'default' => 'date',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'order' => [
                    'type' => 'string',
                    'default' => 'DESC',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'heroCount' => [
                    'type' => 'integer',
                    'default' => 1,
                    'minimum' => 0,
                    'maximum' => 3,
                    'sanitize_callback' => 'absint',
                ],
                'gridCount' => [
                    'type' => 'integer',
                    'default' => 3,
                    'minimum' => 0,
                    'maximum' => 12,
                    'sanitize_callback' => 'absint',
                ],
                'featured' => [
                    'type' => 'boolean',
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
                'breaking' => [
                    'type' => 'boolean',
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ],
            ],
        ]);

    }

    /**
     * Get articles
     */
    public function getArticles(WP_REST_Request $request): WP_REST_Response
    {
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $section = $request->get_param('section');
        $featured = $request->get_param('featured');
        $breaking = $request->get_param('breaking');

        $query_args = [
            'post_type' => 'news',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish',
        ];

        if (!empty($section)) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'news_section',
                    'field' => 'slug',
                    'terms' => $section,
                ],
            ];
        }

        if ($featured) {
            $query_args['meta_query'] = [
                [
                    'key' => '_news_article_meta',
                    'value' => 'featured',
                    'compare' => 'LIKE',
                ],
            ];
        }

        if ($breaking) {
            $query_args['meta_query'] = [
                [
                    'key' => '_news_article_meta',
                    'value' => 'breaking',
                    'compare' => 'LIKE',
                ],
            ];
        }

        $query = new \WP_Query($query_args);
        $articles = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $articles[] = $this->formatArticle(get_post());
            }
        }

        wp_reset_postdata();

        return new WP_REST_Response([
            'articles' => $articles,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'page' => $page,
            'per_page' => $per_page,
        ]);
    }

    /**
     * Get articles for the layout block
     */
    public function getArticlesLayout(WP_REST_Request $request): WP_REST_Response
    {
        $grid_count = $request->get_param('grid_count');
        $section_filter = $request->get_param('section_filter');
        $show_excerpt = $request->get_param('show_excerpt');
        $show_date = $request->get_param('show_date');

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
        $articles_query = new \WP_Query($query_args);
        $all_posts = $articles_query->posts ?? [];

        if (empty($all_posts)) {
            // Try fallback to regular posts
            $fallback_query = new \WP_Query([
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 10,
                'orderby' => 'modified',
                'order' => 'DESC'
            ]);
            
            if (!empty($fallback_query->posts)) {
                $all_posts = $fallback_query->posts;
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

        // Format posts for JSON response
        $format_post = function($post) use ($show_excerpt, $show_date) {
            return [
                'id' => $post->ID,
                'title' => get_the_title($post->ID),
                'excerpt' => $show_excerpt ? get_the_excerpt($post->ID) : '',
                'date' => $show_date ? get_the_date('', $post->ID) : '',
                'url' => get_permalink($post->ID),
                'image' => get_the_post_thumbnail_url($post->ID, 'large'),
                'image_medium' => get_the_post_thumbnail_url($post->ID, 'medium'),
                'featured' => get_post_meta($post->ID, '_news_featured', true),
            ];
        };

        $response_data = [
            'hero' => $hero_post ? $format_post($hero_post) : null,
            'grid' => array_map($format_post, $grid_posts),
            'list' => array_map($format_post, $list_posts),
            'total' => count($all_posts),
        ];

        return new WP_REST_Response($response_data, 200);
    }

    /**
     * Get single article
     */
    public function getArticle(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');
        $post = get_post($id);

        if (!$post || $post->post_type !== 'news') {
            return new WP_Error('article_not_found', 'Article not found', ['status' => 404]);
        }

        return new WP_REST_Response($this->formatArticle($post));
    }

    /**
     * Get sections
     */
    public function getSections(WP_REST_Request $request): WP_REST_Response
    {
        $parent = $request->get_param('parent');
        $hide_empty = $request->get_param('hide_empty');

        $args = [
            'taxonomy' => 'news_section',
            'hide_empty' => $hide_empty,
        ];

        if ($parent !== null) {
            $args['parent'] = $parent;
        }

        $sections = get_terms($args);
        $formatted_sections = [];

        if (!empty($sections) && !is_wp_error($sections)) {
            foreach ($sections as $section) {
                $formatted_sections[] = $this->formatSection($section);
            }
        }

        return new WP_REST_Response($formatted_sections);
    }

    /**
     * Get single section
     */
    public function getSection(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');
        $section = get_term($id, 'news_section');

        if (!$section || is_wp_error($section)) {
            return new WP_Error('section_not_found', 'Section not found', ['status' => 404]);
        }

        return new WP_REST_Response($this->formatSection($section));
    }

    /**
     * Get analytics data
     */
    public function getAnalytics(WP_REST_Request $request): WP_REST_Response
    {
        // This would typically fetch from the analytics table
        $analytics = [
            'total_articles' => wp_count_posts('news')->publish,
            'total_sections' => wp_count_terms('news_section'),
            'page_views' => 0,
            'unique_visitors' => 0,
            'popular_articles' => [],
        ];

        return new WP_REST_Response($analytics);
    }

    /**
     * Search articles
     */
    public function searchArticles(WP_REST_Request $request): WP_REST_Response
    {
        $query = $request->get_param('q');
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');

        $search_args = [
            'post_type' => 'news',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish',
            's' => $query,
        ];

        $search_query = new \WP_Query($search_args);
        $articles = [];

        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                $articles[] = $this->formatArticle(get_post());
            }
        }

        wp_reset_postdata();

        return new WP_REST_Response([
            'articles' => $articles,
            'total' => $search_query->found_posts,
            'pages' => $search_query->max_num_pages,
            'page' => $page,
            'per_page' => $per_page,
            'query' => $query,
        ]);
    }

    /**
     * Format article for API response
     */
    private function formatArticle($post): array
    {
        $meta = get_post_meta($post->ID, '_news_article_meta', true);
        $sections = wp_get_post_terms($post->ID, 'news_section');
        $featured_image = null;

        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
            if ($image) {
                $featured_image = [
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                    'alt' => get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true),
                ];
            }
        }

        return [
            'id' => $post->ID,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'excerpt' => $post->post_excerpt,
            'content' => $post->post_content,
            'featured_image' => $featured_image,
            'author' => [
                'id' => $post->post_author,
                'name' => get_the_author_meta('display_name', $post->post_author),
                'slug' => get_the_author_meta('user_nicename', $post->post_author),
            ],
            'sections' => array_map([$this, 'formatSection'], $sections),
            'meta' => [
                'featured' => !empty($meta['featured']),
                'breaking' => !empty($meta['breaking']),
                'exclusive' => !empty($meta['exclusive']),
                'sponsored' => !empty($meta['sponsored']),
            ],
            'date' => [
                'published' => get_the_date('c', $post->ID),
                'modified' => get_the_modified_date('c', $post->ID),
            ],
            'url' => get_permalink($post->ID),
        ];
    }

    /**
     * Format section for API response
     */
    private function formatSection($section): array
    {
        return [
            'id' => $section->term_id,
            'name' => $section->name,
            'slug' => $section->slug,
            'description' => $section->description,
            'parent' => $section->parent,
            'count' => $section->count,
            'url' => get_term_link($section),
        ];
    }

    /**
     * Check analytics permission
     */
    public function checkAnalyticsPermission(): bool
    {
        return $this->security->canManageNews();
    }

    /**
     * Get API namespace
     */
    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    /**
     * Get API base URL
     */
    public function getApiBaseUrl(): string
    {
        return rest_url(self::NAMESPACE);
    }

    /**
     * Get article layout for post template block
     */
    public function getArticleLayout(WP_REST_Request $request): WP_REST_Response
    {
        $per_page = $request->get_param('per_page');
        $offset = $request->get_param('offset');
        $section = $request->get_param('section');
        $orderby = $request->get_param('orderby');
        $order = $request->get_param('order');
        $heroCount = $request->get_param('heroCount');
        $gridCount = $request->get_param('gridCount');
        $featured = $request->get_param('featured');
        $breaking = $request->get_param('breaking');

        // Build query arguments
        $query_args = [
            'post_type' => 'news',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'offset' => $offset,
            'orderby' => $orderby,
            'order' => $order,
        ];

        // Add section filter if specified
        if (!empty($section)) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'news_section',
                    'field' => 'slug',
                    'terms' => $section,
                ],
            ];
        }

        // Add meta queries for featured/breaking if specified
        $meta_query = [];
        if ($featured) {
            $meta_query[] = [
                'key' => '_news_featured',
                'value' => '1',
                'compare' => '='
            ];
        }
        if ($breaking) {
            $meta_query[] = [
                'key' => '_news_breaking',
                'value' => '1',
                'compare' => '='
            ];
        }
        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        // Execute query
        $query = new \WP_Query($query_args);
        $posts = $query->posts;

        // Split posts into sections
        $hero_posts = array_slice($posts, 0, $heroCount);
        $grid_posts = array_slice($posts, $heroCount, $gridCount);
        $list_posts = array_slice($posts, $heroCount + $gridCount);

        // Format posts for response
        $format_post = function($post) {
            return [
                'id' => $post->ID,
                'title' => get_the_title($post->ID),
                'excerpt' => get_the_excerpt($post->ID),
                'date' => get_the_date('', $post->ID),
                'url' => get_permalink($post->ID),
                'featured_image' => get_the_post_thumbnail_url($post->ID, 'large'),
                'byline' => get_post_meta($post->ID, '_news_byline', true),
                'featured' => get_post_meta($post->ID, '_news_featured', true),
                'breaking' => get_post_meta($post->ID, '_news_breaking', true),
            ];
        };

        $response_data = [
            'hero' => array_map($format_post, $hero_posts),
            'grid' => array_map($format_post, $grid_posts),
            'list' => array_map($format_post, $list_posts),
            'total' => $query->found_posts,
        ];

        wp_reset_postdata();

        return new WP_REST_Response($response_data, 200);
    }

    /**
     * Get API documentation
     */
    public function getApiDocumentation(): array
    {
        return [
            'namespace' => self::NAMESPACE,
            'base_url' => $this->getApiBaseUrl(),
            'endpoints' => [
                'articles' => [
                    'GET /articles' => 'Get articles list',
                    'GET /articles/{id}' => 'Get single article',
                ],
                'sections' => [
                    'GET /sections' => 'Get sections list',
                    'GET /sections/{id}' => 'Get single section',
                ],
                'search' => [
                    'GET /search' => 'Search articles',
                ],
                'analytics' => [
                    'GET /analytics' => 'Get analytics data',
                ],
                'layout' => [
                    'GET /article-layout' => 'Get article layout for post template',
                ],
            ],
        ];
    }
}
