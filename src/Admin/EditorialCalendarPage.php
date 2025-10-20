<?php
/**
 * Editorial Calendar Admin Page
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the editorial calendar admin page
 */
class EditorialCalendarPage {
    
    /**
     * Initialize the admin page
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=news',
            __('Editorial Calendar', 'news'),
            __('Editorial Calendar', 'news'),
            'edit_news',
            'news-editorial-calendar',
            [$this, 'render_page']
        );
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets(): void {
        $screen = get_current_screen();
        
        if (!$screen || $screen->id !== 'news_page_news-editorial-calendar') {
            return;
        }
        
        wp_enqueue_script(
            'news-editorial-calendar',
            NEWS_PLUGIN_URL . 'src/Assets/js/editorial-calendar.js',
            ['jquery', 'moment'],
            NEWS_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'news-editorial-calendar',
            NEWS_PLUGIN_URL . 'src/Assets/css/editorial-calendar.css',
            [],
            NEWS_PLUGIN_VERSION
        );
        
        wp_localize_script('news-editorial-calendar', 'newsEditorial', [
            'apiUrl' => rest_url('news/v1/editorial/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
    
    /**
     * Render the admin page
     */
    public function render_page(): void {
        ?>
        <div class="wrap">
            <h1><?php _e('Editorial Calendar', 'news'); ?></h1>
            
            <div class="editorial-calendar-header">
                <div class="calendar-navigation">
                    <button class="calendar-nav-button" id="prev-month">
                        <?php _e('Previous Month', 'news'); ?>
                    </button>
                    <div class="calendar-period" id="current-period">
                        <?php echo date('F Y'); ?>
                    </div>
                    <button class="calendar-nav-button" id="next-month">
                        <?php _e('Next Month', 'news'); ?>
                    </button>
                </div>
                
                <div class="calendar-filters">
                    <select id="status-filter">
                        <option value=""><?php _e('All Statuses', 'news'); ?></option>
                        <option value="draft"><?php _e('Draft', 'news'); ?></option>
                        <option value="assigned"><?php _e('Assigned', 'news'); ?></option>
                        <option value="in_progress"><?php _e('In Progress', 'news'); ?></option>
                        <option value="review"><?php _e('Review', 'news'); ?></option>
                        <option value="approved"><?php _e('Approved', 'news'); ?></option>
                        <option value="published"><?php _e('Published', 'news'); ?></option>
                    </select>
                    
                    <select id="priority-filter">
                        <option value=""><?php _e('All Priorities', 'news'); ?></option>
                        <option value="low"><?php _e('Low', 'news'); ?></option>
                        <option value="normal"><?php _e('Normal', 'news'); ?></option>
                        <option value="high"><?php _e('High', 'news'); ?></option>
                        <option value="urgent"><?php _e('Urgent', 'news'); ?></option>
                    </select>
                    
                    <button class="button" id="refresh-calendar">
                        <?php _e('Refresh', 'news'); ?>
                    </button>
                </div>
            </div>
            
            <div id="editorial-calendar" class="editorial-calendar">
                <div class="calendar-loading">
                    <?php _e('Loading calendar...', 'news'); ?>
                </div>
            </div>
            
            <div class="calendar-legend">
                <h3><?php _e('Legend', 'news'); ?></h3>
                <div class="legend-items">
                    <div class="legend-item">
                        <span class="legend-color priority-low"></span>
                        <span><?php _e('Low Priority', 'news'); ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color priority-normal"></span>
                        <span><?php _e('Normal Priority', 'news'); ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color priority-high"></span>
                        <span><?php _e('High Priority', 'news'); ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color priority-urgent"></span>
                        <span><?php _e('Urgent Priority', 'news'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .editorial-calendar-header {
            margin-bottom: 20px;
        }
        
        .calendar-filters {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            align-items: center;
        }
        
        .calendar-filters select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .calendar-legend {
            margin-top: 30px;
            padding: 20px;
            background: #f7f7f7;
            border-radius: 4px;
        }
        
        .calendar-legend h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .legend-items {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 2px;
            display: inline-block;
        }
        
        .legend-color.priority-low {
            background: #28a745;
        }
        
        .legend-color.priority-normal {
            background: #ffc107;
        }
        
        .legend-color.priority-high {
            background: #fd7e14;
        }
        
        .legend-color.priority-urgent {
            background: #dc3545;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Calendar navigation
            $('#prev-month').on('click', function() {
                // Implementation for previous month
                console.log('Previous month clicked');
            });
            
            $('#next-month').on('click', function() {
                // Implementation for next month
                console.log('Next month clicked');
            });
            
            // Calendar filters
            $('#status-filter, #priority-filter').on('change', function() {
                // Implementation for filtering
                console.log('Filter changed');
            });
            
            // Refresh calendar
            $('#refresh-calendar').on('click', function() {
                // Implementation for refresh
                console.log('Refresh clicked');
            });
        });
        </script>
        <?php
    }
}
