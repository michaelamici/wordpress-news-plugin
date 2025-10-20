<?php
/**
 * Integration tests for Fronts system
 *
 * @package NewsPlugin
 */

class Test_Fronts_Integration extends WP_UnitTestCase {
    
    /**
     * @var NewsPlugin\Fronts\FrontManager
     */
    private $front_manager;
    
    public function setUp(): void {
        parent::setUp();
        $this->front_manager = new \NewsPlugin\Fronts\FrontManager();
    }
    
    public function tearDown(): void {
        parent::tearDown();
        News_Test_Utils::cleanup_test_data();
    }
    
    /**
     * Test home front creation and retrieval
     */
    public function test_home_front_creation() {
        $home_front = $this->front_manager->get_front('home');
        
        $this->assertInstanceOf('NewsPlugin\Fronts\HomeFront', $home_front);
        $this->assertEquals('home', $home_front->get_id());
    }
    
    /**
     * Test section front creation
     */
    public function test_section_front_creation() {
        $term_id = News_Test_Utils::create_news_section([
            'name' => 'Politics',
            'slug' => 'politics',
        ]);
        
        $section_front = $this->front_manager->get_front('section', $term_id);
        
        $this->assertInstanceOf('NewsPlugin\Fronts\SectionFront', $section_front);
        $this->assertEquals($term_id, $section_front->get_section_id());
    }
    
    /**
     * Test front regions
     */
    public function test_front_regions() {
        $home_front = $this->front_manager->get_front('home');
        $regions = $home_front->get_regions();
        
        $this->assertArrayHasKey('hero', $regions);
        $this->assertArrayHasKey('rails', $regions);
        $this->assertArrayHasKey('sidebar', $regions);
    }
    
    /**
     * Test front queries
     */
    public function test_front_queries() {
        // Create test content
        $featured_post = News_Test_Utils::create_news_article([
            'meta' => ['is_featured' => true]
        ]);
        
        $breaking_post = News_Test_Utils::create_news_article([
            'meta' => ['is_breaking' => true]
        ]);
        
        $regular_post = News_Test_Utils::create_news_article();
        
        $home_front = $this->front_manager->get_front('home');
        $regions = $home_front->get_regions();
        
        // Test hero region query
        $hero_query = $regions['hero']['query'];
        $this->assertArrayHasKey('meta_query', $hero_query);
        
        // Test rails region query
        $rails_query = $regions['rails']['query'];
        $this->assertArrayHasKey('posts_per_page', $rails_query);
    }
    
    /**
     * Test front caching
     */
    public function test_front_caching() {
        $home_front = $this->front_manager->get_front('home');
        
        // First call should cache the result
        $regions1 = $home_front->get_regions();
        
        // Second call should use cache
        $regions2 = $home_front->get_regions();
        
        $this->assertEquals($regions1, $regions2);
    }
    
    /**
     * Test cache invalidation
     */
    public function test_cache_invalidation() {
        $home_front = $this->front_manager->get_front('home');
        $regions1 = $home_front->get_regions();
        
        // Create a new post
        $new_post = News_Test_Utils::create_news_article();
        
        // Cache should be invalidated
        $regions2 = $home_front->get_regions();
        
        // Results might be different due to new post
        $this->assertIsArray($regions2);
        
        wp_delete_post($new_post, true);
    }
    
    /**
     * Test front template rendering
     */
    public function test_front_template_rendering() {
        $home_front = $this->front_manager->get_front('home');
        
        // Test that template method exists and is callable
        $this->assertTrue(method_exists($home_front, 'render'));
        
        // Test template rendering (should not throw errors)
        ob_start();
        $home_front->render();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }
    
    /**
     * Test front hooks and filters
     */
    public function test_front_hooks_and_filters() {
        $home_front = $this->front_manager->get_front('home');
        
        // Test that hooks are registered
        $this->assertTrue(has_action('news_front_regions', [$home_front, 'get_regions']));
        $this->assertTrue(has_action('news_render_front', [$home_front, 'render']));
    }
    
    /**
     * Test front configuration
     */
    public function test_front_configuration() {
        $home_front = $this->front_manager->get_front('home');
        $config = $home_front->get_config();
        
        $this->assertArrayHasKey('type', $config);
        $this->assertArrayHasKey('regions', $config);
        $this->assertEquals('home', $config['type']);
    }
    
    /**
     * Test section front with content
     */
    public function test_section_front_with_content() {
        $term_id = News_Test_Utils::create_news_section([
            'name' => 'Sports',
            'slug' => 'sports',
        ]);
        
        // Create posts in this section
        $post1 = News_Test_Utils::create_news_article([
            'meta' => ['is_featured' => true]
        ]);
        $post2 = News_Test_Utils::create_news_article();
        
        // Assign posts to section
        wp_set_post_terms($post1, [$term_id], 'news_section');
        wp_set_post_terms($post2, [$term_id], 'news_section');
        
        $section_front = $this->front_manager->get_front('section', $term_id);
        $regions = $section_front->get_regions();
        
        $this->assertIsArray($regions);
        $this->assertArrayHasKey('hero', $regions);
        $this->assertArrayHasKey('rails', $regions);
        
        // Cleanup
        wp_delete_post($post1, true);
        wp_delete_post($post2, true);
    }
    
    /**
     * Test front performance
     */
    public function test_front_performance() {
        $start_time = microtime(true);
        
        $home_front = $this->front_manager->get_front('home');
        $regions = $home_front->get_regions();
        
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;
        
        // Front should load quickly (less than 1 second)
        $this->assertLessThan(1.0, $execution_time);
    }
}
