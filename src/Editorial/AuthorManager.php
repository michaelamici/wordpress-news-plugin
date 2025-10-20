<?php
/**
 * Author Management for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Editorial;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles author and contributor management
 */
class AuthorManager {
    
    /**
     * Initialize author management
     */
    public function __construct() {
        add_action('init', [$this, 'register_user_meta']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('show_user_profile', [$this, 'add_author_fields']);
        add_action('edit_user_profile', [$this, 'add_author_fields']);
        add_action('personal_options_update', [$this, 'save_author_fields']);
        add_action('edit_user_profile_update', [$this, 'save_author_fields']);
    }
    
    /**
     * Register user meta for authors
     */
    public function register_user_meta(): void {
        register_meta('user', 'author_bio', [
            'type' => 'string',
            'default' => '',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_textarea_field',
            'description' => __('Author biography', 'news'),
        ]);
        
        register_meta('user', 'author_title', [
            'type' => 'string',
            'default' => '',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'description' => __('Author title/position', 'news'),
        ]);
        
        register_meta('user', 'author_specialties', [
            'type' => 'array',
            'default' => [],
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => [$this, 'sanitize_specialties'],
            'description' => __('Author specialties/beats', 'news'),
        ]);
        
        register_meta('user', 'author_social_links', [
            'type' => 'object',
            'default' => [],
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => [$this, 'sanitize_social_links'],
            'description' => __('Author social media links', 'news'),
        ]);
        
        register_meta('user', 'author_photo', [
            'type' => 'integer',
            'default' => 0,
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'absint',
            'description' => __('Author profile photo', 'news'),
        ]);
        
        register_meta('user', 'author_contact_info', [
            'type' => 'object',
            'default' => [],
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => [$this, 'sanitize_contact_info'],
            'description' => __('Author contact information', 'news'),
        ]);
    }
    
    /**
     * Register REST API routes for authors
     */
    public function register_rest_routes(): void {
        register_rest_route('news/v1', '/authors', [
            'methods' => 'GET',
            'callback' => [$this, 'get_authors'],
            'permission_callback' => '__return_true',
        ]);
        
        register_rest_route('news/v1', '/authors/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_author'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
        
        register_rest_route('news/v1', '/authors/(?P<id>\d+)/articles', [
            'methods' => 'GET',
            'callback' => [$this, 'get_author_articles'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'default' => 10,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'page' => [
                    'default' => 1,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }
    
    /**
     * Get all authors
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_authors(\WP_REST_Request $request): \WP_REST_Response {
        $per_page = $request->get_param('per_page') ?: 20;
        $page = $request->get_param('page') ?: 1;
        $specialty = $request->get_param('specialty');
        
        $args = [
            'role' => 'author',
            'number' => $per_page,
            'offset' => ($page - 1) * $per_page,
            'meta_query' => [],
        ];
        
        if ($specialty) {
            $args['meta_query'][] = [
                'key' => 'author_specialties',
                'value' => $specialty,
                'compare' => 'LIKE',
            ];
        }
        
        $users = get_users($args);
        $authors = [];
        
        foreach ($users as $user) {
            $authors[] = $this->format_author_data($user);
        }
        
        return rest_ensure_response([
            'authors' => $authors,
            'total' => count($authors),
            'page' => $page,
            'per_page' => $per_page,
        ]);
    }
    
    /**
     * Get single author
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_author(\WP_REST_Request $request) {
        $author_id = $request->get_param('id');
        $user = get_userdata($author_id);
        
        if (!$user || !in_array('author', $user->roles)) {
            return new \WP_Error('author_not_found', __('Author not found', 'news'), ['status' => 404]);
        }
        
        return rest_ensure_response($this->format_author_data($user));
    }
    
    /**
     * Get author articles
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_author_articles(\WP_REST_Request $request): \WP_REST_Response {
        $author_id = $request->get_param('id');
        $per_page = $request->get_param('per_page') ?: 10;
        $page = $request->get_param('page') ?: 1;
        
        $posts = get_posts([
            'post_type' => 'news',
            'author' => $author_id,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
        ]);
        
        $articles = [];
        
        foreach ($posts as $post) {
            $articles[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'excerpt' => $post->post_excerpt,
                'date' => $post->post_date,
                'url' => get_permalink($post->ID),
                'featured_image' => get_the_post_thumbnail_url($post->ID, 'medium'),
            ];
        }
        
        return rest_ensure_response([
            'articles' => $articles,
            'total' => count($articles),
            'page' => $page,
            'per_page' => $per_page,
        ]);
    }
    
    /**
     * Format author data for API
     *
     * @param \WP_User $user
     * @return array
     */
    private function format_author_data(\WP_User $user): array {
        $bio = get_user_meta($user->ID, 'author_bio', true);
        $title = get_user_meta($user->ID, 'author_title', true);
        $specialties = get_user_meta($user->ID, 'author_specialties', true) ?: [];
        $social_links = get_user_meta($user->ID, 'author_social_links', true) ?: [];
        $photo_id = get_user_meta($user->ID, 'author_photo', true);
        $contact_info = get_user_meta($user->ID, 'author_contact_info', true) ?: [];
        
        return [
            'id' => $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'bio' => $bio,
            'title' => $title,
            'specialties' => $specialties,
            'social_links' => $social_links,
            'photo' => $photo_id ? wp_get_attachment_url($photo_id) : null,
            'contact_info' => $contact_info,
            'url' => get_author_posts_url($user->ID),
        ];
    }
    
    /**
     * Add author fields to user profile
     *
     * @param \WP_User $user
     */
    public function add_author_fields(\WP_User $user): void {
        if (!in_array('author', $user->roles)) {
            return;
        }
        
        $bio = get_user_meta($user->ID, 'author_bio', true);
        $title = get_user_meta($user->ID, 'author_title', true);
        $specialties = get_user_meta($user->ID, 'author_specialties', true) ?: [];
        $social_links = get_user_meta($user->ID, 'author_social_links', true) ?: [];
        $photo_id = get_user_meta($user->ID, 'author_photo', true);
        $contact_info = get_user_meta($user->ID, 'author_contact_info', true) ?: [];
        ?>
        <h3><?php _e('Author Information', 'news'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="author_bio"><?php _e('Biography', 'news'); ?></label></th>
                <td>
                    <textarea name="author_bio" id="author_bio" rows="5" cols="30"><?php echo esc_textarea($bio); ?></textarea>
                    <p class="description"><?php _e('Brief biography for author profile', 'news'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="author_title"><?php _e('Title/Position', 'news'); ?></label></th>
                <td>
                    <input type="text" name="author_title" id="author_title" value="<?php echo esc_attr($title); ?>" class="regular-text" />
                    <p class="description"><?php _e('Author title or position', 'news'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="author_specialties"><?php _e('Specialties', 'news'); ?></label></th>
                <td>
                    <input type="text" name="author_specialties" id="author_specialties" value="<?php echo esc_attr(implode(', ', $specialties)); ?>" class="regular-text" />
                    <p class="description"><?php _e('Comma-separated list of specialties or beats', 'news'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save author fields
     *
     * @param int $user_id
     */
    public function save_author_fields(int $user_id): void {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        
        if (isset($_POST['author_bio'])) {
            update_user_meta($user_id, 'author_bio', sanitize_textarea_field($_POST['author_bio']));
        }
        
        if (isset($_POST['author_title'])) {
            update_user_meta($user_id, 'author_title', sanitize_text_field($_POST['author_title']));
        }
        
        if (isset($_POST['author_specialties'])) {
            $specialties = array_map('trim', explode(',', $_POST['author_specialties']));
            update_user_meta($user_id, 'author_specialties', $specialties);
        }
    }
    
    /**
     * Sanitize specialties array
     *
     * @param mixed $value
     * @return array
     */
    public function sanitize_specialties($value): array {
        if (!is_array($value)) {
            return [];
        }
        
        return array_map('sanitize_text_field', $value);
    }
    
    /**
     * Sanitize social links object
     *
     * @param mixed $value
     * @return array
     */
    public function sanitize_social_links($value): array {
        if (!is_array($value)) {
            return [];
        }
        
        $allowed_keys = ['twitter', 'facebook', 'linkedin', 'instagram'];
        $sanitized = [];
        
        foreach ($allowed_keys as $key) {
            if (isset($value[$key])) {
                $sanitized[$key] = esc_url_raw($value[$key]);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize contact info object
     *
     * @param mixed $value
     * @return array
     */
    public function sanitize_contact_info($value): array {
        if (!is_array($value)) {
            return [];
        }
        
        $allowed_keys = ['phone', 'email', 'office'];
        $sanitized = [];
        
        foreach ($allowed_keys as $key) {
            if (isset($value[$key])) {
                $sanitized[$key] = sanitize_text_field($value[$key]);
            }
        }
        
        return $sanitized;
    }
}
