<?php
/**
 * Give All Users Full Access Script
 * 
 * This script removes all capability restrictions and gives all users full access.
 * Run this from the WordPress root directory:
 * php wp-content/plugins/news/scripts/give-all-users-full-access.php
 */

// Load WordPress
require_once('../../../wp-config.php');

// Ensure we're in WordPress context
if (!function_exists('get_user_by')) {
    die('WordPress not loaded properly');
}

/**
 * Give all users full access
 */
function give_all_users_full_access() {
    echo "🔧 REMOVING ALL CAPABILITY RESTRICTIONS\n";
    echo "=====================================\n\n";
    
    // Get all users
    $users = get_users();
    echo "👥 Found " . count($users) . " users\n\n";
    
    // Define all possible capabilities
    $all_capabilities = [
        // Basic WordPress capabilities
        'read' => true,
        'upload_files' => true,
        'edit_posts' => true,
        'delete_posts' => true,
        'edit_others_posts' => true,
        'delete_others_posts' => true,
        'publish_posts' => true,
        'read_private_posts' => true,
        'edit_private_posts' => true,
        'delete_private_posts' => true,
        'edit_published_posts' => true,
        'delete_published_posts' => true,
        
        // News-specific capabilities
        'edit_news' => true,
        'edit_own_news' => true,
        'edit_others_news' => true,
        'publish_news' => true,
        'delete_news' => true,
        'delete_own_news' => true,
        'delete_others_news' => true,
        'read_news' => true,
        'read_private_news' => true,
        
        // Editorial workflow capabilities
        'edit_editorial_status' => true,
        'edit_editorial_priority' => true,
        'edit_editorial_assignee' => true,
        'edit_editorial_notes' => true,
        
        // Author profile capabilities
        'edit_author_profile' => true,
        'edit_own_author_profile' => true,
        
        // Calendar capabilities
        'view_editorial_calendar' => true,
        'edit_editorial_calendar' => true,
        
        // Section capabilities
        'edit_news_sections' => true,
        'assign_news_sections' => true,
        
        // Media capabilities
        'edit_media' => true,
        'delete_media' => true,
        
        // Comment capabilities
        'moderate_comments' => true,
        'edit_comments' => true,
        'delete_comments' => true,
        
        // Admin capabilities
        'manage_options' => true,
        'manage_users' => true,
        'manage_plugins' => true,
        'manage_themes' => true,
        'edit_users' => true,
        'delete_users' => true,
        'create_users' => true,
        'promote_users' => true,
        'activate_plugins' => true,
        'install_plugins' => true,
        'edit_plugins' => true,
        'delete_plugins' => true,
        'update_plugins' => true,
        'switch_themes' => true,
        'edit_themes' => true,
        'delete_themes' => true,
        'import' => true,
        'export' => true,
        'edit_files' => true,
    ];
    
    $updated_count = 0;
    
    foreach ($users as $user) {
        echo "👤 Processing user: {$user->user_login} (ID: {$user->ID})\n";
        
        // Give user all capabilities
        foreach ($all_capabilities as $cap => $value) {
            $user->add_cap($cap, $value);
        }
        
        // Ensure user has administrator role for maximum access
        $user->set_role('administrator');
        
        echo "✅ Granted full access to {$user->user_login}\n";
        $updated_count++;
    }
    
    echo "\n🎉 CAPABILITY RESTRICTIONS REMOVED!\n";
    echo "==================================\n";
    echo "✅ Updated {$updated_count} users\n";
    echo "✅ All users now have full access\n";
    echo "✅ No capability restrictions\n";
    echo "✅ All users can access everything\n";
    
    return true;
}

// Main execution
echo "🔧 News Plugin - Remove All Capability Restrictions\n";
echo "==================================================\n\n";

$success = give_all_users_full_access();

if ($success) {
    echo "\n✅ Success!\n";
    echo "All users now have full access to everything.\n";
    echo "No capability restrictions remain.\n";
} else {
    echo "\n❌ Failed!\n";
    echo "Please check the error messages above.\n";
}
