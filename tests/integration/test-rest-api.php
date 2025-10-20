<?php
/**
 * Integration tests for REST API
 *
 * @package NewsPlugin
 */

class Test_REST_API_Integration extends WP_UnitTestCase {
    
    /**
     * @var int
     */
    private $user_id;
    
    public function setUp(): void {
        parent::setUp();
        $this->user_id = News_Test_Utils::create_test_user(['edit_news']);
    }
    
    public function tearDown(): void {
        parent::tearDown();
        wp_delete_user($this->user_id);
        News_Test_Utils::cleanup_test_data();
    }
    
    /**
     * Test news posts REST endpoint
     */
    public function test_news_posts_rest_endpoint() {
        $post_id = News_Test_Utils::create_news_article([
            'meta' => [
                'dek' => 'Test dek',
                'byline' => 'Test Author',
                'is_featured' => true,
            ],
        ]);
        
        $request = new WP_REST_Request('GET', '/wp/v2/news');
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
        
        // Find our test post
        $test_post = null;
        foreach ($data as $post) {
            if ($post['id'] === $post_id) {
                $test_post = $post;
                break;
            }
        }
        
        $this->assertNotNull($test_post);
        $this->assertEquals('Test News Article', $test_post['title']['rendered']);
        $this->assertArrayHasKey('meta', $test_post);
        $this->assertEquals('Test dek', $test_post['meta']['dek']);
        $this->assertEquals('Test Author', $test_post['meta']['byline']);
        $this->assertTrue($test_post['meta']['is_featured']);
        
        wp_delete_post($post_id, true);
    }
    
    /**
     * Test news sections REST endpoint
     */
    public function test_news_sections_rest_endpoint() {
        $term_id = News_Test_Utils::create_news_section([
            'name' => 'Test Section',
            'description' => 'Test section description',
            'meta' => [
                'display_name' => 'Custom Display Name',
                'order' => 5,
            ],
        ]);
        
        $request = new WP_REST_Request('GET', '/wp/v2/news_section');
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        
        $this->assertIsArray($data);
        
        // Find our test term
        $test_term = null;
        foreach ($data as $term) {
            if ($term['id'] === $term_id) {
                $test_term = $term;
                break;
            }
        }
        
        $this->assertNotNull($test_term);
        $this->assertEquals('Test Section', $test_term['name']);
        $this->assertEquals('Test section description', $test_term['description']);
        $this->assertArrayHasKey('meta', $test_term);
        $this->assertEquals('Custom Display Name', $test_term['meta']['display_name']);
        $this->assertEquals(5, $test_term['meta']['order']);
    }
    
    /**
     * Test front data REST endpoint
     */
    public function test_front_data_rest_endpoint() {
        $request = new WP_REST_Request('GET', '/news/v1/front/home');
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('regions', $data);
        $this->assertEquals('home', $data['id']);
        $this->assertEquals('home', $data['type']);
        $this->assertArrayHasKey('hero', $data['regions']);
        $this->assertArrayHasKey('rails', $data['regions']);
        $this->assertArrayHasKey('sidebar', $data['regions']);
    }
    
    /**
     * Test breaking alerts REST endpoint
     */
    public function test_breaking_alerts_rest_endpoint() {
        $request = new WP_REST_Request('GET', '/news/v1/breaking');
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        
        $this->assertArrayHasKey('active', $data);
        $this->assertArrayHasKey('headline', $data);
        $this->assertArrayHasKey('link', $data);
        $this->assertArrayHasKey('severity', $data);
    }
    
    /**
     * Test fronts list REST endpoint
     */
    public function test_fronts_list_rest_endpoint() {
        $request = new WP_REST_Request('GET', '/news/v1/fronts');
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
        
        // Should include home front
        $home_front = null;
        foreach ($data as $front) {
            if ($front['id'] === 'home') {
                $home_front = $front;
                break;
            }
        }
        
        $this->assertNotNull($home_front);
        $this->assertEquals('home', $home_front['id']);
        $this->assertEquals('Home Front', $home_front['name']);
    }
    
    /**
     * Test REST API permissions
     */
    public function test_rest_api_permissions() {
        // Test unauthenticated access
        wp_set_current_user(0);
        
        $request = new WP_REST_Request('GET', '/news/v1/front/home');
        $response = rest_do_request($request);
        
        // Should be accessible to unauthenticated users
        $this->assertEquals(200, $response->get_status());
        
        // Test authenticated access
        wp_set_current_user($this->user_id);
        
        $request = new WP_REST_Request('GET', '/news/v1/front/home');
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
    }
    
    /**
     * Test REST API error handling
     */
    public function test_rest_api_error_handling() {
        // Test invalid front ID
        $request = new WP_REST_Request('GET', '/news/v1/front/invalid');
        $response = rest_do_request($request);
        
        $this->assertEquals(404, $response->get_status());
        
        // Test invalid section front
        $request = new WP_REST_Request('GET', '/news/v1/front/section/99999');
        $response = rest_do_request($request);
        
        $this->assertEquals(404, $response->get_status());
    }
    
    /**
     * Test REST API caching
     */
    public function test_rest_api_caching() {
        $request = new WP_REST_Request('GET', '/news/v1/front/home');
        
        // First request
        $response1 = rest_do_request($request);
        $this->assertEquals(200, $response1->get_status());
        
        // Second request (should use cache)
        $response2 = rest_do_request($request);
        $this->assertEquals(200, $response2->get_status());
        
        $this->assertEquals($response1->get_data(), $response2->get_data());
    }
    
    /**
     * Test REST API performance
     */
    public function test_rest_api_performance() {
        $start_time = microtime(true);
        
        $request = new WP_REST_Request('GET', '/news/v1/front/home');
        $response = rest_do_request($request);
        
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;
        
        $this->assertEquals(200, $response->get_status());
        $this->assertLessThan(1.0, $execution_time);
    }
}
