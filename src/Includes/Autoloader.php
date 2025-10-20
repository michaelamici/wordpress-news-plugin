<?php
/**
 * Autoloader for News Plugin
 *
 * @package NewsPlugin
 */

declare(strict_types=1);

namespace NewsPlugin\Includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple PSR-4 autoloader for the News Plugin
 */
class Autoloader {
    
    /**
     * Register the autoloader
     */
    public static function register(): void {
        spl_autoload_register([self::class, 'load']);
    }
    
    /**
     * Load a class file
     *
     * @param string $class_name The class name to load
     */
    public static function load(string $class_name): void {
        $prefix = 'NewsPlugin\\';
        $base_dir = __DIR__ . '/../';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class_name, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class_name, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
