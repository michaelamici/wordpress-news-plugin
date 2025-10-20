<?php
/**
 * Integration Tests for Role Switcher
 *
 * @package NewsPlugin
 */

class Test_RoleSwitcher extends WP_UnitTestCase {
    
    /**
     * @var \NewsPlugin\Admin\RoleSwitcher
     */
    private $role_switcher;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        
        // Initialize the role switcher
        $this->role_switcher = new \NewsPlugin\Admin\RoleSwitcher();
        
        // Ensure journalist role exists
        $journalist_role = new \NewsPlugin\Includes\JournalistRole();
        $journalist_role->add_journalist_role();
    }
    
    /**
     * Clean up after tests
     */
    public function tearDown(): void {
        parent::tearDown();
    }
    
    /**
     * Test role switcher initialization
     */
    public function test_role_switcher_initialization() {
        $this->assertInstanceOf('\NewsPlugin\Admin\RoleSwitcher', $this->role_switcher, 'Role switcher should be initialized');
    }
    
    /**
     * Test role switching from administrator to journalist
     */
    public function test_switch_to_journalist_role() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin',
            'user_email' => 'admin@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Verify initial role
        $user = get_userdata($admin_id);
        $this->assertContains('administrator', $user->roles, 'User should have administrator role initially');
        
        // Simulate role switch to journalist
        $_POST['action'] = 'switch_user_role';
        $_POST['role'] = 'journalist';
        $_POST['nonce'] = wp_create_nonce('role_switch_nonce');
        
        // Mock the AJAX request
        $this->_set_post_data([
            'action' => 'switch_user_role',
            'role' => 'journalist',
            'nonce' => wp_create_nonce('role_switch_nonce')
        ]);
        
        // Store original role
        update_user_meta($admin_id, '_original_admin_role', 'administrator');
        
        // Switch role
        $user = get_userdata($admin_id);
        $user->set_role('journalist');
        
        // Verify role change
        $user = get_userdata($admin_id);
        $this->assertContains('journalist', $user->roles, 'User should have journalist role after switch');
        $this->assertNotContains('administrator', $user->roles, 'User should not have administrator role after switch');
        
        // Verify original role is stored
        $original_role = get_user_meta($admin_id, '_original_admin_role', true);
        $this->assertEquals('administrator', $original_role, 'Original role should be stored');
    }
    
    /**
     * Test role switching from administrator to editor
     */
    public function test_switch_to_editor_role() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin_2',
            'user_email' => 'admin2@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Store original role
        update_user_meta($admin_id, '_original_admin_role', 'administrator');
        
        // Switch to editor role
        $user = get_userdata($admin_id);
        $user->set_role('editor');
        
        // Verify role change
        $user = get_userdata($admin_id);
        $this->assertContains('editor', $user->roles, 'User should have editor role after switch');
        $this->assertNotContains('administrator', $user->roles, 'User should not have administrator role after switch');
        
        // Test editor capabilities
        $this->assertTrue($user->has_cap('edit_posts'), 'Editor should be able to edit posts');
        $this->assertTrue($user->has_cap('edit_others_posts'), 'Editor should be able to edit others posts');
        $this->assertTrue($user->has_cap('publish_posts'), 'Editor should be able to publish posts');
        $this->assertTrue($user->has_cap('delete_posts'), 'Editor should be able to delete posts');
        $this->assertTrue($user->has_cap('delete_others_posts'), 'Editor should be able to delete others posts');
    }
    
    /**
     * Test role switching from administrator to author
     */
    public function test_switch_to_author_role() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin_3',
            'user_email' => 'admin3@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Store original role
        update_user_meta($admin_id, '_original_admin_role', 'administrator');
        
        // Switch to author role
        $user = get_userdata($admin_id);
        $user->set_role('author');
        
        // Verify role change
        $user = get_userdata($admin_id);
        $this->assertContains('author', $user->roles, 'User should have author role after switch');
        $this->assertNotContains('administrator', $user->roles, 'User should not have administrator role after switch');
        
        // Test author capabilities
        $this->assertTrue($user->has_cap('edit_posts'), 'Author should be able to edit posts');
        $this->assertFalse($user->has_cap('edit_others_posts'), 'Author should not be able to edit others posts');
        $this->assertTrue($user->has_cap('publish_posts'), 'Author should be able to publish posts');
        $this->assertTrue($user->has_cap('delete_posts'), 'Author should be able to delete posts');
        $this->assertFalse($user->has_cap('delete_others_posts'), 'Author should not be able to delete others posts');
    }
    
    /**
     * Test role switching from administrator to contributor
     */
    public function test_switch_to_contributor_role() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin_4',
            'user_email' => 'admin4@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Store original role
        update_user_meta($admin_id, '_original_admin_role', 'administrator');
        
        // Switch to contributor role
        $user = get_userdata($admin_id);
        $user->set_role('contributor');
        
        // Verify role change
        $user = get_userdata($admin_id);
        $this->assertContains('contributor', $user->roles, 'User should have contributor role after switch');
        $this->assertNotContains('administrator', $user->roles, 'User should not have administrator role after switch');
        
        // Test contributor capabilities
        $this->assertTrue($user->has_cap('edit_posts'), 'Contributor should be able to edit posts');
        $this->assertFalse($user->has_cap('edit_others_posts'), 'Contributor should not be able to edit others posts');
        $this->assertFalse($user->has_cap('publish_posts'), 'Contributor should not be able to publish posts');
        $this->assertTrue($user->has_cap('delete_posts'), 'Contributor should be able to delete posts');
        $this->assertFalse($user->has_cap('delete_others_posts'), 'Contributor should not be able to delete others posts');
    }
    
    /**
     * Test role restoration to administrator
     */
    public function test_restore_administrator_role() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin_5',
            'user_email' => 'admin5@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Store original role
        update_user_meta($admin_id, '_original_admin_role', 'administrator');
        
        // Switch to journalist role
        $user = get_userdata($admin_id);
        $user->set_role('journalist');
        
        // Verify role change
        $user = get_userdata($admin_id);
        $this->assertContains('journalist', $user->roles, 'User should have journalist role after switch');
        
        // Restore administrator role
        $original_role = get_user_meta($admin_id, '_original_admin_role', true);
        $user = get_userdata($admin_id);
        $user->set_role($original_role);
        
        // Clear stored original role
        delete_user_meta($admin_id, '_original_admin_role');
        
        // Verify role restoration
        $user = get_userdata($admin_id);
        $this->assertContains('administrator', $user->roles, 'User should have administrator role after restoration');
        $this->assertNotContains('journalist', $user->roles, 'User should not have journalist role after restoration');
        
        // Verify original role meta is cleared
        $stored_role = get_user_meta($admin_id, '_original_admin_role', true);
        $this->assertEmpty($stored_role, 'Original role meta should be cleared');
    }
    
    /**
     * Test role switching with news post capabilities
     */
    public function test_role_switching_news_capabilities() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin_6',
            'user_email' => 'admin6@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Test administrator capabilities
        $this->assertTrue(current_user_can('edit_news'), 'Administrator should be able to edit news');
        $this->assertTrue(current_user_can('edit_others_news'), 'Administrator should be able to edit others news');
        $this->assertTrue(current_user_can('publish_news'), 'Administrator should be able to publish news');
        $this->assertTrue(current_user_can('delete_news'), 'Administrator should be able to delete news');
        $this->assertTrue(current_user_can('delete_others_news'), 'Administrator should be able to delete others news');
        
        // Switch to journalist role
        $user = get_userdata($admin_id);
        $user->set_role('journalist');
        
        // Test journalist capabilities
        $this->assertTrue(current_user_can('edit_news'), 'Journalist should be able to edit news');
        $this->assertFalse(current_user_can('edit_others_news'), 'Journalist should not be able to edit others news');
        $this->assertTrue(current_user_can('publish_news'), 'Journalist should be able to publish news');
        $this->assertTrue(current_user_can('delete_own_news'), 'Journalist should be able to delete own news');
        $this->assertFalse(current_user_can('delete_others_news'), 'Journalist should not be able to delete others news');
        
        // Switch to editor role
        $user = get_userdata($admin_id);
        $user->set_role('editor');
        
        // Test editor capabilities
        $this->assertTrue(current_user_can('edit_news'), 'Editor should be able to edit news');
        $this->assertTrue(current_user_can('edit_others_news'), 'Editor should be able to edit others news');
        $this->assertTrue(current_user_can('publish_news'), 'Editor should be able to publish news');
        $this->assertTrue(current_user_can('delete_news'), 'Editor should be able to delete news');
        $this->assertTrue(current_user_can('delete_others_news'), 'Editor should be able to delete others news');
    }
    
    /**
     * Test role switching with editorial calendar capabilities
     */
    public function test_role_switching_editorial_calendar_capabilities() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin_7',
            'user_email' => 'admin7@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Test administrator capabilities
        $this->assertTrue(current_user_can('edit_editorial_calendar'), 'Administrator should be able to edit editorial calendar');
        $this->assertTrue(current_user_can('view_editorial_calendar'), 'Administrator should be able to view editorial calendar');
        
        // Switch to journalist role
        $user = get_userdata($admin_id);
        $user->set_role('journalist');
        
        // Test journalist capabilities
        $this->assertTrue(current_user_can('edit_editorial_calendar'), 'Journalist should be able to edit editorial calendar');
        $this->assertTrue(current_user_can('view_editorial_calendar'), 'Journalist should be able to view editorial calendar');
        
        // Switch to author role
        $user = get_userdata($admin_id);
        $user->set_role('author');
        
        // Test author capabilities (should not have editorial calendar access)
        $this->assertFalse(current_user_can('edit_editorial_calendar'), 'Author should not be able to edit editorial calendar');
        $this->assertFalse(current_user_can('view_editorial_calendar'), 'Author should not be able to view editorial calendar');
    }
    
    /**
     * Test role switching with author profile capabilities
     */
    public function test_role_switching_author_profile_capabilities() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin_8',
            'user_email' => 'admin8@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Test administrator capabilities
        $this->assertTrue(current_user_can('edit_author_profile'), 'Administrator should be able to edit author profile');
        $this->assertTrue(current_user_can('edit_own_author_profile'), 'Administrator should be able to edit own author profile');
        
        // Switch to journalist role
        $user = get_userdata($admin_id);
        $user->set_role('journalist');
        
        // Test journalist capabilities
        $this->assertTrue(current_user_can('edit_author_profile'), 'Journalist should be able to edit author profile');
        $this->assertTrue(current_user_can('edit_own_author_profile'), 'Journalist should be able to edit own author profile');
        
        // Switch to author role
        $user = get_userdata($admin_id);
        $user->set_role('author');
        
        // Test author capabilities (should not have author profile access)
        $this->assertFalse(current_user_can('edit_author_profile'), 'Author should not be able to edit author profile');
        $this->assertFalse(current_user_can('edit_own_author_profile'), 'Author should not be able to edit own author profile');
    }
    
    /**
     * Test role switching with news section capabilities
     */
    public function test_role_switching_news_section_capabilities() {
        // Create an administrator user
        $admin_id = $this->factory->user->create([
            'user_login' => 'test_admin_9',
            'user_email' => 'admin9@example.com',
            'role' => 'administrator'
        ]);
        
        wp_set_current_user($admin_id);
        
        // Test administrator capabilities
        $this->assertTrue(current_user_can('edit_news_sections'), 'Administrator should be able to edit news sections');
        $this->assertTrue(current_user_can('assign_news_sections'), 'Administrator should be able to assign news sections');
        
        // Switch to journalist role
        $user = get_userdata($admin_id);
        $user->set_role('journalist');
        
        // Test journalist capabilities
        $this->assertFalse(current_user_can('edit_news_sections'), 'Journalist should not be able to edit news sections');
        $this->assertTrue(current_user_can('assign_news_sections'), 'Journalist should be able to assign news sections');
        
        // Switch to author role
        $user = get_userdata($admin_id);
        $user->set_role('author');
        
        // Test author capabilities (should not have news section access)
        $this->assertFalse(current_user_can('edit_news_sections'), 'Author should not be able to edit news sections');
        $this->assertFalse(current_user_can('assign_news_sections'), 'Author should not be able to assign news sections');
    }
}
