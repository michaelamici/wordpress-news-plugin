<?php
/**
 * PHPUnit bootstrap file
 */

// Define plugin constants for testing
if (!defined('NEWS_PLUGIN_VERSION')) {
    define('NEWS_PLUGIN_VERSION', '1.0.0');
}

if (!defined('NEWS_PLUGIN_FILE')) {
    define('NEWS_PLUGIN_FILE', dirname(dirname(__FILE__)) . '/news.php');
}

if (!defined('NEWS_PLUGIN_DIR')) {
    define('NEWS_PLUGIN_DIR', dirname(dirname(__FILE__)) . '/');
}

if (!defined('NEWS_PLUGIN_URL')) {
    define('NEWS_PLUGIN_URL', 'https://wordpress.local/');
}

if (!defined('NEWS_PLUGIN_BASENAME')) {
    define('NEWS_PLUGIN_BASENAME', 'news/news.php');
}

if (!defined('NEWS_PLUGIN_SLUG')) {
    define('NEWS_PLUGIN_SLUG', 'news');
}
