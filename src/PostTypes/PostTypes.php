<?php

declare(strict_types=1);

namespace NewsPlugin\PostTypes;

use NewsPlugin\Core\Plugin;
use NewsPlugin\Security\SecurityManager;

/**
 * Post Types Manager
 * 
 * Handles custom post types and taxonomies registration
 */
class PostTypes
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
     * Constructor
     */
    public function __construct()
    {
        $this->plugin = Plugin::instance();
        $this->security = $this->plugin->getSecurityManager();
        
        $this->init();
    }

    /**
     * Initialize post types
     */
    private function init(): void
    {
        add_action('init', [$this, 'registerPostTypes']);
        add_action('init', [$this, 'registerTaxonomies']);
        add_action('init', [$this, 'registerMetaFields']);
        
        // Add custom capabilities
        add_action('init', [$this, 'addCapabilities']);
        
        // Add rewrite rules
        add_action('init', [$this, 'addRewriteRules']);
        add_filter('post_type_link', [$this, 'customPostTypeLink'], 10, 2);
    }

    /**
     * Register custom post types
     */
    public function registerPostTypes(): void
    {
        $this->registerNewsPostType();
    }

    /**
     * Register news post type
     */
    private function registerNewsPostType(): void
    {
        $labels = [
            'name' => _x('News Articles', 'Post type general name', 'news'),
            'singular_name' => _x('News Article', 'Post type singular name', 'news'),
            'menu_name' => _x('News Articles', 'Admin Menu text', 'news'),
            'name_admin_bar' => _x('News Article', 'Add New on Toolbar', 'news'),
            'add_new' => __('Add New', 'news'),
            'add_new_item' => __('Add New News Article', 'news'),
            'new_item' => __('New News Article', 'news'),
            'edit_item' => __('Edit News Article', 'news'),
            'view_item' => __('View News Article', 'news'),
            'all_items' => __('All News Articles', 'news'),
            'search_items' => __('Search News Articles', 'news'),
            'parent_item_colon' => __('Parent News Articles:', 'news'),
            'not_found' => __('No news articles found.', 'news'),
            'not_found_in_trash' => __('No news articles found in Trash.', 'news'),
            'featured_image' => _x('Featured Image', 'Overrides the "Featured Image" phrase', 'news'),
            'set_featured_image' => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'news'),
            'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'news'),
            'use_featured_image' => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'news'),
            'archives' => _x('News Archives', 'The post type archive label', 'news'),
            'insert_into_item' => _x('Insert into news article', 'Overrides the "Insert into post" phrase', 'news'),
            'uploaded_to_this_item' => _x('Uploaded to this news article', 'Overrides the "Uploaded to this post" phrase', 'news'),
            'filter_items_list' => _x('Filter news articles list', 'Screen reader text for the filter links', 'news'),
            'items_list_navigation' => _x('News articles list navigation', 'Screen reader text for the pagination', 'news'),
            'items_list' => _x('News articles list', 'Screen reader text for the items list', 'news'),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'news',
                'with_front' => false,
            ],
            'capability_type' => 'news',
            'capabilities' => [
                'edit_post' => 'edit_news',
                'read_post' => 'read_news',
                'delete_post' => 'delete_news',
                'edit_posts' => 'edit_news',
                'edit_others_posts' => 'edit_others_news',
                'publish_posts' => 'publish_news',
                'read_private_posts' => 'read_private_news',
                'delete_posts' => 'delete_news',
                'delete_private_posts' => 'delete_private_news',
                'delete_published_posts' => 'delete_published_news',
                'delete_others_posts' => 'delete_others_news',
                'edit_private_posts' => 'edit_private_news',
                'edit_published_posts' => 'edit_published_news',
            ],
            'map_meta_cap' => true,
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-megaphone',
            'supports' => [
                'title',
                'editor',
                'excerpt',
                'author',
                'thumbnail',
                'comments',
                'revisions',
                'custom-fields',
                'page-attributes',
            ],
            'taxonomies' => ['news_section', 'news_beat'],
        ];

        register_post_type('news', $args);
    }

    /**
     * Register taxonomies
     */
    public function registerTaxonomies(): void
    {
        $this->registerNewsSectionTaxonomy();
        $this->registerNewsBeatTaxonomy();
    }

    /**
     * Register news section taxonomy
     */
    private function registerNewsSectionTaxonomy(): void
    {
        $labels = [
            'name' => _x('News Sections', 'Taxonomy general name', 'news'),
            'singular_name' => _x('News Section', 'Taxonomy singular name', 'news'),
            'menu_name' => _x('Sections', 'Admin Menu text', 'news'),
            'all_items' => __('All Sections', 'news'),
            'parent_item' => __('Parent Section', 'news'),
            'parent_item_colon' => __('Parent Section:', 'news'),
            'new_item_name' => __('New Section Name', 'news'),
            'add_new_item' => __('Add New Section', 'news'),
            'edit_item' => __('Edit Section', 'news'),
            'update_item' => __('Update Section', 'news'),
            'view_item' => __('View Section', 'news'),
            'separate_items_with_commas' => __('Separate sections with commas', 'news'),
            'add_or_remove_items' => __('Add or remove sections', 'news'),
            'choose_from_most_used' => __('Choose from the most used', 'news'),
            'popular_items' => __('Popular Sections', 'news'),
            'search_items' => __('Search Sections', 'news'),
            'not_found' => __('Not Found', 'news'),
            'no_terms' => __('No sections', 'news'),
            'items_list' => __('Sections list', 'news'),
            'items_list_navigation' => __('Sections list navigation', 'news'),
        ];

        $args = [
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => false,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'news-section',
                'with_front' => false,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_news',
                'edit_terms' => 'manage_news',
                'delete_terms' => 'manage_news',
                'assign_terms' => 'edit_news',
            ],
        ];

        register_taxonomy('news_section', ['news'], $args);
    }

    /**
     * Register news beat taxonomy
     */
    private function registerNewsBeatTaxonomy(): void
    {
        $labels = [
            'name' => _x('News Beats', 'Taxonomy general name', 'news'),
            'singular_name' => _x('News Beat', 'Taxonomy singular name', 'news'),
            'menu_name' => _x('Beats', 'Admin Menu text', 'news'),
            'all_items' => __('All Beats', 'news'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'new_item_name' => __('New Beat Name', 'news'),
            'add_new_item' => __('Add New Beat', 'news'),
            'edit_item' => __('Edit Beat', 'news'),
            'update_item' => __('Update Beat', 'news'),
            'view_item' => __('View Beat', 'news'),
            'separate_items_with_commas' => __('Separate beats with commas', 'news'),
            'add_or_remove_items' => __('Add or remove beats', 'news'),
            'choose_from_most_used' => __('Choose from the most used', 'news'),
            'popular_items' => __('Popular Beats', 'news'),
            'search_items' => __('Search Beats', 'news'),
            'not_found' => __('Not Found', 'news'),
            'no_terms' => __('No beats', 'news'),
            'items_list' => __('Beats list', 'news'),
            'items_list_navigation' => __('Beats list navigation', 'news'),
        ];

        $args = [
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'news-beat',
                'with_front' => false,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_news',
                'edit_terms' => 'manage_news',
                'delete_terms' => 'manage_news',
                'assign_terms' => 'edit_news',
            ],
        ];

        register_taxonomy('news_beat', ['news'], $args);
    }

    /**
     * Register meta fields
     */
    public function registerMetaFields(): void
    {
        // Register article meta fields
        register_post_meta('news', '_news_article_meta', [
            'type' => 'object',
            'description' => 'News article metadata',
            'single' => true,
            'show_in_rest' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'featured' => [
                            'type' => 'boolean',
                            'default' => false,
                        ],
                        'breaking' => [
                            'type' => 'boolean',
                            'default' => false,
                        ],
                        'exclusive' => [
                            'type' => 'boolean',
                            'default' => false,
                        ],
                        'sponsored' => [
                            'type' => 'boolean',
                            'default' => false,
                        ],
                    ],
                ],
            ],
        ]);

        // Register section meta fields
        register_term_meta('news_section', 'section_color', [
            'type' => 'string',
            'description' => 'Section color',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        register_term_meta('news_section', 'section_icon', [
            'type' => 'string',
            'description' => 'Section icon',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_term_meta('news_section', 'section_order', [
            'type' => 'integer',
            'description' => 'Section display order',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'absint',
        ]);
    }

    /**
     * Add custom capabilities
     */
    public function addCapabilities(): void
    {
        $admin_role = get_role('administrator');
        
        if ($admin_role) {
            $capabilities = [
                'manage_news',
                'edit_news',
                'read_news',
                'delete_news',
                'edit_others_news',
                'publish_news',
                'read_private_news',
                'delete_private_news',
                'delete_published_news',
                'delete_others_news',
                'edit_private_news',
                'edit_published_news',
            ];

            foreach ($capabilities as $cap) {
                $admin_role->add_cap($cap);
            }
        }
    }

    /**
     * Add rewrite rules
     */
    public function addRewriteRules(): void
    {
        // Add custom rewrite rules for news sections
        add_rewrite_rule(
            '^news/section/([^/]+)/?$',
            'index.php?news_section=$matches[1]',
            'top'
        );

        // Add custom rewrite rules for news beats
        add_rewrite_rule(
            '^news/beat/([^/]+)/?$',
            'index.php?news_beat=$matches[1]',
            'top'
        );
    }

    /**
     * Custom post type link
     */
    public function customPostTypeLink(string $post_link, $post): string
    {
        if ($post->post_type === 'news') {
            $sections = wp_get_post_terms($post->ID, 'news_section');
            
            if (!empty($sections) && !is_wp_error($sections)) {
                $section = $sections[0];
                $post_link = str_replace('/news/', '/news/' . $section->slug . '/', $post_link);
            }
        }

        return $post_link;
    }

    /**
     * Get post type capabilities
     */
    public function getPostTypeCapabilities(): array
    {
        return [
            'edit_post' => 'edit_news',
            'read_post' => 'read_news',
            'delete_post' => 'delete_news',
            'edit_posts' => 'edit_news',
            'edit_others_posts' => 'edit_others_news',
            'publish_posts' => 'publish_news',
            'read_private_posts' => 'read_private_news',
            'delete_posts' => 'delete_news',
            'delete_private_posts' => 'delete_private_news',
            'delete_published_posts' => 'delete_published_news',
            'delete_others_posts' => 'delete_others_news',
            'edit_private_posts' => 'edit_private_news',
            'edit_published_posts' => 'edit_published_news',
        ];
    }

    /**
     * Get taxonomy capabilities
     */
    public function getTaxonomyCapabilities(): array
    {
        return [
            'manage_terms' => 'manage_news',
            'edit_terms' => 'manage_news',
            'delete_terms' => 'manage_news',
            'assign_terms' => 'edit_news',
        ];
    }

    /**
     * Get registered post types
     */
    public function getRegisteredPostTypes(): array
    {
        return ['news'];
    }

    /**
     * Get registered taxonomies
     */
    public function getRegisteredTaxonomies(): array
    {
        return ['news_section', 'news_beat'];
    }
}
