<?php
/**
 * News Analytics Template
 * 
 * @package NewsPlugin
 * @subpackage Templates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get analytics data
$total_articles = wp_count_posts('news');
$total_sections = wp_count_terms('news_section');
$recent_articles = get_posts([
    'post_type' => 'news',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
]);

// Get popular articles (by comment count as a proxy for popularity)
$popular_articles = get_posts([
    'post_type' => 'news',
    'posts_per_page' => 5,
    'post_status' => 'publish',
    'orderby' => 'comment_count',
    'order' => 'DESC',
    'meta_query' => [
        [
            'key' => '_news_article_meta',
            'value' => 'featured',
            'compare' => 'LIKE'
        ]
    ]
]);

// Get section statistics
$sections = get_terms([
    'taxonomy' => 'news_section',
    'hide_empty' => false,
    'orderby' => 'count',
    'order' => 'DESC'
]);
?>

<div class="wrap">
    <h1><?php esc_html_e('News Analytics', 'news'); ?></h1>
    
    <div class="news-analytics-overview">
        <div class="news-stat-card">
            <h3><?php esc_html_e('Total Articles', 'news'); ?></h3>
            <div class="news-stat-number"><?php echo esc_html($total_articles->publish ?? 0); ?></div>
            <div class="news-stat-label"><?php esc_html_e('Published', 'news'); ?></div>
        </div>
        
        <div class="news-stat-card">
            <h3><?php esc_html_e('Draft Articles', 'news'); ?></h3>
            <div class="news-stat-number"><?php echo esc_html($total_articles->draft ?? 0); ?></div>
            <div class="news-stat-label"><?php esc_html_e('In Progress', 'news'); ?></div>
        </div>
        
        <div class="news-stat-card">
            <h3><?php esc_html_e('News Sections', 'news'); ?></h3>
            <div class="news-stat-number"><?php echo esc_html($total_sections); ?></div>
            <div class="news-stat-label"><?php esc_html_e('Active Sections', 'news'); ?></div>
        </div>
        
        <div class="news-stat-card">
            <h3><?php esc_html_e('Featured Articles', 'news'); ?></h3>
            <div class="news-stat-number"><?php echo esc_html(count($popular_articles)); ?></div>
            <div class="news-stat-label"><?php esc_html_e('Currently Featured', 'news'); ?></div>
        </div>
    </div>
    
    <div class="news-analytics-content">
        <div class="news-section-stats">
            <h2><?php esc_html_e('Section Statistics', 'news'); ?></h2>
            <?php if (!empty($sections) && !is_wp_error($sections)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Section', 'news'); ?></th>
                            <th><?php esc_html_e('Articles', 'news'); ?></th>
                            <th><?php esc_html_e('Percentage', 'news'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_article_count = array_sum(wp_list_pluck($sections, 'count'));
                        foreach ($sections as $section): 
                            $percentage = $total_article_count > 0 ? round(($section->count / $total_article_count) * 100, 1) : 0;
                        ?>
                            <tr>
                                <td><?php echo esc_html($section->name); ?></td>
                                <td><?php echo esc_html($section->count); ?></td>
                                <td>
                                    <div class="news-percentage-bar">
                                        <div class="news-percentage-fill" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                                        <span class="news-percentage-text"><?php echo esc_html($percentage); ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php esc_html_e('No sections found.', 'news'); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="news-recent-activity">
            <h2><?php esc_html_e('Recent Activity', 'news'); ?></h2>
            <?php if (!empty($recent_articles)): ?>
                <ul class="news-activity-list">
                    <?php foreach ($recent_articles as $article): ?>
                        <li>
                            <a href="<?php echo esc_url(get_edit_post_link($article->ID)); ?>">
                                <?php echo esc_html($article->post_title); ?>
                            </a>
                            <span class="news-activity-date">
                                <?php echo esc_html(human_time_diff(get_the_time('U', $article->ID), current_time('timestamp')) . ' ago'); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php esc_html_e('No recent activity.', 'news'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.news-analytics-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.news-stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.news-stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.news-stat-number {
    font-size: 36px;
    font-weight: bold;
    color: #0073aa;
    margin: 0;
    line-height: 1;
}

.news-stat-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.news-analytics-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-top: 30px;
}

.news-section-stats,
.news-recent-activity {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.news-percentage-bar {
    position: relative;
    background: #f0f0f0;
    border-radius: 3px;
    height: 20px;
    overflow: hidden;
}

.news-percentage-fill {
    background: #0073aa;
    height: 100%;
    transition: width 0.3s ease;
}

.news-percentage-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 12px;
    font-weight: 500;
    color: #333;
}

.news-activity-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.news-activity-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.news-activity-list li:last-child {
    border-bottom: none;
}

.news-activity-list a {
    text-decoration: none;
    font-weight: 500;
    flex: 1;
}

.news-activity-date {
    color: #666;
    font-size: 12px;
    white-space: nowrap;
    margin-left: 10px;
}
</style>
