<?php
/**
 * News Articles Template
 * 
 * @package NewsPlugin
 * @subpackage Templates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get articles
$articles = get_posts([
    'post_type' => 'news',
    'posts_per_page' => 20,
    'post_status' => 'any',
    'orderby' => 'date',
    'order' => 'DESC'
]);
?>

<div class="wrap">
    <h1><?php esc_html_e('News Articles', 'news'); ?></h1>
    
    <div class="news-articles-header">
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=news')); ?>" class="button button-primary">
            <?php esc_html_e('Add New Article', 'news'); ?>
        </a>
    </div>
    
    <div class="news-articles-list">
        <?php if (!empty($articles)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Title', 'news'); ?></th>
                        <th><?php esc_html_e('Section', 'news'); ?></th>
                        <th><?php esc_html_e('Status', 'news'); ?></th>
                        <th><?php esc_html_e('Date', 'news'); ?></th>
                        <th><?php esc_html_e('Actions', 'news'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <?php
                        $sections = get_the_terms($article->ID, 'news_section');
                        $section_name = !empty($sections) ? $sections[0]->name : __('No Section', 'news');
                        $flags = [];
                        if (get_post_meta($article->ID, '_news_featured', true)) $flags[] = __('Featured', 'news');
                        if (get_post_meta($article->ID, '_news_breaking', true)) $flags[] = __('Breaking', 'news');
                        if (get_post_meta($article->ID, '_news_exclusive', true)) $flags[] = __('Exclusive', 'news');
                        ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(get_edit_post_link($article->ID)); ?>">
                                        <?php echo esc_html($article->post_title); ?>
                                    </a>
                                </strong>
                                <?php if (!empty($flags)): ?>
                                    <br><small class="news-flags"><?php echo esc_html(implode(', ', $flags)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($section_name); ?></td>
                            <td>
                                <span class="news-status news-status-<?php echo esc_attr($article->post_status); ?>">
                                    <?php echo esc_html(ucfirst($article->post_status)); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html(get_the_date('Y-m-d H:i', $article->ID)); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_post_link($article->ID)); ?>" class="button button-small">
                                    <?php esc_html_e('Edit', 'news'); ?>
                                </a>
                                <a href="<?php echo esc_url(get_permalink($article->ID)); ?>" class="button button-small" target="_blank">
                                    <?php esc_html_e('View', 'news'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php esc_html_e('No articles found.', 'news'); ?></p>
        <?php endif; ?>
    </div>
</div>

<style>
.news-articles-header {
    margin: 20px 0;
}

.news-articles-list {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    overflow: hidden;
}

.news-flags {
    color: #0073aa;
    font-weight: 500;
}

.news-status {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.news-status-publish {
    background: #d4edda;
    color: #155724;
}

.news-status-draft {
    background: #fff3cd;
    color: #856404;
}

.news-status-private {
    background: #f8d7da;
    color: #721c24;
}
</style>
