<?php
/**
 * Article Meta Box Template
 * 
 * @package NewsPlugin
 * @subpackage Templates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current meta values - now using individual meta fields
$featured = get_post_meta($post->ID, '_news_featured', true);
$breaking = get_post_meta($post->ID, '_news_breaking', true);
$exclusive = get_post_meta($post->ID, '_news_exclusive', true);
$sponsored = get_post_meta($post->ID, '_news_sponsored', true);
$is_live = get_post_meta($post->ID, '_news_is_live', true);
$last_updated = get_post_meta($post->ID, '_news_last_updated', true);
$byline = get_post_meta($post->ID, '_news_byline', true);

// Get nonce for security
wp_nonce_field('news_article_meta', 'news_article_meta_nonce');
?>

<div class="news-meta-box">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="news_featured"><?php esc_html_e('Featured Article', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_featured" name="news_featured" value="1" <?php checked($featured, true); ?> />
                <label for="news_featured"><?php esc_html_e('Mark as featured article', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Featured articles appear prominently on the homepage and section fronts.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_breaking"><?php esc_html_e('Breaking News', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_breaking" name="news_breaking" value="1" <?php checked($breaking, true); ?> />
                <label for="news_breaking"><?php esc_html_e('Mark as breaking news', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Breaking news appears in the breaking news ticker and gets priority placement.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_exclusive"><?php esc_html_e('Exclusive Content', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_exclusive" name="news_exclusive" value="1" <?php checked($exclusive, true); ?> />
                <label for="news_exclusive"><?php esc_html_e('Mark as exclusive content', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Exclusive content is marked with special badges and gets priority in feeds.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_sponsored"><?php esc_html_e('Sponsored Content', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_sponsored" name="news_sponsored" value="1" <?php checked($sponsored, true); ?> />
                <label for="news_sponsored"><?php esc_html_e('Mark as sponsored content', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Sponsored content is clearly marked and may have different display rules.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_is_live"><?php esc_html_e('Live Content', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_is_live" name="news_is_live" value="1" <?php checked($is_live, true); ?> />
                <label for="news_is_live"><?php esc_html_e('Mark as live content', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Live content is marked with special badges and gets priority placement.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_last_updated"><?php esc_html_e('Last Updated', 'news'); ?></label>
            </th>
            <td>
                <input type="datetime-local" id="news_last_updated" name="news_last_updated" value="<?php echo esc_attr($last_updated); ?>" />
                <p class="description"><?php esc_html_e('Manually set the last updated date. Leave empty for automatic updates.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_byline"><?php esc_html_e('Byline', 'news'); ?></label>
            </th>
            <td>
                <input type="text" id="news_byline" name="news_byline" value="<?php echo esc_attr($byline); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e('Article byline (e.g., "By John Smith" or "Staff Writer"). Leave empty to use author display name.', 'news'); ?></p>
            </td>
        </tr>
    </table>
</div>

<style>
.news-meta-box .form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
    vertical-align: top;
}

.news-meta-box .form-table td {
    padding: 15px 10px;
}

.news-meta-box .description {
    margin-top: 5px;
    font-style: italic;
    color: #666;
}

.news-meta-box input[type="checkbox"] {
    margin-right: 8px;
}
</style>
