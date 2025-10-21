<?php
/**
 * News Dashboard Template
 * 
 * @package NewsPlugin
 * @subpackage Templates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get some basic stats
$total_articles = wp_count_posts('news');
$total_sections = wp_count_terms('news_section');
$recent_articles = get_posts([
    'post_type' => 'news',
    'posts_per_page' => 5,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
]);
?>

<div class="wrap">
    <h1><?php esc_html_e('News Dashboard', 'news'); ?></h1>
    
    <div class="news-dashboard-stats">
        <div class="news-stat-box">
            <h3><?php esc_html_e('Total Articles', 'news'); ?></h3>
            <p class="news-stat-number"><?php echo esc_html($total_articles->publish ?? 0); ?></p>
        </div>
        
        <div class="news-stat-box">
            <h3><?php esc_html_e('Total Sections', 'news'); ?></h3>
            <p class="news-stat-number"><?php echo esc_html($total_sections); ?></p>
        </div>
        
        <div class="news-stat-box">
            <h3><?php esc_html_e('Draft Articles', 'news'); ?></h3>
            <p class="news-stat-number"><?php echo esc_html($total_articles->draft ?? 0); ?></p>
        </div>
    </div>
    
    <div class="news-dashboard-content">
        <div class="news-recent-articles">
            <h2><?php esc_html_e('Recent Articles', 'news'); ?></h2>
            <?php if (!empty($recent_articles)): ?>
                <ul class="news-article-list">
                    <?php foreach ($recent_articles as $article): ?>
                        <li>
                            <a href="<?php echo esc_url(get_edit_post_link($article->ID)); ?>">
                                <?php echo esc_html($article->post_title); ?>
                            </a>
                            <span class="news-article-date">
                                <?php echo esc_html(get_the_date('', $article->ID)); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php esc_html_e('No articles found.', 'news'); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="news-quick-actions">
            <h2><?php esc_html_e('Quick Actions', 'news'); ?></h2>
            <p>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=news')); ?>" class="button button-primary">
                    <?php esc_html_e('Add New Article', 'news'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=news_section')); ?>" class="button">
                    <?php esc_html_e('Manage Sections', 'news'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=news')); ?>" class="button">
                    <?php esc_html_e('View All Articles', 'news'); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<style>
.news-dashboard-stats {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}

.news-stat-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    flex: 1;
    text-align: center;
}

.news-stat-box h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
}

.news-stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #0073aa;
    margin: 0;
}

.news-dashboard-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-top: 30px;
}

.news-recent-articles,
.news-quick-actions {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.news-article-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.news-article-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.news-article-list li:last-child {
    border-bottom: none;
}

.news-article-list a {
    text-decoration: none;
    font-weight: 500;
}

.news-article-date {
    color: #666;
    font-size: 12px;
    margin-left: 10px;
}
</style>
