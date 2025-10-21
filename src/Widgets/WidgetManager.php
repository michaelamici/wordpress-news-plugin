<?php

declare(strict_types=1);

namespace NewsPlugin\Widgets;

use NewsPlugin\Core\Plugin;
use NewsPlugin\Assets\AssetManager;

/**
 * Widget Manager
 * 
 * Handles WordPress widget registration and management
 */
class WidgetManager
{
    /**
     * Plugin instance
     */
    private Plugin $plugin;

    /**
     * Asset manager
     */
    private AssetManager $assets;

    /**
     * Registered widgets
     */
    private array $widgets = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin = Plugin::instance();
        $this->assets = $this->plugin->getAssetManager();
        
        $this->init();
    }

    /**
     * Initialize widget manager
     */
    private function init(): void
    {
        // Add widget hooks
        add_action('widgets_init', [$this, 'registerWidgets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueWidgetAssets']);
    }

    /**
     * Register all widgets
     */
    public function registerWidgets(): void
    {
        $this->registerNewsArticlesWidget();
        $this->registerNewsSectionsWidget();
        $this->registerBreakingNewsWidget();
        $this->registerNewsSearchWidget();
    }

    /**
     * Register news articles widget
     */
    private function registerNewsArticlesWidget(): void
    {
        register_widget(NewsArticlesWidget::class);
        $this->widgets['news-articles'] = NewsArticlesWidget::class;
    }

    /**
     * Register news sections widget
     */
    private function registerNewsSectionsWidget(): void
    {
        register_widget(NewsSectionsWidget::class);
        $this->widgets['news-sections'] = NewsSectionsWidget::class;
    }

    /**
     * Register breaking news widget
     */
    private function registerBreakingNewsWidget(): void
    {
        register_widget(BreakingNewsWidget::class);
        $this->widgets['breaking-news'] = BreakingNewsWidget::class;
    }

    /**
     * Register news search widget
     */
    private function registerNewsSearchWidget(): void
    {
        register_widget(NewsSearchWidget::class);
        $this->widgets['news-search'] = NewsSearchWidget::class;
    }

    /**
     * Enqueue widget assets
     */
    public function enqueueWidgetAssets(): void
    {
        $this->assets->enqueueStyle('news-widgets', 'css/widgets.css');
    }

    /**
     * Get registered widgets
     */
    public function getWidgets(): array
    {
        return $this->widgets;
    }

    /**
     * Get widget by name
     */
    public function getWidget(string $name): ?string
    {
        return $this->widgets[$name] ?? null;
    }
}

// Only define widget classes if WP_Widget is available (i.e., in WordPress context)
if (class_exists('WP_Widget')) {
    /**
     * News Articles Widget
     */
    class NewsArticlesWidget extends \WP_Widget
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            parent::__construct(
                'news_articles',
                __('News Articles', 'news'),
                [
                    'description' => __('Display recent news articles', 'news'),
                    'classname' => 'news-articles-widget',
                ]
            );
        }

        /**
         * Widget output
         */
        public function widget($args, $instance): void
        {
            $title = apply_filters('widget_title', $instance['title'] ?? '');
            $count = (int) ($instance['count'] ?? 5);
            $section = $instance['section'] ?? '';
            $show_excerpt = (bool) ($instance['show_excerpt'] ?? true);
            $show_meta = (bool) ($instance['show_meta'] ?? true);

            echo $args['before_widget'];

            if (!empty($title)) {
                echo $args['before_title'] . esc_html($title) . $args['after_title'];
            }

            $query_args = [
                'post_type' => 'news',
                'posts_per_page' => $count,
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

            $query = new \WP_Query($query_args);

            if ($query->have_posts()) {
                echo '<ul class="news-articles-list">';
                
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<li class="news-article-item">';
                    echo '<h4><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h4>';
                    
                    if ($show_excerpt) {
                        echo '<div class="news-article-excerpt">' . wp_kses_post(get_the_excerpt()) . '</div>';
                    }
                    
                    if ($show_meta) {
                        echo '<div class="news-article-meta">';
                        echo '<span class="news-article-date">' . get_the_date() . '</span>';
                        echo '<span class="news-article-author">' . get_the_author() . '</span>';
                        echo '</div>';
                    }
                    
                    echo '</li>';
                }
                
                echo '</ul>';
            } else {
                echo '<p>' . esc_html__('No articles found.', 'news') . '</p>';
            }

            wp_reset_postdata();
            echo $args['after_widget'];
        }

        /**
         * Widget form
         */
        public function form($instance): void
        {
            $title = $instance['title'] ?? '';
            $count = $instance['count'] ?? 5;
            $section = $instance['section'] ?? '';
            $show_excerpt = $instance['show_excerpt'] ?? true;
            $show_meta = $instance['show_meta'] ?? true;

            echo '<p>';
            echo '<label for="' . $this->get_field_id('title') . '">' . __('Title:', 'news') . '</label>';
            echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '">';
            echo '</p>';

            echo '<p>';
            echo '<label for="' . $this->get_field_id('count') . '">' . __('Number of articles:', 'news') . '</label>';
            echo '<input class="tiny-text" id="' . $this->get_field_id('count') . '" name="' . $this->get_field_name('count') . '" type="number" value="' . esc_attr($count) . '" min="1" max="20">';
            echo '</p>';

            echo '<p>';
            echo '<label for="' . $this->get_field_id('section') . '">' . __('Section (optional):', 'news') . '</label>';
            echo '<select class="widefat" id="' . $this->get_field_id('section') . '" name="' . $this->get_field_name('section') . '">';
            echo '<option value="">' . __('All Sections', 'news') . '</option>';
            
            $sections = get_terms([
                'taxonomy' => 'news_section',
                'hide_empty' => false,
            ]);
            
            foreach ($sections as $section_term) {
                $selected = selected($section, $section_term->slug, false);
                echo '<option value="' . esc_attr($section_term->slug) . '"' . $selected . '>' . esc_html($section_term->name) . '</option>';
            }
            
            echo '</select>';
            echo '</p>';

            echo '<p>';
            echo '<input class="checkbox" type="checkbox" id="' . $this->get_field_id('show_excerpt') . '" name="' . $this->get_field_name('show_excerpt') . '"' . checked($show_excerpt, true, false) . '>';
            echo '<label for="' . $this->get_field_id('show_excerpt') . '">' . __('Show excerpt', 'news') . '</label>';
            echo '</p>';

            echo '<p>';
            echo '<input class="checkbox" type="checkbox" id="' . $this->get_field_id('show_meta') . '" name="' . $this->get_field_name('show_meta') . '"' . checked($show_meta, true, false) . '>';
            echo '<label for="' . $this->get_field_id('show_meta') . '">' . __('Show meta information', 'news') . '</label>';
            echo '</p>';
        }

        /**
         * Update widget
         */
        public function update($new_instance, $old_instance): array
        {
            $instance = [];
            $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
            $instance['count'] = (int) ($new_instance['count'] ?? 5);
            $instance['section'] = sanitize_text_field($new_instance['section'] ?? '');
            $instance['show_excerpt'] = (bool) ($new_instance['show_excerpt'] ?? true);
            $instance['show_meta'] = (bool) ($new_instance['show_meta'] ?? true);
            
            return $instance;
        }
    }

    /**
     * News Sections Widget
     */
    class NewsSectionsWidget extends \WP_Widget
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            parent::__construct(
                'news_sections',
                __('News Sections', 'news'),
                [
                    'description' => __('Display news sections', 'news'),
                    'classname' => 'news-sections-widget',
                ]
            );
        }

        /**
         * Widget output
         */
        public function widget($args, $instance): void
        {
            $title = apply_filters('widget_title', $instance['title'] ?? '');
            $show_count = (bool) ($instance['show_count'] ?? true);
            $parent = (int) ($instance['parent'] ?? 0);

            echo $args['before_widget'];

            if (!empty($title)) {
                echo $args['before_title'] . esc_html($title) . $args['after_title'];
            }

            $sections = get_terms([
                'taxonomy' => 'news_section',
                'parent' => $parent,
                'hide_empty' => false,
            ]);

            if (!empty($sections) && !is_wp_error($sections)) {
                echo '<ul class="news-sections-list">';
                
                foreach ($sections as $section) {
                    echo '<li class="news-section-item">';
                    echo '<a href="' . esc_url(get_term_link($section)) . '">' . esc_html($section->name) . '</a>';
                    
                    if ($show_count) {
                        echo ' <span class="news-section-count">(' . $section->count . ')</span>';
                    }
                    
                    echo '</li>';
                }
                
                echo '</ul>';
            } else {
                echo '<p>' . esc_html__('No sections found.', 'news') . '</p>';
            }

            echo $args['after_widget'];
        }

        /**
         * Widget form
         */
        public function form($instance): void
        {
            $title = $instance['title'] ?? '';
            $show_count = $instance['show_count'] ?? true;
            $parent = $instance['parent'] ?? 0;

            echo '<p>';
            echo '<label for="' . $this->get_field_id('title') . '">' . __('Title:', 'news') . '</label>';
            echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '">';
            echo '</p>';

            echo '<p>';
            echo '<input class="checkbox" type="checkbox" id="' . $this->get_field_id('show_count') . '" name="' . $this->get_field_name('show_count') . '"' . checked($show_count, true, false) . '>';
            echo '<label for="' . $this->get_field_id('show_count') . '">' . __('Show article count', 'news') . '</label>';
            echo '</p>';

            echo '<p>';
            echo '<label for="' . $this->get_field_id('parent') . '">' . __('Parent section:', 'news') . '</label>';
            echo '<select class="widefat" id="' . $this->get_field_id('parent') . '" name="' . $this->get_field_name('parent') . '">';
            echo '<option value="0">' . __('All Sections', 'news') . '</option>';
            
            $parent_sections = get_terms([
                'taxonomy' => 'news_section',
                'parent' => 0,
                'hide_empty' => false,
            ]);
            
            foreach ($parent_sections as $parent_section) {
                $selected = selected($parent, $parent_section->term_id, false);
                echo '<option value="' . esc_attr($parent_section->term_id) . '"' . $selected . '>' . esc_html($parent_section->name) . '</option>';
            }
            
            echo '</select>';
            echo '</p>';
        }

        /**
         * Update widget
         */
        public function update($new_instance, $old_instance): array
        {
            $instance = [];
            $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
            $instance['show_count'] = (bool) ($new_instance['show_count'] ?? true);
            $instance['parent'] = (int) ($new_instance['parent'] ?? 0);
            
            return $instance;
        }
    }

    /**
     * Breaking News Widget
     */
    class BreakingNewsWidget extends \WP_Widget
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            parent::__construct(
                'breaking_news',
                __('Breaking News', 'news'),
                [
                    'description' => __('Display breaking news ticker', 'news'),
                    'classname' => 'breaking-news-widget',
                ]
            );
        }

        /**
         * Widget output
         */
        public function widget($args, $instance): void
        {
            $title = apply_filters('widget_title', $instance['title'] ?? '');
            $count = (int) ($instance['count'] ?? 3);
            $scroll = (bool) ($instance['scroll'] ?? true);

            echo $args['before_widget'];

            if (!empty($title)) {
                echo $args['before_title'] . esc_html($title) . $args['after_title'];
            }

            $query = new \WP_Query([
                'post_type' => 'news',
                'posts_per_page' => $count,
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key' => '_news_article_meta',
                        'value' => 'breaking',
                        'compare' => 'LIKE',
                    ],
                ],
            ]);

            if ($query->have_posts()) {
                echo '<div class="breaking-news-content' . ($scroll ? ' breaking-news-scroll' : '') . '">';
                
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<div class="breaking-news-item">';
                    echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
                    echo '</div>';
                }
                
                echo '</div>';
            } else {
                echo '<p>' . esc_html__('No breaking news found.', 'news') . '</p>';
            }

            wp_reset_postdata();
            echo $args['after_widget'];
        }

        /**
         * Widget form
         */
        public function form($instance): void
        {
            $title = $instance['title'] ?? '';
            $count = $instance['count'] ?? 3;
            $scroll = $instance['scroll'] ?? true;

            echo '<p>';
            echo '<label for="' . $this->get_field_id('title') . '">' . __('Title:', 'news') . '</label>';
            echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '">';
            echo '</p>';

            echo '<p>';
            echo '<label for="' . $this->get_field_id('count') . '">' . __('Number of articles:', 'news') . '</label>';
            echo '<input class="tiny-text" id="' . $this->get_field_id('count') . '" name="' . $this->get_field_name('count') . '" type="number" value="' . esc_attr($count) . '" min="1" max="10">';
            echo '</p>';

            echo '<p>';
            echo '<input class="checkbox" type="checkbox" id="' . $this->get_field_id('scroll') . '" name="' . $this->get_field_name('scroll') . '"' . checked($scroll, true, false) . '>';
            echo '<label for="' . $this->get_field_id('scroll') . '">' . __('Enable scrolling', 'news') . '</label>';
            echo '</p>';
        }

        /**
         * Update widget
         */
        public function update($new_instance, $old_instance): array
        {
            $instance = [];
            $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
            $instance['count'] = (int) ($new_instance['count'] ?? 3);
            $instance['scroll'] = (bool) ($new_instance['scroll'] ?? true);
            
            return $instance;
        }
    }

    /**
     * News Search Widget
     */
    class NewsSearchWidget extends \WP_Widget
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            parent::__construct(
                'news_search',
                __('News Search', 'news'),
                [
                    'description' => __('Search news articles', 'news'),
                    'classname' => 'news-search-widget',
                ]
            );
        }

        /**
         * Widget output
         */
        public function widget($args, $instance): void
        {
            $title = apply_filters('widget_title', $instance['title'] ?? '');

            echo $args['before_widget'];

            if (!empty($title)) {
                echo $args['before_title'] . esc_html($title) . $args['after_title'];
            }

            echo '<form role="search" method="get" class="news-search-form" action="' . esc_url(home_url('/')) . '">';
            echo '<input type="hidden" name="post_type" value="news">';
            echo '<input type="search" class="search-field" placeholder="' . esc_attr__('Search news...', 'news') . '" value="' . get_search_query() . '" name="s">';
            echo '<button type="submit" class="search-submit">' . esc_html__('Search', 'news') . '</button>';
            echo '</form>';

            echo $args['after_widget'];
        }

        /**
         * Widget form
         */
        public function form($instance): void
        {
            $title = $instance['title'] ?? '';

            echo '<p>';
            echo '<label for="' . $this->get_field_id('title') . '">' . __('Title:', 'news') . '</label>';
            echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '">';
            echo '</p>';
        }

        /**
         * Update widget
         */
        public function update($new_instance, $old_instance): array
        {
            $instance = [];
            $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
            
            return $instance;
        }
    }
}
