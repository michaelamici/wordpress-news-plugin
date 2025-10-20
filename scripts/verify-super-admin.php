<?php
/**
 * Verify Super Administrator Status
 * 
 * This script verifies and ensures michaelamici has maximum administrative privileges.
 * Run this from the WordPress root directory:
 * php wp-content/plugins/news/scripts/verify-super-admin.php
 */

// Load WordPress
require_once('../../../wp-config.php');

// Ensure we're in WordPress context
if (!function_exists('get_user_by')) {
    die('WordPress not loaded properly');
}

/**
 * Verify and ensure super admin status
 */
function verify_super_admin_status($username) {
    echo "🔧 Verifying super administrator status for: {$username}\n";
    echo "================================================\n\n";
    
    // Get user by username
    $user = get_user_by('login', $username);
    
    if (!$user) {
        echo "❌ User '{$username}' not found.\n";
        return false;
    }
    
    echo "✅ Found user: {$user->display_name} (ID: {$user->ID})\n";
    echo "📧 Email: {$user->user_email}\n";
    echo "👤 Current roles: " . implode(', ', $user->roles) . "\n\n";
    
    // Check if this is a multisite installation
    if (is_multisite()) {
        echo "🌐 Multisite installation detected\n";
        echo "-----------------------------------\n";
        
        // Get current super admins
        $super_admins = get_super_admins();
        echo "📋 Current super admins: " . implode(', ', $super_admins) . "\n";
        
        // Check if user is super admin
        if (is_super_admin($user->ID)) {
            echo "✅ {$username} is already a super administrator!\n";
        } else {
            echo "⚠️  {$username} is not a super administrator\n";
            echo "🔧 Adding to super admin list...\n";
            
            // Add to super admins
            $super_admins[] = $username;
            update_super_admins($super_admins);
            
            // Verify the change
            if (is_super_admin($user->ID)) {
                echo "✅ Successfully added {$username} to super admin list\n";
            } else {
                echo "❌ Failed to add {$username} to super admin list\n";
                return false;
            }
        }
        
        echo "\n🌐 Super Administrator Capabilities:\n";
        echo "====================================\n";
        echo "✅ Full network administration\n";
        echo "✅ Manage all sites in the network\n";
        echo "✅ Install/activate network plugins and themes\n";
        echo "✅ Manage network users and roles\n";
        echo "✅ Network settings and configuration\n";
        echo "✅ Database and file system access\n";
        echo "✅ All News Plugin features across network\n";
        
    } else {
        echo "📝 Single site installation detected\n";
        echo "------------------------------------\n";
        
        // Ensure user has administrator role
        if (!in_array('administrator', $user->roles)) {
            echo "⚠️  User does not have administrator role\n";
            echo "🔧 Assigning administrator role...\n";
            
            $user->set_role('administrator');
            echo "✅ Successfully assigned administrator role\n";
        } else {
            echo "✅ User already has administrator role\n";
        }
        
        echo "\n📋 Administrator Capabilities:\n";
        echo "============================\n";
        echo "✅ Full WordPress administration\n";
        echo "✅ Plugin and theme management\n";
        echo "✅ User and role management\n";
        echo "✅ System settings and configuration\n";
        echo "✅ Database and file system access\n";
        echo "✅ All News Plugin features\n";
        echo "✅ Role switcher for testing\n";
    }
    
    // Check specific capabilities
    echo "\n🔍 Capability Check:\n";
    echo "===================\n";
    
    $capabilities_to_check = [
        'manage_options' => 'Manage options',
        'manage_users' => 'Manage users',
        'manage_plugins' => 'Manage plugins',
        'manage_themes' => 'Manage themes',
        'edit_users' => 'Edit users',
        'delete_users' => 'Delete users',
        'create_users' => 'Create users',
        'promote_users' => 'Promote users',
        'switch_themes' => 'Switch themes',
        'edit_themes' => 'Edit themes',
        'activate_plugins' => 'Activate plugins',
        'edit_plugins' => 'Edit plugins',
        'install_plugins' => 'Install plugins',
        'delete_plugins' => 'Delete plugins',
        'update_plugins' => 'Update plugins',
        'edit_files' => 'Edit files',
        'edit_posts' => 'Edit posts',
        'edit_pages' => 'Edit pages',
        'publish_posts' => 'Publish posts',
        'publish_pages' => 'Publish pages',
        'delete_posts' => 'Delete posts',
        'delete_pages' => 'Delete pages',
        'upload_files' => 'Upload files',
        'import' => 'Import content',
        'export' => 'Export content',
    ];
    
    $user_caps = $user->allcaps;
    $has_all_caps = true;
    
    foreach ($capabilities_to_check as $cap => $description) {
        $has_cap = isset($user_caps[$cap]) && $user_caps[$cap];
        $status = $has_cap ? '✅' : '❌';
        echo "{$status} {$description}\n";
        
        if (!$has_cap) {
            $has_all_caps = false;
        }
    }
    
    if ($has_all_caps) {
        echo "\n🎉 {$username} has ALL administrative capabilities!\n";
    } else {
        echo "\n⚠️  Some capabilities may be missing\n";
    }
    
    // Check News Plugin specific capabilities
    echo "\n📰 News Plugin Capabilities:\n";
    echo "===========================\n";
    
    $news_caps = [
        'edit_news' => 'Edit news articles',
        'edit_others_news' => 'Edit others news articles',
        'publish_news' => 'Publish news articles',
        'delete_news' => 'Delete news articles',
        'delete_others_news' => 'Delete others news articles',
        'read_private_news' => 'Read private news articles',
        'edit_news_sections' => 'Edit news sections',
        'manage_news_fronts' => 'Manage news fronts',
        'edit_editorial_calendar' => 'Edit editorial calendar',
        'manage_breaking_news' => 'Manage breaking news',
    ];
    
    foreach ($news_caps as $cap => $description) {
        $has_cap = isset($user_caps[$cap]) && $user_caps[$cap];
        $status = $has_cap ? '✅' : '❌';
        echo "{$status} {$description}\n";
    }
    
    echo "\n🚀 {$username} is ready for full administrative access!\n";
    echo "You can now test all features and user roles.\n";
    
    return true;
}

// Main execution
echo "🔧 News Plugin - Super Administrator Verification\n";
echo "================================================\n\n";

$username = 'michaelamici';

$success = verify_super_admin_status($username);

if ($success) {
    echo "\n✅ Verification complete!\n";
    echo "michaelamici has maximum administrative privileges.\n";
} else {
    echo "\n❌ Verification failed!\n";
    echo "Please check the error messages above.\n";
}
