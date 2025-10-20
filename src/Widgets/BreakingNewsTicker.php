<?php
/**
 * Breaking News Ticker Widget
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Breaking news ticker widget for displaying urgent news
 */
class BreakingNewsTicker extends \WP_Widget {
    
    /**
     * Initialize the widget
     */
    public function __construct() {
        parent::__construct(
            'news_breaking_ticker',
            __('Breaking News Ticker', 'news'),
            [
                'description' => __('Display breaking news in a scrolling ticker', 'news'),
                'classname' => 'news-breaking-ticker-widget',
            ]
        );
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Enqueue widget scripts
     */
    public function enqueue_scripts(): void {
        if (is_active_widget(false, false, $this->id_base)) {
            wp_enqueue_script(
                'news-ticker',
                NEWS_PLUGIN_URL . 'src/Assets/js/ticker.js',
                ['jquery'],
                NEWS_PLUGIN_VERSION,
                true
            );
            
            wp_enqueue_style(
                'news-ticker',
                NEWS_PLUGIN_URL . 'src/Assets/css/ticker.css',
                [],
                NEWS_PLUGIN_VERSION
            );
        }
    }
    
    /**
     * Display the widget
     *
     * @param array $args Widget arguments
     * @param array $instance Widget instance
     */
    public function widget($args, $instance): void {
        $title = apply_filters('widget_title', $instance['title'] ?? '');
        $speed = absint($instance['speed'] ?? 50);
        $direction = $instance['direction'] ?? 'left';
        $show_breaking_only = (bool) ($instance['breaking_only'] ?? true);
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        $ticker_data = $this->get_ticker_data($show_breaking_only);
        
        if (!empty($ticker_data)) {
            $this->render_ticker($ticker_data, $speed, $direction);
        } else {
            echo '<p class="news-ticker-empty">' . __('No breaking news at this time', 'news') . '</p>';
        }
        
        echo $args['after_widget'];
    }
    
    /**
     * Get ticker data
     *
     * @param bool $breaking_only Show only breaking news
     * @return array
     */
    private function get_ticker_data(bool $breaking_only): array {
        $query_args = [
            'post_type' => 'news',
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        if ($breaking_only) {
            $query_args['meta_query'] = [
                [
                    'key' => 'is_breaking',
                    'value' => true,
                    'compare' => '=',
                ],
            ];
        }
        
        $query = new \WP_Query($query_args);
        $ticker_items = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $ticker_items[] = [
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'excerpt' => get_the_excerpt(),
                    'date' => get_the_date(),
                    'is_breaking' => get_post_meta(get_the_ID(), 'is_breaking', true),
                    'is_exclusive' => get_post_meta(get_the_ID(), 'is_exclusive', true),
                ];
            }
            wp_reset_postdata();
        }
        
        return apply_filters('news_ticker_data', $ticker_items, $breaking_only);
    }
    
    /**
     * Render ticker HTML
     *
     * @param array $items Ticker items
     * @param int $speed Animation speed
     * @param string $direction Animation direction
     */
    private function render_ticker(array $items, int $speed, string $direction): void {
        $ticker_id = 'news-ticker-' . uniqid();
        ?>
        <div class="news-ticker-container" id="<?php echo esc_attr($ticker_id); ?>">
            <div class="news-ticker-wrapper" 
                 data-speed="<?php echo esc_attr($speed); ?>"
                 data-direction="<?php echo esc_attr($direction); ?>">
                <div class="news-ticker-content">
                    <?php foreach ($items as $item): ?>
                        <div class="news-ticker-item">
                            <?php if ($item['is_breaking']): ?>
                                <span class="news-ticker-breaking"><?php _e('BREAKING', 'news'); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($item['is_exclusive']): ?>
                                <span class="news-ticker-exclusive"><?php _e('EXCLUSIVE', 'news'); ?></span>
                            <?php endif; ?>
                            
                            <a href="<?php echo esc_url($item['url']); ?>" class="news-ticker-link">
                                <?php echo esc_html($item['title']); ?>
                            </a>
                            
                            <span class="news-ticker-time">
                                <?php echo esc_html($item['date']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#<?php echo esc_js($ticker_id); ?>').newsTicker({
                speed: <?php echo esc_js($speed); ?>,
                direction: '<?php echo esc_js($direction); ?>'
            });
        });
        </script>
        <?php
    }
    
    /**
     * Widget form
     *
     * @param array $instance Widget instance
     */
    public function form($instance): void {
        $title = $instance['title'] ?? __('Breaking News', 'news');
        $speed = absint($instance['speed'] ?? 50);
        $direction = $instance['direction'] ?? 'left';
        $breaking_only = (bool) ($instance['breaking_only'] ?? true);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php _e('Title:', 'news'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('speed'); ?>">
                <?php _e('Speed (ms):', 'news'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('speed'); ?>" 
                   name="<?php echo $this->get_field_name('speed'); ?>" 
                   type="number" 
                   min="10" 
                   max="200" 
                   value="<?php echo esc_attr($speed); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('direction'); ?>">
                <?php _e('Direction:', 'news'); ?>
            </label>
            <select class="widefat" 
                    id="<?php echo $this->get_field_id('direction'); ?>" 
                    name="<?php echo $this->get_field_name('direction'); ?>">
                <option value="left" <?php selected($direction, 'left'); ?>><?php _e('Left to Right', 'news'); ?></option>
                <option value="right" <?php selected($direction, 'right'); ?>><?php _e('Right to Left', 'news'); ?></option>
            </select>
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   id="<?php echo $this->get_field_id('breaking_only'); ?>" 
                   name="<?php echo $this->get_field_name('breaking_only'); ?>" 
                   value="1" 
                   <?php checked($breaking_only); ?>>
            <label for="<?php echo $this->get_field_id('breaking_only'); ?>">
                <?php _e('Show only breaking news', 'news'); ?>
            </label>
        </p>
        <?php
    }
    
    /**
     * Update widget instance
     *
     * @param array $new_instance New instance
     * @param array $old_instance Old instance
     * @return array
     */
    public function update($new_instance, $old_instance): array {
        $instance = [];
        $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
        $instance['speed'] = absint($new_instance['speed'] ?? 50);
        $instance['direction'] = in_array($new_instance['direction'] ?? 'left', ['left', 'right']) 
            ? $new_instance['direction'] 
            : 'left';
        $instance['breaking_only'] = (bool) ($new_instance['breaking_only'] ?? false);
        
        return $instance;
    }
}
