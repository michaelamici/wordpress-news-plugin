<?php
/**
 * Unit tests for NewsPostType
 *
 * @package NewsPlugin
 */

class Test_NewsPostType extends WP_UnitTestCase {
    
    /**
     * @var NewsPlugin\PostTypes\NewsPostType
     */
    private $post_type;
    
    public function setUp(): void {
        parent::setUp();
        $this->post_type = new \NewsPlugin\PostTypes\NewsPostType();
    }
    
    public function tearDown(): void {
        parent::tearDown();
        News_Test_Utils::cleanup_test_data();
    }
    
    /**
     * Test post type registration
     */
    public function test_post_type_registration() {
        $this->assertTrue(post_type_exists('news'), 'News post type should be registered');
        
        $post_type_obj = get_post_type_object('news');
        $this->assertInstanceOf('WP_Post_Type', $post_type_obj);
        $this->assertEquals('news', $post_type_obj->name);
        $this->assertTrue($post_type_obj->public);
        $this->assertTrue($post_type_obj->show_in_rest);
    }
    
    /**
     * Test post type labels
     */
    public function test_post_type_labels() {
        $post_type_obj = get_post_type_object('news');
        $labels = $post_type_obj->labels;
        
        $this->assertEquals('News Articles', $labels->name);
        $this->assertEquals('News Article', $labels->singular_name);
        $this->assertEquals('Add New Article', $labels->add_new);
        $this->assertEquals('Edit Article', $labels->edit_item);
    }
    
    /**
     * Test post type capabilities
     */
    public function test_post_type_capabilities() {
        $post_type_obj = get_post_type_object('news');
        $caps = $post_type_obj->cap;
        
        $this->assertEquals('edit_news', $caps->edit_post);
        $this->assertEquals('read_news', $caps->read_post);
        $this->assertEquals('delete_news', $caps->delete_post);
        $this->assertEquals('edit_news', $caps->edit_posts);
        $this->assertEquals('edit_others_news', $caps->edit_others_posts);
        $this->assertEquals('publish_news', $caps->publish_posts);
        $this->assertEquals('read_private_news', $caps->read_private_posts);
    }
    
    /**
     * Test meta capability mapping
     */
    public function test_meta_capability_mapping() {
        $user_id = News_Test_Utils::create_test_user(['edit_news']);
        $post_id = News_Test_Utils::create_news_article();
        
        // Test edit_news capability
        $this->assertTrue(user_can($user_id, 'edit_news', $post_id));
        
        // Test edit_others_news capability
        $other_user_id = News_Test_Utils::create_test_user(['edit_others_news']);
        $this->assertTrue(user_can($other_user_id, 'edit_news', $post_id));
        
        wp_delete_user($user_id);
        wp_delete_user($other_user_id);
    }
    
    /**
     * Test post meta registration
     */
    public function test_post_meta_registration() {
        $meta_keys = [
            'dek', 'byline', 'location', 'is_featured', 'is_breaking',
            'is_exclusive', 'is_sponsored', 'embargo', 'expire'
        ];
        
        foreach ($meta_keys as $meta_key) {
            $meta = get_registered_meta_keys('post', 'news');
            $this->assertArrayHasKey($meta_key, $meta, "Meta key '$meta_key' should be registered");
        }
    }
    
    /**
     * Test meta field sanitization
     */
    public function test_meta_field_sanitization() {
        $post_id = News_Test_Utils::create_news_article();
        
        // Test text field sanitization
        update_post_meta($post_id, 'dek', '<script>alert("xss")</script>');
        $dek = get_post_meta($post_id, 'dek', true);
        $this->assertStringNotContainsString('<script>', $dek);
        
        // Test boolean field sanitization
        update_post_meta($post_id, 'is_featured', '1');
        $is_featured = get_post_meta($post_id, 'is_featured', true);
        $this->assertTrue($is_featured);
        
        update_post_meta($post_id, 'is_featured', '0');
        $is_featured = get_post_meta($post_id, 'is_featured', true);
        $this->assertFalse($is_featured);
        
        wp_delete_post($post_id, true);
    }
    
    /**
     * Test REST API exposure
     */
    public function test_rest_api_exposure() {
        $post_id = News_Test_Utils::create_news_article();
        
        // Test that meta fields are exposed in REST API
        $request = new WP_REST_Request('GET', "/wp/v2/news/{$post_id}");
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('dek', $data['meta']);
        $this->assertArrayHasKey('byline', $data['meta']);
        
        wp_delete_post($post_id, true);
    }
    
    /**
     * Test query modifications
     */
    public function test_query_modifications() {
        // Create test posts
        $featured_post = News_Test_Utils::create_news_article([
            'meta' => ['is_featured' => true]
        ]);
        
        $breaking_post = News_Test_Utils::create_news_article([
            'meta' => ['is_breaking' => true]
        ]);
        
        $regular_post = News_Test_Utils::create_news_article();
        
        // Test featured posts query
        $featured_query = new WP_Query([
            'post_type' => 'news',
            'meta_query' => [
                [
                    'key' => 'is_featured',
                    'value' => true,
                    'compare' => '=',
                ],
            ],
        ]);
        
        $this->assertEquals(1, $featured_query->found_posts);
        $this->assertEquals($featured_post, $featured_query->posts[0]->ID);
        
        // Test breaking news query
        $breaking_query = new WP_Query([
            'post_type' => 'news',
            'meta_query' => [
                [
                    'key' => 'is_breaking',
                    'value' => true,
                    'compare' => '=',
                ],
            ],
        ]);
        
        $this->assertEquals(1, $breaking_query->found_posts);
        $this->assertEquals($breaking_post, $breaking_query->posts[0]->ID);
        
        // Cleanup
        wp_delete_post($featured_post, true);
        wp_delete_post($breaking_post, true);
        wp_delete_post($regular_post, true);
    }
}
