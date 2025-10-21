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
            ],
        ];
    }
}
