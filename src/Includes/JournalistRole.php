<?php
/**
 * Journalist Role Management
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles journalist role and capabilities
 */
class JournalistRole {
    
    /**
     * Role slug
     */
    public const ROLE_SLUG = 'journalist';
    
    /**
     * Initialize journalist role
     */
    public function __construct() {
        add_action('init', [$this, 'add_journalist_role']);
        add_action('admin_init', [$this, 'assign_journalist_to_user']);
    }
    
    /**
     * Add journalist role with appropriate capabilities
     */
    public function add_journalist_role(): void {
        // Define journalist capabilities
        $capabilities = [
            // Basic WordPress capabilities
            'read' => true,
            'upload_files' => true,
            'edit_posts' => false, // Only for news posts
            'delete_posts' => false, // Only for own news posts
            
            // News-specific capabilities
            'edit_news' => true,
            'edit_own_news' => true,
            'edit_others_news' => false,
            'publish_news' => true,
            'delete_news' => false,
            'delete_own_news' => true,
            'delete_others_news' => false,
            'read_news' => true,
            'read_private_news' => false,
            
            // Editorial workflow capabilities
            'edit_editorial_status' => true,
            'edit_editorial_priority' => true,
            'edit_editorial_assignee' => false, // Can't assign to others
            'edit_editorial_notes' => true,
            
            // Author profile capabilities
            'edit_author_profile' => true,
            'edit_own_author_profile' => true,
            
            // Calendar capabilities
            'view_editorial_calendar' => true,
            'edit_editorial_calendar' => true,
            
            // Section capabilities
            'edit_news_sections' => false,
            'assign_news_sections' => true,
            
            // Media capabilities
            'edit_media' => true,
            'delete_media' => false,
            
            // Comment capabilities (for news comments)
            'moderate_comments' => false,
            'edit_comments' => false,
            'delete_comments' => false,
        ];
        
        // Add the role if it doesn't exist
        if (!get_role(self::ROLE_SLUG)) {
            add_role(
                self::ROLE_SLUG,
                __('Journalist', 'news'),
                $capabilities
            );
        }
    }
    
    /**
     * Assign journalist role to specific user
     */
    public function assign_journalist_to_user(): void {
        $username = 'michaelamici';
        $user = get_user_by('login', $username);
        
        if ($user && !in_array(self::ROLE_SLUG, $user->roles)) {
            // Remove existing roles and assign journalist role
            $user->set_role(self::ROLE_SLUG);
            
            // Log the assignment
            error_log("Assigned journalist role to user: {$username}");
        }
    }
    
    /**
     * Get journalist capabilities
     *
     * @return array
     */
    public static function get_journalist_capabilities(): array {
        return [
            'read' => __('Read content', 'news'),
            'upload_files' => __('Upload media files', 'news'),
            'edit_news' => __('Edit news articles', 'news'),
            'edit_own_news' => __('Edit own news articles', 'news'),
            'publish_news' => __('Publish news articles', 'news'),
            'delete_own_news' => __('Delete own news articles', 'news'),
            'read_news' => __('Read news articles', 'news'),
            'edit_editorial_status' => __('Edit editorial status', 'news'),
            'edit_editorial_priority' => __('Edit editorial priority', 'news'),
            'edit_editorial_notes' => __('Edit editorial notes', 'news'),
            'edit_author_profile' => __('Edit author profile', 'news'),
            'edit_own_author_profile' => __('Edit own author profile', 'news'),
            'view_editorial_calendar' => __('View editorial calendar', 'news'),
            'edit_editorial_calendar' => __('Edit editorial calendar', 'news'),
            'assign_news_sections' => __('Assign news sections', 'news'),
            'edit_media' => __('Edit media files', 'news'),
        ];
    }
    
    /**
     * Check if user is a journalist
     *
     * @param int $user_id
     * @return bool
     */
    public static function is_journalist(int $user_id): bool {
        $user = get_userdata($user_id);
        return $user && in_array(self::ROLE_SLUG, $user->roles);
    }
    
    /**
     * Get journalist users
     *
     * @return array
     */
    public static function get_journalist_users(): array {
        return get_users([
            'role' => self::ROLE_SLUG,
            'fields' => ['ID', 'user_login', 'display_name', 'user_email'],
        ]);
    }
    
    /**
     * Add journalist-specific admin notices
     */
    public function add_journalist_notices(): void {
        $user = wp_get_current_user();
        
        if (!self::is_journalist($user->ID)) {
            return;
        }
        
        // Show journalist-specific notices
        if (get_current_screen()->id === 'edit-news') {
            echo '<div class="notice notice-info">';
            echo '<p><strong>' . __('Journalist Mode:', 'news') . '</strong> ' . 
                 __('You can create and edit news articles. Your articles will go through the editorial workflow.', 'news') . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Customize admin menu for journalists
     */
    public function customize_admin_menu(): void {
        $user = wp_get_current_user();
        
        if (!self::is_journalist($user->ID)) {
            return;
        }
        
        // Remove menu items journalists shouldn't see
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
        remove_menu_page('plugins.php');
        remove_menu_page('themes.php');
        remove_menu_page('users.php');
        
        // Add journalist-specific menu items
        add_menu_page(
            __('My Articles', 'news'),
            __('My Articles', 'news'),
            'edit_news',
            'my-articles',
            [$this, 'render_my_articles_page'],
            'dashicons-media-document',
            5
        );
    }
    
    /**
     * Render my articles page
     */
    public function render_my_articles_page(): void {
        $user = wp_get_current_user();
        
        $posts = get_posts([
            'post_type' => 'news',
            'author' => $user->ID,
            'posts_per_page' => 20,
            'post_status' => ['draft', 'pending', 'publish'],
        ]);
        
        ?>
        <div class="wrap">
            <h1><?php _e('My Articles', 'news'); ?></h1>
            
            <div class="journalist-dashboard">
                <div class="dashboard-stats">
                    <?php
                    $stats = [
                        'total' => count($posts),
                        'published' => count(array_filter($posts, function($post) { return $post->post_status === 'publish'; })),
                        'draft' => count(array_filter($posts, function($post) { return $post->post_status === 'draft'; })),
                        'pending' => count(array_filter($posts, function($post) { return $post->post_status === 'pending'; })),
                    ];
                    ?>
                    
                    <div class="stat-box">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p><?php _e('Total Articles', 'news'); ?></p>
                    </div>
                    
                    <div class="stat-box">
                        <h3><?php echo $stats['published']; ?></h3>
                        <p><?php _e('Published', 'news'); ?></p>
                    </div>
                    
                    <div class="stat-box">
                        <h3><?php echo $stats['draft']; ?></h3>
                        <p><?php _e('Drafts', 'news'); ?></p>
                    </div>
                    
                    <div class="stat-box">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p><?php _e('Pending Review', 'news'); ?></p>
                    </div>
                </div>
                
                <div class="articles-list">
                    <h2><?php _e('Recent Articles', 'news'); ?></h2>
                    
                    <?php if (empty($posts)): ?>
                        <p><?php _e('No articles found.', 'news'); ?></p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Title', 'news'); ?></th>
                                    <th><?php _e('Status', 'news'); ?></th>
                                    <th><?php _e('Date', 'news'); ?></th>
                                    <th><?php _e('Actions', 'news'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <a href="<?php echo get_edit_post_link($post->ID); ?>">
                                                    <?php echo esc_html($post->post_title); ?>
                                                </a>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="post-status status-<?php echo $post->post_status; ?>">
                                                <?php echo ucfirst($post->post_status); ?>
                                            </span>
                                        </td>
                                        <td><?php echo get_the_date('M j, Y', $post); ?></td>
                                        <td>
                                            <a href="<?php echo get_edit_post_link($post->ID); ?>" class="button button-small">
                                                <?php _e('Edit', 'news'); ?>
                                            </a>
                                            <?php if ($post->post_status === 'publish'): ?>
                                                <a href="<?php echo get_permalink($post->ID); ?>" class="button button-small" target="_blank">
                                                    <?php _e('View', 'news'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
        .journalist-dashboard {
            margin-top: 20px;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: #f7f7f7;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
        }
        
        .stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 32px;
            color: #0073aa;
        }
        
        .stat-box p {
            margin: 0;
            color: #666;
            font-weight: 500;
        }
        
        .post-status {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-publish {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #f8f9fa;
            color: #6c757d;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        </style>
        <?php
    }
}
