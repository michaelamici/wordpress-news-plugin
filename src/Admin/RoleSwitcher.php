<?php
/**
 * Role Switcher for Testing
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles role switching for testing different user capabilities
 */
class RoleSwitcher {
    
    /**
     * Initialize role switcher
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_switch_user_role', [$this, 'handle_role_switch']);
        add_action('wp_ajax_restore_admin_role', [$this, 'handle_role_restore']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu(): void {
        // Show to all users - no restrictions
        
        add_submenu_page(
            'tools.php',
            __('Role Switcher', 'news'),
            __('Role Switcher', 'news'),
            'read',
            'news-role-switcher',
            [$this, 'render_page']
        );
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets(): void {
        $screen = get_current_screen();
        
        if (!$screen || $screen->id !== 'tools_page_news-role-switcher') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_style('wp-admin');
    }
    
    /**
     * Render the role switcher page
     */
    public function render_page(): void {
        $current_user = wp_get_current_user();
        $original_role = get_user_meta($current_user->ID, '_original_admin_role', true);
        $is_switched = !empty($original_role);
        
        ?>
        <div class="wrap">
            <h1><?php _e('Role Switcher', 'news'); ?></h1>
            <p><?php _e('Test different user roles and capabilities without affecting your actual permissions.', 'news'); ?></p>
            
            <?php if ($is_switched): ?>
                <div class="notice notice-warning">
                    <p><strong><?php _e('Role Switch Active:', 'news'); ?></strong> 
                    <?php printf(__('You are currently testing the <strong>%s</strong> role. Click "Restore Administrator" to return to full admin access.', 'news'), ucfirst($current_user->roles[0])); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="role-switcher-container">
                <div class="role-cards">
                    <h2><?php _e('Available Roles', 'news'); ?></h2>
                    
                    <div class="role-card">
                        <h3><?php _e('Administrator', 'news'); ?></h3>
                        <p><?php _e('Full access to all WordPress and News Plugin features.', 'news'); ?></p>
                        <ul>
                            <li><?php _e('Manage all content', 'news'); ?></li>
                            <li><?php _e('Manage users and roles', 'news'); ?></li>
                            <li><?php _e('Plugin and theme management', 'news'); ?></li>
                            <li><?php _e('System settings', 'news'); ?></li>
                        </ul>
                        <?php if (!$is_switched): ?>
                            <p><em><?php _e('Current role', 'news'); ?></em></p>
                        <?php else: ?>
                            <button class="button button-primary" onclick="restoreAdminRole()">
                                <?php _e('Restore Administrator', 'news'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="role-card">
                        <h3><?php _e('Editor', 'news'); ?></h3>
                        <p><?php _e('Can edit and publish all content, manage other users\' posts.', 'news'); ?></p>
                        <ul>
                            <li><?php _e('Edit all news articles', 'news'); ?></li>
                            <li><?php _e('Publish and manage content', 'news'); ?></li>
                            <li><?php _e('Manage editorial workflow', 'news'); ?></li>
                            <li><?php _e('View editorial calendar', 'news'); ?></li>
                        </ul>
                        <button class="button" onclick="switchRole('editor')">
                            <?php _e('Switch to Editor', 'news'); ?>
                        </button>
                    </div>
                    
                    <div class="role-card">
                        <h3><?php _e('Journalist', 'news'); ?></h3>
                        <p><?php _e('Can create and edit news articles, limited to own content.', 'news'); ?></p>
                        <ul>
                            <li><?php _e('Create and edit own articles', 'news'); ?></li>
                            <li><?php _e('Upload media files', 'news'); ?></li>
                            <li><?php _e('View editorial calendar', 'news'); ?></li>
                            <li><?php _e('Manage author profile', 'news'); ?></li>
                        </ul>
                        <button class="button" onclick="switchRole('journalist')">
                            <?php _e('Switch to Journalist', 'news'); ?>
                        </button>
                    </div>
                    
                    <div class="role-card">
                        <h3><?php _e('Author', 'news'); ?></h3>
                        <p><?php _e('Can create and edit own posts, limited capabilities.', 'news'); ?></p>
                        <ul>
                            <li><?php _e('Create and edit own posts', 'news'); ?></li>
                            <li><?php _e('Upload media files', 'news'); ?></li>
                            <li><?php _e('Basic content management', 'news'); ?></li>
                        </ul>
                        <button class="button" onclick="switchRole('author')">
                            <?php _e('Switch to Author', 'news'); ?>
                        </button>
                    </div>
                    
                    <div class="role-card">
                        <h3><?php _e('Contributor', 'news'); ?></h3>
                        <p><?php _e('Can create posts but cannot publish them.', 'news'); ?></p>
                        <ul>
                            <li><?php _e('Create draft posts', 'news'); ?></li>
                            <li><?php _e('Submit for review', 'news'); ?></li>
                            <li><?php _e('Limited content access', 'news'); ?></li>
                        </ul>
                        <button class="button" onclick="switchRole('contributor')">
                            <?php _e('Switch to Contributor', 'news'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="testing-info">
                    <h2><?php _e('Testing Guidelines', 'news'); ?></h2>
                    <div class="info-box">
                        <h4><?php _e('How to Test:', 'news'); ?></h4>
                        <ol>
                            <li><?php _e('Switch to a role using the buttons above', 'news'); ?></li>
                            <li><?php _e('Navigate through the WordPress admin to see what\'s available', 'news'); ?></li>
                            <li><?php _e('Try creating, editing, and managing content', 'news'); ?></li>
                            <li><?php _e('Test the News Plugin features (Editorial Calendar, etc.)', 'news'); ?></li>
                            <li><?php _e('Return to Administrator role when done testing', 'news'); ?></li>
                        </ol>
                    </div>
                    
                    <div class="info-box">
                        <h4><?php _e('What to Look For:', 'news'); ?></h4>
                        <ul>
                            <li><?php _e('Menu items that appear/disappear', 'news'); ?></li>
                            <li><?php _e('Capabilities in different sections', 'news'); ?></li>
                            <li><?php _e('Access to News Plugin features', 'news'); ?></li>
                            <li><?php _e('Editorial workflow permissions', 'news'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .role-switcher-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .role-cards {
            display: grid;
            gap: 20px;
        }
        
        .role-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .role-card h3 {
            margin-top: 0;
            color: #0073aa;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        
        .role-card ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .role-card li {
            margin-bottom: 5px;
        }
        
        .testing-info {
            background: #f7f7f7;
            padding: 20px;
            border-radius: 8px;
        }
        
        .info-box {
            background: #fff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            border-left: 4px solid #0073aa;
        }
        
        .info-box h4 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .info-box ol, .info-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .info-box li {
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .role-switcher-container {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        function switchRole(role) {
            if (!confirm('Are you sure you want to switch to the ' + role + ' role? You can restore your administrator role at any time.')) {
                return;
            }
            
            jQuery.post(ajaxurl, {
                action: 'switch_user_role',
                role: role,
                nonce: '<?php echo wp_create_nonce('role_switch_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error switching role: ' + response.data);
                }
            });
        }
        
        function restoreAdminRole() {
            if (!confirm('Are you sure you want to restore your administrator role?')) {
                return;
            }
            
            jQuery.post(ajaxurl, {
                action: 'restore_admin_role',
                nonce: '<?php echo wp_create_nonce('role_switch_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error restoring role: ' + response.data);
                }
            });
        }
        </script>
        <?php
    }
    
    /**
     * Handle role switch AJAX request
     */
    public function handle_role_switch(): void {
        check_ajax_referer('role_switch_nonce', 'nonce');
        
        // Allow all users to switch roles - no restrictions
        
        $role = sanitize_text_field($_POST['role']);
        $user_id = get_current_user_id();
        
        // Store original role if not already stored
        if (!get_user_meta($user_id, '_original_admin_role', true)) {
            update_user_meta($user_id, '_original_admin_role', 'administrator');
        }
        
        // Switch to new role
        $user = get_userdata($user_id);
        $user->set_role($role);
        
        wp_send_json_success([
            'message' => 'Role switched to ' . $role,
            'new_role' => $role
        ]);
    }
    
    /**
     * Handle role restore AJAX request
     */
    public function handle_role_restore(): void {
        check_ajax_referer('role_switch_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        $original_role = get_user_meta($user_id, '_original_admin_role', true);
        
        if (!$original_role) {
            wp_send_json_error('No original role found');
        }
        
        // Restore original role
        $user = get_userdata($user_id);
        $user->set_role($original_role);
        
        // Clear stored original role
        delete_user_meta($user_id, '_original_admin_role');
        
        wp_send_json_success([
            'message' => 'Role restored to ' . $original_role,
            'restored_role' => $original_role
        ]);
    }
}
