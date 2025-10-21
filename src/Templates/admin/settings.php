<?php
/**
 * News Settings Template
 * 
 * @package NewsPlugin
 * @subpackage Templates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['news_settings_submit']) && wp_verify_nonce($_POST['news_settings_nonce'], 'news_settings')) {
    $settings = [
        'front_page_layout' => sanitize_text_field($_POST['front_page_layout'] ?? 'grid'),
        'articles_per_page' => intval($_POST['articles_per_page'] ?? 10),
        'enable_breaking_news' => isset($_POST['enable_breaking_news']),
        'breaking_news_count' => intval($_POST['breaking_news_count'] ?? 3),
        'enable_comments' => isset($_POST['enable_comments']),
        'enable_sharing' => isset($_POST['enable_sharing']),
        'default_section' => intval($_POST['default_section'] ?? 0),
    ];
    
    update_option('news_settings', $settings);
    echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully!', 'news') . '</p></div>';
}

// Get current settings
$settings = get_option('news_settings', [
    'front_page_layout' => 'grid',
    'articles_per_page' => 10,
    'enable_breaking_news' => true,
    'breaking_news_count' => 3,
    'enable_comments' => false,
    'enable_sharing' => true,
    'default_section' => 0,
]);

// Get sections for dropdown
$sections = get_terms([
    'taxonomy' => 'news_section',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
]);
?>

<div class="wrap">
    <h1><?php esc_html_e('News Settings', 'news'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('news_settings', 'news_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="front_page_layout"><?php esc_html_e('Front Page Layout', 'news'); ?></label>
                </th>
                <td>
                    <select id="front_page_layout" name="front_page_layout">
                        <option value="grid" <?php selected($settings['front_page_layout'], 'grid'); ?>>
                            <?php esc_html_e('Grid Layout', 'news'); ?>
                        </option>
                        <option value="list" <?php selected($settings['front_page_layout'], 'list'); ?>>
                            <?php esc_html_e('List Layout', 'news'); ?>
                        </option>
                        <option value="magazine" <?php selected($settings['front_page_layout'], 'magazine'); ?>>
                            <?php esc_html_e('Magazine Layout', 'news'); ?>
                        </option>
                    </select>
                    <p class="description"><?php esc_html_e('Choose how articles are displayed on the front page.', 'news'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="articles_per_page"><?php esc_html_e('Articles Per Page', 'news'); ?></label>
                </th>
                <td>
                    <input type="number" id="articles_per_page" name="articles_per_page" 
                           value="<?php echo esc_attr($settings['articles_per_page']); ?>" 
                           min="1" max="50" class="small-text" />
                    <p class="description"><?php esc_html_e('Number of articles to show per page in news feeds.', 'news'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="enable_breaking_news"><?php esc_html_e('Enable Breaking News', 'news'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="enable_breaking_news" name="enable_breaking_news" 
                           value="1" <?php checked($settings['enable_breaking_news']); ?> />
                    <label for="enable_breaking_news"><?php esc_html_e('Show breaking news ticker', 'news'); ?></label>
                    <p class="description"><?php esc_html_e('Display a scrolling ticker for breaking news articles.', 'news'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="breaking_news_count"><?php esc_html_e('Breaking News Count', 'news'); ?></label>
                </th>
                <td>
                    <input type="number" id="breaking_news_count" name="breaking_news_count" 
                           value="<?php echo esc_attr($settings['breaking_news_count']); ?>" 
                           min="1" max="10" class="small-text" />
                    <p class="description"><?php esc_html_e('Number of breaking news items to show in the ticker.', 'news'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="enable_comments"><?php esc_html_e('Enable Comments', 'news'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="enable_comments" name="enable_comments" 
                           value="1" <?php checked($settings['enable_comments']); ?> />
                    <label for="enable_comments"><?php esc_html_e('Allow comments on news articles', 'news'); ?></label>
                    <p class="description"><?php esc_html_e('Enable or disable comments for news articles.', 'news'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="enable_sharing"><?php esc_html_e('Enable Social Sharing', 'news'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="enable_sharing" name="enable_sharing" 
                           value="1" <?php checked($settings['enable_sharing']); ?> />
                    <label for="enable_sharing"><?php esc_html_e('Show social sharing buttons', 'news'); ?></label>
                    <p class="description"><?php esc_html_e('Display social sharing buttons on news articles.', 'news'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="default_section"><?php esc_html_e('Default Section', 'news'); ?></label>
                </th>
                <td>
                    <select id="default_section" name="default_section">
                        <option value="0"><?php esc_html_e('No Default Section', 'news'); ?></option>
                        <?php if (!empty($sections) && !is_wp_error($sections)): ?>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo esc_attr($section->term_id); ?>" 
                                        <?php selected($settings['default_section'], $section->term_id); ?>>
                                    <?php echo esc_html($section->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Default section for new articles.', 'news'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Save Settings', 'news'), 'primary', 'news_settings_submit'); ?>
    </form>
</div>

<style>
.form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
    vertical-align: top;
}

.form-table td {
    padding: 15px 10px;
}

.form-table .description {
    margin-top: 5px;
    font-style: italic;
    color: #666;
}

.form-table input[type="checkbox"] {
    margin-right: 8px;
}

.form-table input[type="number"] {
    width: 80px;
}
</style>
