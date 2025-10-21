<?php
/**
 * News Sections Template
 * 
 * @package NewsPlugin
 * @subpackage Templates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get sections
$sections = get_terms([
    'taxonomy' => 'news_section',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
]);
?>

<div class="wrap">
    <h1><?php esc_html_e('News Sections', 'news'); ?></h1>
    
    <div class="news-sections-header">
        <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=news_section')); ?>" class="button button-primary">
            <?php esc_html_e('Manage Sections', 'news'); ?>
        </a>
    </div>
    
    <div class="news-sections-list">
        <?php if (!empty($sections) && !is_wp_error($sections)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'news'); ?></th>
                        <th><?php esc_html_e('Slug', 'news'); ?></th>
                        <th><?php esc_html_e('Description', 'news'); ?></th>
                        <th><?php esc_html_e('Articles', 'news'); ?></th>
                        <th><?php esc_html_e('Actions', 'news'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $section): ?>
                        <?php
                        $article_count = $section->count;
                        $section_meta = get_term_meta($section->term_id, 'news_section_meta', true);
                        $section_meta = wp_parse_args($section_meta, [
                            'color' => '#0073aa',
                            'icon' => 'dashicons-megaphone',
                            'order' => 0
                        ]);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($section->name); ?></strong>
                                <?php if ($section->parent): ?>
                                    <?php $parent = get_term($section->parent, 'news_section'); ?>
                                    <br><small class="news-parent-section">
                                        <?php printf(__('Child of: %s', 'news'), esc_html($parent->name)); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($section->slug); ?></td>
                            <td><?php echo esc_html($section->description); ?></td>
                            <td>
                                <span class="news-article-count"><?php echo esc_html($article_count); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_term_link($section->term_id, 'news_section')); ?>" class="button button-small">
                                    <?php esc_html_e('Edit', 'news'); ?>
                                </a>
                                <a href="<?php echo esc_url(get_term_link($section)); ?>" class="button button-small" target="_blank">
                                    <?php esc_html_e('View', 'news'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php esc_html_e('No sections found.', 'news'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="news-sections-help">
        <h2><?php esc_html_e('About News Sections', 'news'); ?></h2>
        <p><?php esc_html_e('News sections help organize your articles into categories like Politics, Sports, Technology, etc. You can create hierarchical sections with parent and child relationships.', 'news'); ?></p>
        
        <h3><?php esc_html_e('Creating Sections', 'news'); ?></h3>
        <ol>
            <li><?php esc_html_e('Go to the "Manage Sections" page to add new sections', 'news'); ?></li>
            <li><?php esc_html_e('Give each section a name, slug, and description', 'news'); ?></li>
            <li><?php esc_html_e('Assign articles to sections when creating or editing articles', 'news'); ?></li>
            <li><?php esc_html_e('Use sections to create organized news fronts and feeds', 'news'); ?></li>
        </ol>
    </div>
</div>

<style>
.news-sections-header {
    margin: 20px 0;
}

.news-sections-list {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 30px;
}

.news-parent-section {
    color: #666;
    font-style: italic;
}

.news-article-count {
    font-weight: 500;
    color: #0073aa;
}

.news-sections-help {
    background: #f9f9f9;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.news-sections-help h2,
.news-sections-help h3 {
    margin-top: 0;
}

.news-sections-help ol {
    margin-left: 20px;
}
</style>
