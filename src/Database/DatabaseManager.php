<?php

declare(strict_types=1);

namespace NewsPlugin\Database;

/**
 * Database Manager
 * 
 * Handles database operations and migrations
 */
class DatabaseManager
{
    /**
     * Database version
     */
    private const DB_VERSION = '1.0.0';

    /**
     * Database version option name
     */
    private const DB_VERSION_OPTION = 'news_db_version';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize database manager
     */
    private function init(): void
    {
        // Check if database needs updating
        add_action('admin_init', [$this, 'checkDatabaseVersion']);
        
        // Add database hooks
        add_action('news_plugin_activate', [$this, 'createTables']);
        add_action('news_plugin_deactivate', [$this, 'cleanup']);
    }

    /**
     * Check database version and update if needed
     */
    public function checkDatabaseVersion(): void
    {
        $installed_version = get_option(self::DB_VERSION_OPTION, '0.0.0');
        
        if (version_compare($installed_version, self::DB_VERSION, '<')) {
            $this->updateDatabase();
        }
    }

    /**
     * Create database tables
     */
    public function createTables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Create news articles table
        $table_name = $wpdb->prefix . 'news_articles';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            excerpt text,
            content longtext,
            featured_image_id bigint(20),
            author_id bigint(20) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'draft',
            published_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY author_id (author_id),
            KEY status (status),
            KEY published_at (published_at),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Create news sections table
        $sections_table = $wpdb->prefix . 'news_sections';
        $sections_sql = "CREATE TABLE $sections_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            term_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            parent_id bigint(20) DEFAULT 0,
            sort_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY term_id (term_id),
            KEY parent_id (parent_id),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) $charset_collate;";

        // Create news article sections relationship table
        $article_sections_table = $wpdb->prefix . 'news_article_sections';
        $article_sections_sql = "CREATE TABLE $article_sections_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            article_id bigint(20) NOT NULL,
            section_id bigint(20) NOT NULL,
            is_primary tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY article_id (article_id),
            KEY section_id (section_id),
            KEY is_primary (is_primary),
            UNIQUE KEY unique_article_section (article_id, section_id)
        ) $charset_collate;";

        // Create news analytics table
        $analytics_table = $wpdb->prefix . 'news_analytics';
        $analytics_sql = "CREATE TABLE $analytics_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            article_id bigint(20) NOT NULL,
            event_type varchar(50) NOT NULL,
            event_data text,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY article_id (article_id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        dbDelta($sql);
        dbDelta($sections_sql);
        dbDelta($article_sections_sql);
        dbDelta($analytics_sql);

        // Update database version
        update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
    }

    /**
     * Update database
     */
    public function updateDatabase(): void
    {
        // Run migrations based on version
        $installed_version = get_option(self::DB_VERSION_OPTION, '0.0.0');
        
        if (version_compare($installed_version, '1.0.0', '<')) {
            $this->createTables();
        }

        // Add future migrations here
        // if (version_compare($installed_version, '1.1.0', '<')) {
        //     $this->migrateTo110();
        // }
    }

    /**
     * Get database version
     */
    public function getDatabaseVersion(): string
    {
        return get_option(self::DB_VERSION_OPTION, '0.0.0');
    }

    /**
     * Check if table exists
     */
    public function tableExists(string $table_name): bool
    {
        global $wpdb;
        
        $table = $wpdb->prefix . $table_name;
        $result = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table
        ));
        
        return $result === $table;
    }

    /**
     * Get table structure
     */
    public function getTableStructure(string $table_name): array
    {
        global $wpdb;
        
        $table = $wpdb->prefix . $table_name;
        $columns = $wpdb->get_results("DESCRIBE $table");
        
        return $columns;
    }

    /**
     * Execute custom query
     */
    public function query(string $sql, array $params = []): mixed
    {
        global $wpdb;
        
        if (empty($params)) {
            return $wpdb->query($sql);
        }
        
        return $wpdb->query($wpdb->prepare($sql, $params));
    }

    /**
     * Get results from query
     */
    public function getResults(string $sql, array $params = []): array
    {
        global $wpdb;
        
        if (empty($params)) {
            return $wpdb->get_results($sql);
        }
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }

    /**
     * Get single result from query
     */
    public function getResult(string $sql, array $params = []): mixed
    {
        global $wpdb;
        
        if (empty($params)) {
            return $wpdb->get_row($sql);
        }
        
        return $wpdb->get_row($wpdb->prepare($sql, $params));
    }

    /**
     * Get single value from query
     */
    public function getVar(string $sql, array $params = []): mixed
    {
        global $wpdb;
        
        if (empty($params)) {
            return $wpdb->get_var($sql);
        }
        
        return $wpdb->get_var($wpdb->prepare($sql, $params));
    }

    /**
     * Insert data into table
     */
    public function insert(string $table, array $data): int|false
    {
        global $wpdb;
        
        $table = $wpdb->prefix . $table;
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }

    /**
     * Update data in table
     */
    public function update(string $table, array $data, array $where): int|false
    {
        global $wpdb;
        
        $table = $wpdb->prefix . $table;
        return $wpdb->update($table, $data, $where);
    }

    /**
     * Delete data from table
     */
    public function delete(string $table, array $where): int|false
    {
        global $wpdb;
        
        $table = $wpdb->prefix . $table;
        return $wpdb->delete($table, $where);
    }

    /**
     * Get table prefix
     */
    public function getTablePrefix(): string
    {
        global $wpdb;
        return $wpdb->prefix;
    }

    /**
     * Get full table name
     */
    public function getTableName(string $table): string
    {
        return $this->getTablePrefix() . $table;
    }

    /**
     * Cleanup database on deactivation
     */
    public function cleanup(): void
    {
        // Don't delete data on deactivation
        // Only clear caches and temporary data
        wp_cache_flush();
    }

    /**
     * Get database statistics
     */
    public function getStats(): array
    {
        global $wpdb;
        
        $stats = [
            'version' => $this->getDatabaseVersion(),
            'tables' => [],
        ];

        $tables = [
            'news_articles',
            'news_sections',
            'news_article_sections',
            'news_analytics',
        ];

        foreach ($tables as $table) {
            $full_table = $this->getTableName($table);
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
            $stats['tables'][$table] = [
                'name' => $full_table,
                'count' => (int) $count,
                'exists' => $this->tableExists($table),
            ];
        }

        return $stats;
    }

    /**
     * Backup table data
     */
    public function backupTable(string $table): array
    {
        global $wpdb;
        
        $table = $this->getTableName($table);
        $data = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
        
        return $data;
    }

    /**
     * Restore table data
     */
    public function restoreTable(string $table, array $data): bool
    {
        global $wpdb;
        
        $table = $this->getTableName($table);
        
        foreach ($data as $row) {
            $result = $wpdb->insert($table, $row);
            if ($result === false) {
                return false;
            }
        }
        
        return true;
    }
}
