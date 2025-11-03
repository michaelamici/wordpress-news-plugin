<?php
/**
 * Plugin Name: Kestrel Courier
 * Description: Blank WordPress plugin scaffold for a news-themed site.
 * Version: 0.1.0
 * Author: Team
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kestrel-courier
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register blocks
require_once plugin_dir_path(__FILE__) . 'src/blocks/juicy-headline-multi-query/index.php';
require_once plugin_dir_path(__FILE__) . 'src/blocks/saucy-story-template/index.php';
require_once plugin_dir_path(__FILE__) . 'src/blocks/breaking-news-template/index.php';
require_once plugin_dir_path(__FILE__) . 'src/blocks/featured-story-template/index.php';

// Plugin initialized.
