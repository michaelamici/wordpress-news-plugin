<?php
/**
 * Unit Tests for JournalistRole
 *
 * @package NewsPlugin
 */

class Test_JournalistRole extends WP_UnitTestCase {
    
    /**
     * @var \NewsPlugin\Includes\JournalistRole
     */
    private $journalist_role;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        
        // Initialize the journalist role class
        $this->journalist_role = new \NewsPlugin\Includes\JournalistRole();
        
        // Ensure journalist role exists
        $this->journalist_role->add_journalist_role();
    }
    
    /**
     * Clean up after tests
     */
    public function tearDown(): void {
        parent::tearDown();
    }
    
    /**
     * Test journalist role creation
     */
    public function test_journalist_role_creation() {
        $role = get_role(\NewsPlugin\Includes\JournalistRole::ROLE_SLUG);
        
        $this->assertInstanceOf('WP_Role', $role, 'Journalist role should be created');
        $this->assertEquals('Journalist', $role->name, 'Role name should be "Journalist"');
    }
    
    /**
     * Test journalist role capabilities
     */
    public function test_journalist_role_capabilities() {
        $role = get_role(\NewsPlugin\Includes\JournalistRole::ROLE_SLUG);
        $capabilities = $role->capabilities;
        
        // Test basic WordPress capabilities
        $this->assertTrue($capabilities['read'], 'Journalist should be able to read');
        $this->assertTrue($capabilities['upload_files'], 'Journalist should be able to upload files');
        $this->assertFalse($capabilities['edit_posts'], 'Journalist should not be able to edit regular posts');
        $this->assertFalse($capabilities['delete_posts'], 'Journalist should not be able to delete regular posts');
        
        // Test news-specific capabilities
        $this->assertTrue($capabilities['edit_news'], 'Journalist should be able to edit news');
        $this->assertTrue($capabilities['edit_own_news'], 'Journalist should be able to edit own news');
        $this->assertFalse($capabilities['edit_others_news'], 'Journalist should not be able to edit others news');
        $this->assertTrue($capabilities['publish_news'], 'Journalist should be able to publish news');
        $this->assertTrue($capabilities['delete_own_news'], 'Journalist should be able to delete own news');
        $this->assertFalse($capabilities['delete_news'], 'Journalist should not be able to delete all news');
        $this->assertFalse($capabilities['delete_others_news'], 'Journalist should not be able to delete others news');
        
        // Test editorial workflow capabilities
        $this->assertTrue($capabilities['edit_editorial_status'], 'Journalist should be able to edit editorial status');
        $this->assertTrue($capabilities['edit_editorial_priority'], 'Journalist should be able to edit editorial priority');
        $this->assertFalse($capabilities['edit_editorial_assignee'], 'Journalist should not be able to edit editorial assignee');
        $this->assertTrue($capabilities['edit_editorial_notes'], 'Journalist should be able to edit editorial notes');
        
        // Test author profile capabilities
        $this->assertTrue($capabilities['edit_author_profile'], 'Journalist should be able to edit author profile');
        $this->assertTrue($capabilities['edit_own_author_profile'], 'Journalist should be able to edit own author profile');
        
        // Test calendar capabilities
        $this->assertTrue($capabilities['view_editorial_calendar'], 'Journalist should be able to view editorial calendar');
        $this->assertTrue($capabilities['edit_editorial_calendar'], 'Journalist should be able to edit editorial calendar');
        
        // Test section capabilities
        $this->assertFalse($capabilities['edit_news_sections'], 'Journalist should not be able to edit news sections');
        $this->assertTrue($capabilities['assign_news_sections'], 'Journalist should be able to assign news sections');
        
        // Test media capabilities
        $this->assertTrue($capabilities['edit_media'], 'Journalist should be able to edit media');
        $this->assertFalse($capabilities['delete_media'], 'Journalist should not be able to delete media');
    }
    
    /**
     * Test journalist role assignment
     */
    public function test_journalist_role_assignment() {
        // Create a test user
        $user_id = $this->factory->user->create([
            'user_login' => 'test_journalist',
            'user_email' => 'test@example.com',
            'role' => 'subscriber'
        ]);
        
        $user = get_userdata($user_id);
        $this->assertNotContains(\NewsPlugin\Includes\JournalistRole::ROLE_SLUG, $user->roles, 'User should not have journalist role initially');
        
        // Assign journalist role
        $user->set_role(\NewsPlugin\Includes\JournalistRole::ROLE_SLUG);
        
        // Refresh user data
        $user = get_userdata($user_id);
        $this->assertContains(\NewsPlugin\Includes\JournalistRole::ROLE_SLUG, $user->roles, 'User should have journalist role after assignment');
    }
    
    /**
     * Test journalist role capabilities after assignment
     */
    public function test_journalist_capabilities_after_assignment() {
        // Create a test user with journalist role
        $user_id = $this->factory->user->create([
            'user_login' => 'test_journalist_2',
            'user_email' => 'test2@example.com',
            'role' => \NewsPlugin\Includes\JournalistRole::ROLE_SLUG
        ]);
        
        $user = get_userdata($user_id);
        
        // Test key capabilities
        $this->assertTrue($user->has_cap('read'), 'Journalist should be able to read');
        $this->assertTrue($user->has_cap('upload_files'), 'Journalist should be able to upload files');
        $this->assertTrue($user->has_cap('edit_news'), 'Journalist should be able to edit news');
        $this->assertTrue($user->has_cap('edit_own_news'), 'Journalist should be able to edit own news');
        $this->assertTrue($user->has_cap('publish_news'), 'Journalist should be able to publish news');
        $this->assertTrue($user->has_cap('delete_own_news'), 'Journalist should be able to delete own news');
        $this->assertFalse($user->has_cap('edit_others_news'), 'Journalist should not be able to edit others news');
        $this->assertFalse($user->has_cap('delete_others_news'), 'Journalist should not be able to delete others news');
        $this->assertTrue($user->has_cap('edit_editorial_status'), 'Journalist should be able to edit editorial status');
        $this->assertTrue($user->has_cap('view_editorial_calendar'), 'Journalist should be able to view editorial calendar');
        $this->assertTrue($user->has_cap('edit_author_profile'), 'Journalist should be able to edit author profile');
        $this->assertTrue($user->has_cap('assign_news_sections'), 'Journalist should be able to assign news sections');
    }
    
    /**
     * Test is_journalist method
     */
    public function test_is_journalist_method() {
        // Create a journalist user
        $journalist_id = $this->factory->user->create([
            'user_login' => 'test_journalist_3',
            'user_email' => 'test3@example.com',
            'role' => \NewsPlugin\Includes\JournalistRole::ROLE_SLUG
        ]);
        
        // Create a regular user
        $regular_id = $this->factory->user->create([
            'user_login' => 'test_regular',
            'user_email' => 'test4@example.com',
            'role' => 'subscriber'
        ]);
        
        // Test journalist user
        $this->assertTrue(\NewsPlugin\Includes\JournalistRole::is_journalist($journalist_id), 'Journalist user should be identified as journalist');
        
        // Test regular user
        $this->assertFalse(\NewsPlugin\Includes\JournalistRole::is_journalist($regular_id), 'Regular user should not be identified as journalist');
    }
    
    /**
     * Test get_journalist_users method
     */
    public function test_get_journalist_users() {
        // Create multiple users with different roles
        $journalist1_id = $this->factory->user->create([
            'user_login' => 'journalist_1',
            'user_email' => 'journalist1@example.com',
            'role' => \NewsPlugin\Includes\JournalistRole::ROLE_SLUG
        ]);
        
        $journalist2_id = $this->factory->user->create([
            'user_login' => 'journalist_2',
            'user_email' => 'journalist2@example.com',
            'role' => \NewsPlugin\Includes\JournalistRole::ROLE_SLUG
        ]);
        
        $regular_id = $this->factory->user->create([
            'user_login' => 'regular_user',
            'user_email' => 'regular@example.com',
            'role' => 'subscriber'
        ]);
        
        // Get journalist users
        $journalist_users = \NewsPlugin\Includes\JournalistRole::get_journalist_users();
        
        $this->assertCount(2, $journalist_users, 'Should return 2 journalist users');
        
        $user_ids = wp_list_pluck($journalist_users, 'ID');
        $this->assertContains($journalist1_id, $user_ids, 'Should include first journalist');
        $this->assertContains($journalist2_id, $user_ids, 'Should include second journalist');
        $this->assertNotContains($regular_id, $user_ids, 'Should not include regular user');
    }
    
    /**
     * Test journalist capabilities with news posts
     */
    public function test_journalist_news_post_capabilities() {
        // Create a journalist user
        $journalist_id = $this->factory->user->create([
            'user_login' => 'test_journalist_4',
            'user_email' => 'test4@example.com',
            'role' => \NewsPlugin\Includes\JournalistRole::ROLE_SLUG
        ]);
        
        // Create a news post by the journalist
        $post_id = $this->factory->post->create([
            'post_type' => 'news',
            'post_author' => $journalist_id,
            'post_status' => 'draft'
        ]);
        
        // Switch to journalist user
        wp_set_current_user($journalist_id);
        
        // Test capabilities on own post
        $this->assertTrue(current_user_can('edit_news', $post_id), 'Journalist should be able to edit own news post');
        $this->assertTrue(current_user_can('delete_news', $post_id), 'Journalist should be able to delete own news post');
        $this->assertTrue(current_user_can('publish_news', $post_id), 'Journalist should be able to publish own news post');
        
        // Create another news post by different user
        $other_user_id = $this->factory->user->create([
            'user_login' => 'other_user',
            'user_email' => 'other@example.com',
            'role' => 'subscriber'
        ]);
        
        $other_post_id = $this->factory->post->create([
            'post_type' => 'news',
            'post_author' => $other_user_id,
            'post_status' => 'draft'
        ]);
        
        // Test capabilities on others' post
        $this->assertFalse(current_user_can('edit_news', $other_post_id), 'Journalist should not be able to edit others news post');
        $this->assertFalse(current_user_can('delete_news', $other_post_id), 'Journalist should not be able to delete others news post');
        $this->assertFalse(current_user_can('publish_news', $other_post_id), 'Journalist should not be able to publish others news post');
    }
    
    /**
     * Test journalist role cleanup
     */
    public function test_journalist_role_cleanup() {
        // Remove the journalist role
        remove_role(\NewsPlugin\Includes\JournalistRole::ROLE_SLUG);
        
        $role = get_role(\NewsPlugin\Includes\JournalistRole::ROLE_SLUG);
        $this->assertNull($role, 'Journalist role should be removed');
        
        // Re-add the role for other tests
        $this->journalist_role->add_journalist_role();
    }
}

