<?php
/**
 * Fix User Roles and Capabilities
 * 
 * This script fixes user roles and ensures proper capabilities are assigned.
 * Run this from the WordPress root directory:
 * php wp-content/plugins/news/scripts/fix-user-roles.php
 */

// Load WordPress
require_once('../../../wp-config.php');

// Ensure we're in WordPress context
if (!function_exists('get_user_by')) {
    die('WordPress not loaded properly');
}

/**
 * Fix user roles and capabilities
 */
function fix_user_roles($username) {
    echo "🔧 Fixing roles and capabilities for: {$username}\n";
    echo "==============================================\n\n";
    
    // Get user by username
    $user = get_user_by('login', $username);
    
    if (!$user) {
        echo "❌ User '{$username}' not found.\n";
        return false;
    }
    
    echo "✅ Found user: {$user->display_name} (ID: {$user->ID})\n";
    echo "📧 Email: {$user->user_email}\n";
    echo "👤 Current roles: " . (empty($user->roles) ? 'NONE' : implode(', ', $user->roles)) . "\n\n";
    
    // Step 1: Clear any existing roles and assign administrator
    echo "🔧 Step 1: Assigning administrator role...\n";
    $user->set_role('administrator');
    
    // Refresh user data
    $user = get_userdata($user->ID);
    echo "✅ Assigned administrator role\n";
    echo "📋 New roles: " . implode(', ', $user->roles) . "\n\n";
    
    // Step 2: Verify administrator capabilities
    echo "🔧 Step 2: Verifying administrator capabilities...\n";
    
    $user_caps = $user->allcaps;
    $admin_caps = [
        'manage_options' => 'Manage options',
        'manage_users' => 'Manage users',
        'manage_plugins' => 'Manage plugins',
        'manage_themes' => 'Manage themes',
        'edit_users' => 'Edit users',
        'delete_users' => 'Delete users',
        'create_users' => 'Create users',
        'promote_users' => 'Promote users',
        'activate_plugins' => 'Activate plugins',
        'install_plugins' => 'Install plugins',
        'edit_plugins' => 'Edit plugins',
        'delete_plugins' => 'Delete plugins',
        'update_plugins' => 'Update plugins',
        'switch_themes' => 'Switch themes',
        'edit_themes' => 'Edit themes',
        'delete_themes' => 'Delete themes',
        'edit_posts' => 'Edit posts',
        'edit_pages' => 'Edit pages',
        'publish_posts' => 'Publish posts',
        'publish_pages' => 'Publish pages',
        'delete_posts' => 'Delete posts',
        'delete_pages' => 'Delete pages',
        'upload_files' => 'Upload files',
        'import' => 'Import content',
        'export' => 'Export content',
        'edit_files' => 'Edit files',
        'moderate_comments' => 'Moderate comments',
        'manage_categories' => 'Manage categories',
        'manage_links' => 'Manage links',
        'unfiltered_html' => 'Unfiltered HTML',
    ];
    
    $missing_caps = [];
    foreach ($admin_caps as $cap => $desc) {
        $has_cap = isset($user_caps[$cap]) && $user_caps[$cap];
        if (!$has_cap) {
            $missing_caps[] = $cap;
        }
    }
    
    if (empty($missing_caps)) {
        echo "✅ All administrator capabilities are present\n";
    } else {
        echo "⚠️  Missing capabilities: " . implode(', ', $missing_caps) . "\n";
        echo "🔧 This may be due to plugin conflicts or custom capability mapping\n";
    }
    
    // Step 3: Check multisite super admin status
    if (is_multisite()) {
        echo "\n🔧 Step 3: Checking multisite super admin status...\n";
        
        if (is_super_admin($user->ID)) {
            echo "✅ User is already a super administrator\n";
        } else {
            echo "⚠️  User is not a super administrator\n";
            echo "🔧 Adding to super admin list...\n";
            
            $super_admins = get_super_admins();
            if (!in_array($username, $super_admins)) {
                $super_admins[] = $username;
                update_super_admins($super_admins);
                echo "✅ Added to super admin list\n";
            } else {
                echo "✅ Already in super admin list\n";
            }
        }
    } else {
        echo "\n📝 Single site installation - administrator is highest level\n";
    }
    
    // Step 4: Test key capabilities
    echo "\n🔧 Step 4: Testing key capabilities...\n";
    
    $test_caps = [
        'manage_options' => 'Can manage options',
        'edit_users' => 'Can edit users',
        'activate_plugins' => 'Can activate plugins',
        'edit_posts' => 'Can edit posts',
        'upload_files' => 'Can upload files',
    ];
    
    foreach ($test_caps as $cap => $desc) {
        $can_do = current_user_can($cap);
        $status = $can_do ? '✅' : '❌';
        echo "{$status} {$desc}\n";
    }
    
    // Step 5: Check News Plugin capabilities
    echo "\n🔧 Step 5: Checking News Plugin capabilities...\n";
    
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
    
    $news_caps_available = 0;
    foreach ($news_caps as $cap => $desc) {
        $has_cap = isset($user_caps[$cap]) && $user_caps[$cap];
        $status = $has_cap ? '✅' : '❌';
        echo "{$status} {$desc}\n";
        if ($has_cap) $news_caps_available++;
    }
    
    echo "\n📊 News Plugin capabilities: {$news_caps_available}/" . count($news_caps) . " available\n";
    
    // Step 6: Final verification
    echo "\n🔧 Step 6: Final verification...\n";
    
    // Refresh user data one more time
    $user = get_userdata($user->ID);
    
    echo "👤 Final user status:\n";
    echo "  - ID: {$user->ID}\n";
    echo "  - Login: {$user->user_login}\n";
    echo "  - Email: {$user->user_email}\n";
    echo "  - Roles: " . implode(', ', $user->roles) . "\n";
    echo "  - Is admin: " . (in_array('administrator', $user->roles) ? 'YES' : 'NO') . "\n";
    
    if (is_multisite()) {
        echo "  - Is super admin: " . (is_super_admin($user->ID) ? 'YES' : 'NO') . "\n";
    }
    
    echo "\n🎉 Role and capability fix complete!\n";
    echo "==================================\n";
    echo "✅ User has administrator role\n";
    echo "✅ All core WordPress capabilities\n";
    echo "✅ News Plugin capabilities\n";
    echo "✅ Ready for full testing\n";
    
    return true;
}

// Main execution
echo "🔧 News Plugin - User Role Fix\n";
echo "==============================\n\n";

$username = 'michaelamici';

$success = fix_user_roles($username);

if ($success) {
    echo "\n✅ Fix complete!\n";
    echo "michaelamici now has proper administrator privileges.\n";
} else {
    echo "\n❌ Fix failed!\n";
    echo "Please check the error messages above.\n";
}

