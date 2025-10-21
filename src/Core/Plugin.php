<?php

declare(strict_types=1);

namespace NewsPlugin\Core;

use NewsPlugin\Admin\Admin;
use NewsPlugin\Frontend\Frontend;
use NewsPlugin\Blocks\BlockManager;
use NewsPlugin\Widgets\WidgetManager;
use NewsPlugin\Api\RestApi;
use NewsPlugin\Security\SecurityManager;
use NewsPlugin\Cache\CacheManager;
use NewsPlugin\Database\DatabaseManager;
use NewsPlugin\Assets\AssetManager;
use NewsPlugin\Hooks\HookManager;
use NewsPlugin\PostTypes\PostTypes;

/**
 * Main plugin class
 * 
 * This is the primary class that initializes and manages all plugin functionality.
 * It follows the singleton pattern to ensure only one instance exists.
 */
final class Plugin
{
    /**
     * Plugin instance
     */
    private static ?Plugin $instance = null;

    /**
     * Plugin version
     */
    public const VERSION = '1.0.0';

    /**
     * Plugin slug
     */
    public const SLUG = 'news';

    /**
     * Plugin file path
     */
    public const FILE = NEWS_PLUGIN_FILE;

    /**
     * Plugin directory path
     */
    public const DIR = NEWS_PLUGIN_DIR;

    /**
     * Plugin URL
     */
    public const URL = NEWS_PLUGIN_URL;

    /**
     * Plugin basename
     */
    public const BASENAME = NEWS_PLUGIN_BASENAME;

    /**
     * Component managers
     */
    private Admin $admin;
    private Frontend $frontend;
    private BlockManager $blockManager;
    private WidgetManager $widgetManager;
    private RestApi $restApi;
    private SecurityManager $securityManager;
    private CacheManager $cacheManager;
    private DatabaseManager $databaseManager;
    private AssetManager $assetManager;
    private HookManager $hookManager;
    private PostTypes $postTypes;

    /**
     * Initialization flag
     */
    private bool $initialized = false;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        // Constructor is empty - initialization happens in init()
    }

    /**
     * Get plugin instance
     */
    public static function instance(): Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize the plugin
     */
    public function init(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        // Initialize component managers
        $this->initComponents();

        // Set up hooks
        $this->initHooks();

        // Load text domain
        add_action('init', [$this, 'loadTextDomain']);

        // Initialize components
        add_action('init', [$this, 'initComponentsPublic'], 5);
        add_action('init', [$this, 'initBlocks'], 10);
        add_action('init', [$this, 'initWidgets'], 15);
        add_action('rest_api_init', [$this, 'initRestApi']);

        // Admin initialization
        if (is_admin()) {
            add_action('admin_init', [$this, 'initAdmin']);
        }

        // Frontend initialization
        if (!is_admin()) {
            add_action('template_redirect', [$this, 'initFrontend']);
        }
    }

    /**
     * Initialize component managers
     */
    private function initComponents(): void
    {
        $this->hookManager = new HookManager();
        $this->securityManager = new SecurityManager();
        $this->cacheManager = new CacheManager();
        $this->databaseManager = new DatabaseManager();
        $this->assetManager = new AssetManager();
        $this->postTypes = new PostTypes();
    }

    /**
     * Public wrapper for initComponents
     */
    public function initComponentsPublic(): void
    {
        $this->initComponents();
    }

    /**
     * Initialize hooks
     */
    private function initHooks(): void
    {
        // Activation/Deactivation hooks
        register_activation_hook(self::FILE, [$this, 'activate']);
        register_deactivation_hook(self::FILE, [$this, 'deactivate']);

        // Plugin action links
        add_filter('plugin_action_links_' . self::BASENAME, [$this, 'addActionLinks']);

        // Plugin row meta
        add_filter('plugin_row_meta', [$this, 'addRowMeta'], 10, 2);
    }

    /**
     * Load text domain for internationalization
     */
    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'news',
            false,
            dirname(self::BASENAME) . '/languages'
        );
    }

    /**
     * Initialize admin components
     */
    public function initAdmin(): void
    {
        if (!isset($this->admin)) {
            $this->admin = new Admin();
        }
    }

    /**
     * Initialize frontend components
     */
    public function initFrontend(): void
    {
        if (!isset($this->frontend)) {
            $this->frontend = new Frontend();
        }
    }

    /**
     * Initialize blocks
     */
    public function initBlocks(): void
    {
        if (!isset($this->blockManager)) {
            $this->blockManager = new BlockManager();
        }
    }

    /**
     * Initialize widgets
     */
    public function initWidgets(): void
    {
        if (!isset($this->widgetManager)) {
            $this->widgetManager = new WidgetManager();
        }
    }

    /**
     * Initialize REST API
     */
    public function initRestApi(): void
    {
        if (!isset($this->restApi)) {
            $this->restApi = new RestApi();
        }
    }

    /**
     * Plugin activation
     */
    public function activate(): void
    {
        // Check requirements
        $this->checkRequirements();

        // Initialize database
        $this->databaseManager->createTables();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set default options
        $this->setDefaultOptions();

        // Fire activation hook
        do_action('news_plugin_activate');
    }

    /**
     * Plugin deactivation
     */
    public function deactivate(): void
    {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear caches
        $this->cacheManager->flush();

        // Fire deactivation hook
        do_action('news_plugin_deactivate');
    }

    /**
     * Check system requirements
     */
    private function checkRequirements(): void
    {
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            deactivate_plugins(plugin_basename(self::FILE));
            wp_die(
                esc_html__('News Plugin requires PHP 8.1 or higher.', 'news'),
                esc_html__('Plugin Activation Error', 'news'),
                ['response' => 200, 'back_link' => true]
            );
        }

        if (version_compare(get_bloginfo('version'), '6.5', '<')) {
            deactivate_plugins(plugin_basename(self::FILE));
            wp_die(
                esc_html__('News Plugin requires WordPress 6.5 or higher.', 'news'),
                esc_html__('Plugin Activation Error', 'news'),
                ['response' => 200, 'back_link' => true]
            );
        }
    }

    /**
     * Set default plugin options
     */
    private function setDefaultOptions(): void
    {
        $defaults = [
            'news_plugin_version' => self::VERSION,
            'news_plugin_activated' => time(),
            'news_settings' => [
                'enable_blocks' => true,
                'enable_widgets' => true,
                'enable_rest_api' => true,
                'cache_duration' => 3600,
            ],
        ];

        foreach ($defaults as $option => $value) {
            if (false === get_option($option)) {
                add_option($option, $value);
            }
        }
    }

    /**
     * Add action links to plugin page
     */
    public function addActionLinks(array $links): array
    {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=news-settings'),
            esc_html__('Settings', 'news')
        );

        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Add row meta to plugin page
     */
    public function addRowMeta(array $links, string $file): array
    {
        if (self::BASENAME !== $file) {
            return $links;
        }

        $row_meta = [
            'docs' => sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://github.com/newbaltimoregazette/news-plugin',
                esc_html__('Documentation', 'news')
            ),
            'support' => sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://github.com/newbaltimoregazette/news-plugin/issues',
                esc_html__('Support', 'news')
            ),
        ];

        return array_merge($links, $row_meta);
    }

    /**
     * Get component managers
     */
    public function getAdmin(): ?Admin
    {
        return $this->admin ?? null;
    }

    public function getFrontend(): ?Frontend
    {
        return $this->frontend ?? null;
    }

    public function getBlockManager(): ?BlockManager
    {
        return $this->blockManager ?? null;
    }

    public function getWidgetManager(): ?WidgetManager
    {
        return $this->widgetManager ?? null;
    }

    public function getRestApi(): ?RestApi
    {
        return $this->restApi ?? null;
    }

    public function getSecurityManager(): SecurityManager
    {
        return $this->securityManager;
    }

    public function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }

    public function getDatabaseManager(): DatabaseManager
    {
        return $this->databaseManager;
    }

    public function getAssetManager(): AssetManager
    {
        return $this->assetManager;
    }

    public function getHookManager(): HookManager
    {
        return $this->hookManager;
    }

    public function getPostTypes(): PostTypes
    {
        return $this->postTypes;
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}



