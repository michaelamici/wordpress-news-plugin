<?php
/**
 * Smoke tests for critical user flows
 *
 * @package NewsPlugin
 */

class Test_Critical_Flows_Smoke extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
    }
    
    public function tearDown(): void {
        parent::tearDown();
        News_Test_Utils::cleanup_test_data();
    }
    
    /**
     * Test complete news article creation flow
     */
    public function test_news_article_creation_flow() {
        // Create section
        $section_id = News_Test_Utils::create_news_section([
            'name' => 'Politics',
            'slug' => 'politics',
        ]);
        
        $this->assertNotInstanceOf('WP_Error', $section_id);
        
        // Create article
        $post_id = News_Test_Utils::create_news_article([
            'post_title' => 'Breaking: Major Political Development',
            'post_content' => 'This is a breaking news story about a major political development.',
            'meta' => [
                'is_breaking' => true,
                'is_featured' => true,
                'dek' => 'Major political development breaks',
                'byline' => 'Political Reporter',
                'location' => 'Capitol Building',
            ],
        ]);
        
        $this->assertGreaterThan(0, $post_id);
        
        // Assign to section
        $result = wp_set_post_terms($post_id, [$section_id], 'news_section');
        $this->assertNotInstanceOf('WP_Error', $result);
        
        // Verify article exists
        $post = get_post($post_id);
        $this->assertEquals('news', $post->post_type);
        $this->assertEquals('Breaking: Major Political Development', $post->post_title);
        
        // Verify meta fields
        $this->assertTrue(get_post_meta($post_id, 'is_breaking', true));
        $this->assertTrue(get_post_meta($post_id, 'is_featured', true));
        $this->assertEquals('Major political development breaks', get_post_meta($post_id, 'dek', true));
        
        // Verify section assignment
        $terms = wp_get_post_terms($post_id, 'news_section');
        $this->assertCount(1, $terms);
        $this->assertEquals($section_id, $terms[0]->term_id);
        
        wp_delete_post($post_id, true);
    }
    
    /**
     * Test front page rendering flow
     */
    public function test_front_page_rendering_flow() {
        // Create test content
        $featured_post = News_Test_Utils::create_news_article([
            'meta' => ['is_featured' => true]
        ]);
        
        $breaking_post = News_Test_Utils::create_news_article([
            'meta' => ['is_breaking' => true]
        ]);
        
        $regular_post = News_Test_Utils::create_news_article();
        
        // Test home front
        $front_manager = new \NewsPlugin\Fronts\FrontManager();
        $home_front = $front_manager->get_front('home');
        
        $this->assertInstanceOf('NewsPlugin\Fronts\HomeFront', $home_front);
        
        // Test regions
        $regions = $home_front->get_regions();
        $this->assertArrayHasKey('hero', $regions);
        $this->assertArrayHasKey('rails', $regions);
        $this->assertArrayHasKey('sidebar', $regions);
        
        // Test template rendering
        ob_start();
        $home_front->render();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
        
        // Cleanup
        wp_delete_post($featured_post, true);
        wp_delete_post($breaking_post, true);
        wp_delete_post($regular_post, true);
    }
    
    /**
     * Test admin interface flow
     */
    public function test_admin_interface_flow() {
        $user_id = News_Test_Utils::create_test_user(['edit_news', 'manage_news_fronts']);
        wp_set_current_user($user_id);
        
        // Test admin menu registration
        $admin_menu = new \NewsPlugin\Admin\AdminMenu();
        $this->assertInstanceOf('NewsPlugin\Admin\AdminMenu', $admin_menu);
        
        // Test dashboard stats
        $reflection = new ReflectionClass($admin_menu);
        $method = $reflection->getMethod('get_news_stats');
        $method->setAccessible(true);
        
        $stats = $method->invoke($admin_menu);
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_articles', $stats);
        $this->assertArrayHasKey('published_articles', $stats);
        $this->assertArrayHasKey('total_sections', $stats);
        $this->assertArrayHasKey('breaking_articles', $stats);
        
        wp_delete_user($user_id);
    }
    
    /**
     * Test REST API flow
     */
    public function test_rest_api_flow() {
        // Create test content
        $post_id = News_Test_Utils::create_news_article([
            'meta' => ['is_featured' => true]
        ]);
        
        $section_id = News_Test_Utils::create_news_section([
            'name' => 'Test Section',
        ]);
        
        // Test news posts endpoint
        $request = new WP_REST_Request('GET', '/wp/v2/news');
        $response = rest_do_request($request);
        $this->assertEquals(200, $response->get_status());
        
        // Test news sections endpoint
        $request = new WP_REST_Request('GET', '/wp/v2/news_section');
        $response = rest_do_request($request);
        $this->assertEquals(200, $response->get_status());
        
        // Test front data endpoint
        $request = new WP_REST_Request('GET', '/news/v1/front/home');
        $response = rest_do_request($request);
        $this->assertEquals(200, $response->get_status());
        
        // Test breaking alerts endpoint
        $request = new WP_REST_Request('GET', '/news/v1/breaking');
        $response = rest_do_request($request);
        $this->assertEquals(200, $response->get_status());
        
        wp_delete_post($post_id, true);
    }
    
    /**
     * Test caching flow
     */
    public function test_caching_flow() {
        $cache_manager = new \NewsPlugin\Includes\CacheManager();
        
        // Test cache setting
        $cache_key = 'test_cache_key';
        $cache_data = ['test' => 'data'];
        
        $result = $cache_manager->set($cache_key, $cache_data, 3600);
        $this->assertTrue($result);
        
        // Test cache retrieval
        $retrieved_data = $cache_manager->get($cache_key);
        $this->assertEquals($cache_data, $retrieved_data);
        
        // Test cache deletion
        $result = $cache_manager->delete($cache_key);
        $this->assertTrue($result);
        
        // Test cache miss
        $retrieved_data = $cache_manager->get($cache_key);
        $this->assertFalse($retrieved_data);
    }
    
    /**
     * Test security flow
     */
    public function test_security_flow() {
        $security_manager = new \NewsPlugin\Includes\SecurityManager();
        
        // Test capability addition
        $admin_role = get_role('administrator');
        $this->assertTrue($admin_role->has_cap('edit_news'));
        $this->assertTrue($admin_role->has_cap('publish_news'));
        $this->assertTrue($admin_role->has_cap('manage_news_fronts'));
        
        // Test nonce verification
        $nonce = wp_create_nonce('news_breaking_alert');
        $this->assertTrue(wp_verify_nonce($nonce, 'news_breaking_alert'));
        
        // Test invalid nonce
        $this->assertFalse(wp_verify_nonce('invalid_nonce', 'news_breaking_alert'));
    }
    
    /**
     * Test performance flow
     */
    public function test_performance_flow() {
        $performance_optimizer = new \NewsPlugin\Includes\PerformanceOptimizer();
        
        // Test performance metrics
        $metrics = \NewsPlugin\Includes\PerformanceOptimizer::get_performance_metrics();
        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('memory_usage', $metrics);
        $this->assertArrayHasKey('query_count', $metrics);
        $this->assertArrayHasKey('load_time', $metrics);
        
        // Test memory usage is reasonable
        $this->assertLessThan(100 * 1024 * 1024, $metrics['memory_usage']); // Less than 100MB
    }
    
    /**
     * Test analytics flow
     */
    public function test_analytics_flow() {
        $analytics_manager = new \NewsPlugin\Includes\AnalyticsManager();
        
        // Test dashboard data
        $dashboard_data = \NewsPlugin\Includes\AnalyticsManager::get_dashboard_data();
        $this->assertIsArray($dashboard_data);
        $this->assertArrayHasKey('stats', $dashboard_data);
        $this->assertArrayHasKey('popular_articles', $dashboard_data);
        $this->assertArrayHasKey('placement_performance', $dashboard_data);
    }
    
    /**
     * Test widget flow
     */
    public function test_widget_flow() {
        $ticker_widget = new \NewsPlugin\Widgets\BreakingNewsTicker();
        
        $this->assertInstanceOf('WP_Widget', $ticker_widget);
        $this->assertEquals('news_breaking_ticker', $ticker_widget->id_base);
        $this->assertEquals('Breaking News Ticker', $ticker_widget->name);
    }
    
    /**
     * Test complete plugin activation flow
     */
    public function test_plugin_activation_flow() {
        // Test that all components are loaded
        $this->assertTrue(class_exists('NewsPlugin\PostTypes\NewsPostType'));
        $this->assertTrue(class_exists('NewsPlugin\PostTypes\NewsSection'));
        $this->assertTrue(class_exists('NewsPlugin\Includes\Options'));
        $this->assertTrue(class_exists('NewsPlugin\Includes\PlacementsRegistry'));
        $this->assertTrue(class_exists('NewsPlugin\Includes\RestApi'));
        $this->assertTrue(class_exists('NewsPlugin\Includes\CacheManager'));
        $this->assertTrue(class_exists('NewsPlugin\Fronts\FrontManager'));
        $this->assertTrue(class_exists('NewsPlugin\Admin\AdminMenu'));
        $this->assertTrue(class_exists('NewsPlugin\Widgets\BreakingNewsTicker'));
        
        // Test that post types and taxonomies are registered
        $this->assertTrue(post_type_exists('news'));
        $this->assertTrue(taxonomy_exists('news_section'));
        
        // Test that options are set
        $fronts = get_option('news_fronts');
        $this->assertIsArray($fronts);
        $this->assertArrayHasKey('home', $fronts);
    }
}
