<?php

declare(strict_types=1);

namespace NewsPlugin\Hooks;

/**
 * Hook Manager
 * 
 * Centralized management of WordPress hooks and filters
 */
class HookManager
{
    /**
     * Registered hooks
     */
    private array $hooks = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize hook manager
     */
    private function init(): void
    {
        // Hook into WordPress lifecycle
        add_action('init', [$this, 'registerHooks']);
        add_action('wp_loaded', [$this, 'onWpLoaded']);
        add_action('admin_init', [$this, 'onAdminInit']);
        add_action('wp_enqueue_scripts', [$this, 'onEnqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'onAdminEnqueueScripts']);
    }

    /**
     * Register a hook
     */
    public function addHook(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->hooks[$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];

        add_filter($hook, $callback, $priority, $accepted_args);
    }

    /**
     * Register an action
     */
    public function addAction(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->addHook($hook, $callback, $priority, $accepted_args);
    }

    /**
     * Register a filter
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->addHook($hook, $callback, $priority, $accepted_args);
    }

    /**
     * Remove a hook
     */
    public function removeHook(string $hook, callable $callback, int $priority = 10): bool
    {
        $removed = remove_filter($hook, $callback, $priority);
        
        if ($removed && isset($this->hooks[$hook])) {
            $this->hooks[$hook] = array_filter(
                $this->hooks[$hook],
                fn($h) => $h['callback'] !== $callback || $h['priority'] !== $priority
            );
        }

        return $removed;
    }

    /**
     * Get all registered hooks
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * Get hooks for a specific hook name
     */
    public function getHooksFor(string $hook): array
    {
        return $this->hooks[$hook] ?? [];
    }

    /**
     * Register default hooks
     */
    public function registerHooks(): void
    {
        // Plugin-specific hooks
        do_action('news_register_hooks', $this);
    }

    /**
     * WordPress loaded hook
     */
    public function onWpLoaded(): void
    {
        do_action('news_wp_loaded');
    }

    /**
     * Admin init hook
     */
    public function onAdminInit(): void
    {
        do_action('news_admin_init');
    }

    /**
     * Frontend enqueue scripts hook
     */
    public function onEnqueueScripts(): void
    {
        do_action('news_enqueue_scripts');
    }

    /**
     * Admin enqueue scripts hook
     */
    public function onAdminEnqueueScripts(): void
    {
        do_action('news_admin_enqueue_scripts');
    }

    /**
     * Apply filters
     */
    public function applyFilters(string $hook, mixed $value, ...$args): mixed
    {
        return apply_filters($hook, $value, ...$args);
    }

    /**
     * Do action
     */
    public function doAction(string $hook, ...$args): void
    {
        do_action($hook, ...$args);
    }
}
