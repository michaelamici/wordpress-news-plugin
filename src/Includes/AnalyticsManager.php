<?php
/**
 * Analytics Manager for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles analytics tracking and reporting
 */
class AnalyticsManager {
    
    /**
     * Initialize analytics manager
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_analytics_scripts']);
        add_action('wp_ajax_news_track_event', [$this, 'handle_analytics_event']);
        add_action('wp_ajax_nopriv_news_track_event', [$this, 'handle_analytics_event']);
        add_action('wp_head', [$this, 'add_analytics_meta']);
    }
    
    /**
     * Enqueue analytics scripts
     */
    public function enqueue_analytics_scripts(): void {
        if (is_singular('news') || is_post_type_archive('news')) {
            wp_enqueue_script(
                'news-analytics',
                NEWS_PLUGIN_URL . 'src/Assets/js/analytics.js',
                ['jquery'],
                NEWS_PLUGIN_VERSION,
                true
            );
            
            wp_localize_script('news-analytics', 'newsAnalytics', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('news_analytics'),
                'postId' => get_the_ID(),
                'userId' => get_current_user_id(),
            ]);
        }
    }
    
    /**
     * Handle analytics event tracking
     */
    public function handle_analytics_event(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'news_analytics')) {
            wp_die(__('Security check failed', 'news'));
        }
        
        $event_type = sanitize_text_field($_POST['event_type'] ?? '');
        $event_data = $_POST['event_data'] ?? [];
        
        if (empty($event_type)) {
            wp_send_json_error(__('Event type required', 'news'));
        }
        
        $analytics_data = [
            'event_type' => $event_type,
            'event_data' => $this->sanitize_event_data($event_data),
            'user_id' => get_current_user_id(),
            'post_id' => absint($_POST['post_id'] ?? 0),
            'timestamp' => current_time('timestamp'),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'referrer' => sanitize_url($_SERVER['HTTP_REFERER'] ?? ''),
        ];
        
        $this->store_analytics_event($analytics_data);
        
        wp_send_json_success([
            'message' => __('Event tracked successfully', 'news'),
        ]);
    }
    
    /**
     * Sanitize event data
     *
     * @param array $data Event data
     * @return array
     */
    private function sanitize_event_data(array $data): array {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $sanitized[sanitize_key($key)] = is_array($value) 
                ? $this->sanitize_event_data($value)
                : sanitize_text_field($value);
        }
        
        return $sanitized;
    }
    
    /**
     * Store analytics event
     *
     * @param array $data Analytics data
     */
    private function store_analytics_event(array $data): void {
        // Store in custom table or options
        $events = get_option('news_analytics_events', []);
        $events[] = $data;
        
        // Keep only last 1000 events to prevent bloat
        if (count($events) > 1000) {
            $events = array_slice($events, -1000);
        }
        
        update_option('news_analytics_events', $events);
        
        // Update aggregated stats
        $this->update_aggregated_stats($data);
    }
    
    /**
     * Update aggregated statistics
     *
     * @param array $data Analytics data
     */
    private function update_aggregated_stats(array $data): void {
        $stats = get_option('news_analytics_stats', [
            'total_events' => 0,
            'page_views' => 0,
            'article_views' => 0,
            'placement_clicks' => 0,
            'breaking_news_views' => 0,
            'last_updated' => current_time('timestamp'),
        ]);
        
        $stats['total_events']++;
        $stats['last_updated'] = current_time('timestamp');
        
        switch ($data['event_type']) {
            case 'page_view':
                $stats['page_views']++;
                break;
                
            case 'article_view':
                $stats['article_views']++;
                break;
                
            case 'placement_click':
                $stats['placement_clicks']++;
                break;
                
            case 'breaking_news_view':
                $stats['breaking_news_views']++;
                break;
        }
        
        update_option('news_analytics_stats', $stats);
    }
    
    /**
     * Get analytics dashboard data
     *
     * @return array
     */
    public static function get_dashboard_data(): array {
        $stats = get_option('news_analytics_stats', []);
        $events = get_option('news_analytics_events', []);
        
        // Get recent events (last 24 hours)
        $recent_events = array_filter($events, function($event) {
            return $event['timestamp'] > (current_time('timestamp') - DAY_IN_SECONDS);
        });
        
        // Get popular articles
        $popular_articles = self::get_popular_articles();
        
        // Get placement performance
        $placement_performance = self::get_placement_performance();
        
        return [
            'stats' => $stats,
            'recent_events' => count($recent_events),
            'popular_articles' => $popular_articles,
            'placement_performance' => $placement_performance,
            'last_updated' => $stats['last_updated'] ?? 0,
        ];
    }
    
    /**
     * Get popular articles
     *
     * @param int $limit Number of articles to return
     * @return array
     */
    public static function get_popular_articles(int $limit = 10): array {
        $events = get_option('news_analytics_events', []);
        $article_views = [];
        
        foreach ($events as $event) {
            if ($event['event_type'] === 'article_view' && !empty($event['post_id'])) {
                $post_id = $event['post_id'];
                $article_views[$post_id] = ($article_views[$post_id] ?? 0) + 1;
            }
        }
        
        arsort($article_views);
        
        $popular = [];
        $count = 0;
        
        foreach ($article_views as $post_id => $views) {
            if ($count >= $limit) {
                break;
            }
            
            $post = get_post($post_id);
            if ($post && $post->post_type === 'news') {
                $popular[] = [
                    'post_id' => $post_id,
                    'title' => $post->post_title,
                    'views' => $views,
                    'url' => get_permalink($post_id),
                ];
            }
            
            $count++;
        }
        
        return $popular;
    }
    
    /**
     * Get placement performance
     *
     * @return array
     */
    public static function get_placement_performance(): array {
        $events = get_option('news_analytics_events', []);
        $placement_stats = [];
        
        foreach ($events as $event) {
            if ($event['event_type'] === 'placement_click' && !empty($event['event_data']['placement_id'])) {
                $placement_id = $event['event_data']['placement_id'];
                if (!isset($placement_stats[$placement_id])) {
                    $placement_stats[$placement_id] = [
                        'clicks' => 0,
                        'impressions' => 0,
                    ];
                }
                $placement_stats[$placement_id]['clicks']++;
            }
            
            if ($event['event_type'] === 'placement_impression' && !empty($event['event_data']['placement_id'])) {
                $placement_id = $event['event_data']['placement_id'];
                if (!isset($placement_stats[$placement_id])) {
                    $placement_stats[$placement_id] = [
                        'clicks' => 0,
                        'impressions' => 0,
                    ];
                }
                $placement_stats[$placement_id]['impressions']++;
            }
        }
        
        // Calculate click-through rates
        foreach ($placement_stats as $placement_id => &$stats) {
            $stats['ctr'] = $stats['impressions'] > 0 
                ? round(($stats['clicks'] / $stats['impressions']) * 100, 2)
                : 0;
        }
        
        return $placement_stats;
    }
    
    /**
     * Add analytics meta tags
     */
    public function add_analytics_meta(): void {
        if (is_singular('news')) {
            $post_id = get_the_ID();
            $analytics_id = 'news-' . $post_id . '-' . current_time('Ymd');
            
            echo '<meta name="news-analytics-id" content="' . esc_attr($analytics_id) . '">' . "\n";
            echo '<meta name="news-post-type" content="news">' . "\n";
            echo '<meta name="news-section" content="' . esc_attr($this->get_primary_section($post_id)) . '">' . "\n";
        }
    }
    
    /**
     * Get primary section for post
     *
     * @param int $post_id Post ID
     * @return string
     */
    private function get_primary_section(int $post_id): string {
        $sections = wp_get_post_terms($post_id, 'news_section');
        
        if (!empty($sections) && !is_wp_error($sections)) {
            return $sections[0]->slug;
        }
        
        return 'uncategorized';
    }
    
    /**
     * Get client IP address
     *
     * @return string
     */
    private function get_client_ip(): string {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
