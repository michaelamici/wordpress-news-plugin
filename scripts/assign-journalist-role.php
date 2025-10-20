<?php
/**
 * Assign Journalist Role Script
 * 
 * This script assigns the journalist role to a specific user.
 * Run this from the WordPress root directory:
 * php wp-content/plugins/news/scripts/assign-journalist-role.php
 */

// Load WordPress
require_once('../../../wp-config.php');

// Ensure we're in WordPress context
if (!function_exists('get_user_by')) {
    die('WordPress not loaded properly');
}

/**
 * Assign journalist role to user
 */
function assign_journalist_role($username) {
    // Get user by username
    $user = get_user_by('login', $username);
    
    if (!$user) {
        echo "❌ User '{$username}' not found.\n";
        return false;
    }
    
    echo "✅ Found user: {$user->display_name} ({$user->user_email})\n";
    
    // Check if journalist role exists
    if (!get_role('journalist')) {
        echo "❌ Journalist role does not exist. Please activate the News Plugin first.\n";
        return false;
    }
    
    // Assign journalist role
    $user->set_role('journalist');
    
    echo "✅ Successfully assigned journalist role to '{$username}'\n";
    
    // Display user's new capabilities
    $user = get_user_by('login', $username); // Refresh user data
    $caps = $user->allcaps;
    
    echo "\n📋 Journalist Capabilities:\n";
    echo "========================\n";
    
    $journalist_caps = [
        'read' => 'Read content',
        'upload_files' => 'Upload media files',
        'edit_news' => 'Edit news articles',
        'edit_own_news' => 'Edit own news articles',
        'publish_news' => 'Publish news articles',
        'delete_own_news' => 'Delete own news articles',
        'read_news' => 'Read news articles',
        'edit_editorial_status' => 'Edit editorial status',
        'edit_editorial_priority' => 'Edit editorial priority',
        'edit_editorial_notes' => 'Edit editorial notes',
        'edit_author_profile' => 'Edit author profile',
        'edit_own_author_profile' => 'Edit own author profile',
        'view_editorial_calendar' => 'View editorial calendar',
        'edit_editorial_calendar' => 'Edit editorial calendar',
        'assign_news_sections' => 'Assign news sections',
        'edit_media' => 'Edit media files',
    ];
    
    foreach ($journalist_caps as $cap => $description) {
        $status = isset($caps[$cap]) && $caps[$cap] ? '✅' : '❌';
        echo "{$status} {$description}\n";
    }
    
    echo "\n🎉 Journalist role assignment complete!\n";
    echo "User '{$username}' can now:\n";
    echo "- Create and edit news articles\n";
    echo "- Upload media files\n";
    echo "- View and edit editorial calendar\n";
    echo "- Manage their author profile\n";
    echo "- Access journalist dashboard\n";
    
    return true;
}

// Main execution
echo "🔧 News Plugin - Journalist Role Assignment\n";
echo "==========================================\n\n";

$username = 'michaelamici';

echo "Assigning journalist role to user: {$username}\n";
echo "--------------------------------------------\n";

$success = assign_journalist_role($username);

if ($success) {
    echo "\n✅ Role assignment successful!\n";
    echo "User can now log in and access journalist features.\n";
} else {
    echo "\n❌ Role assignment failed!\n";
    echo "Please check the error messages above.\n";
}
