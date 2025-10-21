<?php

declare(strict_types=1);

namespace NewsPlugin\Cache;

/**
 * Cache Manager
 * 
 * Handles caching functionality for the plugin
 */
class CacheManager
{
    /**
     * Cache group prefix
     */
    private const CACHE_GROUP = 'news_plugin';

    /**
     * Default cache duration (1 hour)
     */
    private const DEFAULT_DURATION = 3600;

    /**
     * Cache duration
     */
    private int $duration;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->duration = (int) get_option('news_cache_duration', self::DEFAULT_DURATION);
        $this->init();
    }

    /**
     * Initialize cache manager
     */
    private function init(): void
    {
        // Add cache hooks
        add_action('news_clear_cache', [$this, 'clearCache']);
        add_action('news_clear_cache_group', [$this, 'clearCacheGroup']);
    }

    /**
     * Get cache key
     */
    private function getCacheKey(string $key): string
    {
        return self::CACHE_GROUP . '_' . $key;
    }

    /**
     * Get cached data
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cache_key = $this->getCacheKey($key);
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);

        if ($cached === false) {
            return $default;
        }

        return $cached;
    }

    /**
     * Set cached data
     */
    public function set(string $key, mixed $data, int $duration = null): bool
    {
        $cache_key = $this->getCacheKey($key);
        $duration = $duration ?? $this->duration;

        return wp_cache_set($cache_key, $data, self::CACHE_GROUP, $duration);
    }

    /**
     * Delete cached data
     */
    public function delete(string $key): bool
    {
        $cache_key = $this->getCacheKey($key);
        return wp_cache_delete($cache_key, self::CACHE_GROUP);
    }

    /**
     * Check if cache exists
     */
    public function has(string $key): bool
    {
        $cache_key = $this->getCacheKey($key);
        return wp_cache_get($cache_key, self::CACHE_GROUP) !== false;
    }

    /**
     * Get or set cached data
     */
    public function remember(string $key, callable $callback, int $duration = null): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $data = $callback();
        $this->set($key, $data, $duration);

        return $data;
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        return wp_cache_flush();
    }

    /**
     * Clear cache group
     */
    public function clearCacheGroup(): bool
    {
        return wp_cache_flush_group(self::CACHE_GROUP);
    }

    /**
     * Clear specific cache
     */
    public function clearCache(string $key): bool
    {
        return $this->delete($key);
    }

    /**
     * Clear cache by pattern
     */
    public function clearCacheByPattern(string $pattern): int
    {
        $cleared = 0;
        
        // This is a simplified implementation
        // In a real scenario, you might want to maintain a list of cache keys
        $keys = $this->getCacheKeys();
        
        foreach ($keys as $key) {
            if (strpos($key, $pattern) !== false) {
                if ($this->delete($key)) {
                    $cleared++;
                }
            }
        }

        return $cleared;
    }

    /**
     * Get all cache keys (simplified implementation)
     */
    private function getCacheKeys(): array
    {
        // This is a simplified implementation
        // In a real scenario, you might want to maintain a list of cache keys
        return [];
    }

    /**
     * Increment cache value
     */
    public function increment(string $key, int $offset = 1): int
    {
        $cache_key = $this->getCacheKey($key);
        $current = $this->get($key, 0);
        $new_value = $current + $offset;
        
        $this->set($key, $new_value);
        
        return $new_value;
    }

    /**
     * Decrement cache value
     */
    public function decrement(string $key, int $offset = 1): int
    {
        $cache_key = $this->getCacheKey($key);
        $current = $this->get($key, 0);
        $new_value = max(0, $current - $offset);
        
        $this->set($key, $new_value);
        
        return $new_value;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'group' => self::CACHE_GROUP,
            'duration' => $this->duration,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }

    /**
     * Set cache duration
     */
    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
        update_option('news_cache_duration', $duration);
    }

    /**
     * Get cache duration
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * Cache a database query result
     */
    public function cacheQuery(string $key, callable $query, int $duration = null): mixed
    {
        return $this->remember($key, $query, $duration);
    }

    /**
     * Cache a WordPress query
     */
    public function cacheWpQuery(array $args, int $duration = null): mixed
    {
        $key = 'wp_query_' . md5(serialize($args));
        
        return $this->remember($key, function() use ($args) {
            return new \WP_Query($args);
        }, $duration);
    }

    /**
     * Cache a function result
     */
    public function cacheFunction(string $key, callable $function, array $args = [], int $duration = null): mixed
    {
        $cache_key = $key . '_' . md5(serialize($args));
        
        return $this->remember($cache_key, function() use ($function, $args) {
            return call_user_func_array($function, $args);
        }, $duration);
    }
}
