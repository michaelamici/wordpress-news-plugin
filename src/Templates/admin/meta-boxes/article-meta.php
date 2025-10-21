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

// Get current meta values
$meta = get_post_meta($post->ID, '_news_article_meta', true);
$meta = wp_parse_args($meta, [
    'featured' => false,
    'breaking' => false,
    'exclusive' => false,
    'sponsored' => false,
    'top_story' => false,
    'sticky' => false,
]);

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
                <input type="checkbox" id="news_featured" name="news_meta[featured]" value="1" <?php checked($meta['featured'], true); ?> />
                <label for="news_featured"><?php esc_html_e('Mark as featured article', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Featured articles appear prominently on the homepage and section fronts.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_breaking"><?php esc_html_e('Breaking News', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_breaking" name="news_meta[breaking]" value="1" <?php checked($meta['breaking'], true); ?> />
                <label for="news_breaking"><?php esc_html_e('Mark as breaking news', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Breaking news appears in the breaking news ticker and gets priority placement.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_exclusive"><?php esc_html_e('Exclusive Content', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_exclusive" name="news_meta[exclusive]" value="1" <?php checked($meta['exclusive'], true); ?> />
                <label for="news_exclusive"><?php esc_html_e('Mark as exclusive content', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Exclusive content is marked with special badges and gets priority in feeds.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_sponsored"><?php esc_html_e('Sponsored Content', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_sponsored" name="news_meta[sponsored]" value="1" <?php checked($meta['sponsored'], true); ?> />
                <label for="news_sponsored"><?php esc_html_e('Mark as sponsored content', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Sponsored content is clearly marked and may have different display rules.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_top_story"><?php esc_html_e('Top Story', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_top_story" name="news_meta[top_story]" value="1" <?php checked($meta['top_story'], true); ?> />
                <label for="news_top_story"><?php esc_html_e('Mark as top story', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Top stories get priority placement in news feeds and section fronts.', 'news'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="news_sticky"><?php esc_html_e('Sticky Post', 'news'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="news_sticky" name="news_meta[sticky]" value="1" <?php checked($meta['sticky'], true); ?> />
                <label for="news_sticky"><?php esc_html_e('Keep this post at the top of lists', 'news'); ?></label>
                <p class="description"><?php esc_html_e('Sticky posts appear at the top of news lists and feeds.', 'news'); ?></p>
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
