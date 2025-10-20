<?php
/**
 * Unit tests for NewsSection
 *
 * @package NewsPlugin
 */

class Test_NewsSection extends WP_UnitTestCase {
    
    /**
     * @var NewsPlugin\PostTypes\NewsSection
     */
    private $taxonomy;
    
    public function setUp(): void {
        parent::setUp();
        $this->taxonomy = new \NewsPlugin\PostTypes\NewsSection();
    }
    
    public function tearDown(): void {
        parent::tearDown();
        News_Test_Utils::cleanup_test_data();
    }
    
    /**
     * Test taxonomy registration
     */
    public function test_taxonomy_registration() {
        $this->assertTrue(taxonomy_exists('news_section'), 'News section taxonomy should be registered');
        
        $taxonomy_obj = get_taxonomy('news_section');
        $this->assertInstanceOf('WP_Taxonomy', $taxonomy_obj);
        $this->assertEquals('news_section', $taxonomy_obj->name);
        $this->assertTrue($taxonomy_obj->public);
        $this->assertTrue($taxonomy_obj->hierarchical);
        $this->assertTrue($taxonomy_obj->show_in_rest);
    }
    
    /**
     * Test taxonomy labels
     */
    public function test_taxonomy_labels() {
        $taxonomy_obj = get_taxonomy('news_section');
        $labels = $taxonomy_obj->labels;
        
        $this->assertEquals('News Sections', $labels->name);
        $this->assertEquals('News Section', $labels->singular_name);
        $this->assertEquals('Add New Section', $labels->add_new_item);
        $this->assertEquals('Edit Section', $labels->edit_item);
    }
    
    /**
     * Test term meta registration
     */
    public function test_term_meta_registration() {
        $meta_keys = [
            'display_name', 'order', 'visibility', 'front_config'
        ];
        
        foreach ($meta_keys as $meta_key) {
            $meta = get_registered_meta_keys('term', 'news_section');
            $this->assertArrayHasKey($meta_key, $meta, "Term meta key '$meta_key' should be registered");
        }
    }
    
    /**
     * Test term creation and assignment
     */
    public function test_term_creation_and_assignment() {
        $term_id = News_Test_Utils::create_news_section([
            'name' => 'Politics',
            'description' => 'Political news section',
            'slug' => 'politics',
        ]);
        
        $this->assertNotInstanceOf('WP_Error', $term_id);
        $this->assertGreaterThan(0, $term_id);
        
        $term = get_term($term_id, 'news_section');
        $this->assertEquals('Politics', $term->name);
        $this->assertEquals('Political news section', $term->description);
        $this->assertEquals('politics', $term->slug);
    }
    
    /**
     * Test hierarchical sections
     */
    public function test_hierarchical_sections() {
        // Create parent section
        $parent_id = News_Test_Utils::create_news_section([
            'name' => 'Sports',
            'slug' => 'sports',
        ]);
        
        // Create child section
        $child_id = News_Test_Utils::create_news_section([
            'name' => 'Local Sports',
            'slug' => 'local-sports',
        ]);
        
        // Set parent relationship
        wp_update_term($child_id, 'news_section', [
            'parent' => $parent_id,
        ]);
        
        $child_term = get_term($child_id, 'news_section');
        $this->assertEquals($parent_id, $child_term->parent);
        
        // Test getting child terms
        $children = get_term_children($parent_id, 'news_section');
        $this->assertContains($child_id, $children);
    }
    
    /**
     * Test term meta functionality
     */
    public function test_term_meta_functionality() {
        $term_id = News_Test_Utils::create_news_section([
            'name' => 'Test Section',
            'meta' => [
                'display_name' => 'Custom Display Name',
                'order' => 5,
                'visibility' => 'public',
            ],
        ]);
        
        // Test meta retrieval
        $display_name = get_term_meta($term_id, 'display_name', true);
        $this->assertEquals('Custom Display Name', $display_name);
        
        $order = get_term_meta($term_id, 'order', true);
        $this->assertEquals(5, $order);
        
        $visibility = get_term_meta($term_id, 'visibility', true);
        $this->assertEquals('public', $visibility);
    }
    
    /**
     * Test term meta sanitization
     */
    public function test_term_meta_sanitization() {
        $term_id = News_Test_Utils::create_news_section();
        
        // Test text field sanitization
        update_term_meta($term_id, 'display_name', '<script>alert("xss")</script>');
        $display_name = get_term_meta($term_id, 'display_name', true);
        $this->assertStringNotContainsString('<script>', $display_name);
        
        // Test integer field sanitization
        update_term_meta($term_id, 'order', '10.5');
        $order = get_term_meta($term_id, 'order', true);
        $this->assertEquals(10, $order);
        
        // Test boolean field sanitization
        update_term_meta($term_id, 'visibility', '1');
        $visibility = get_term_meta($term_id, 'visibility', true);
        $this->assertTrue($visibility);
    }
    
    /**
     * Test REST API exposure
     */
    public function test_rest_api_exposure() {
        $term_id = News_Test_Utils::create_news_section([
            'name' => 'REST Test Section',
            'meta' => [
                'display_name' => 'REST Display Name',
                'order' => 3,
            ],
        ]);
        
        $request = new WP_REST_Request('GET', "/wp/v2/news_section/{$term_id}");
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        
        $this->assertEquals('REST Test Section', $data['name']);
        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals('REST Display Name', $data['meta']['display_name']);
        $this->assertEquals(3, $data['meta']['order']);
    }
    
    /**
     * Test post-term relationships
     */
    public function test_post_term_relationships() {
        $term_id = News_Test_Utils::create_news_section([
            'name' => 'Test Section',
        ]);
        
        $post_id = News_Test_Utils::create_news_article();
        
        // Assign term to post
        wp_set_post_terms($post_id, [$term_id], 'news_section');
        
        // Test term assignment
        $post_terms = wp_get_post_terms($post_id, 'news_section');
        $this->assertCount(1, $post_terms);
        $this->assertEquals($term_id, $post_terms[0]->term_id);
        
        // Test post count
        $term = get_term($term_id, 'news_section');
        $this->assertEquals(1, $term->count);
        
        wp_delete_post($post_id, true);
    }
    
    /**
     * Test term queries
     */
    public function test_term_queries() {
        // Create test terms
        $term1 = News_Test_Utils::create_news_section([
            'name' => 'Section 1',
            'meta' => ['order' => 2],
        ]);
        
        $term2 = News_Test_Utils::create_news_section([
            'name' => 'Section 2',
            'meta' => ['order' => 1],
        ]);
        
        // Test ordering by meta
        $terms = get_terms([
            'taxonomy' => 'news_section',
            'meta_key' => 'order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'hide_empty' => false,
        ]);
        
        $this->assertCount(2, $terms);
        $this->assertEquals($term2, $terms[0]->term_id); // order = 1
        $this->assertEquals($term1, $terms[1]->term_id); // order = 2
    }
}
