<?php

declare(strict_types=1);

namespace NewsPlugin\Security;

/**
 * Security Manager
 * 
 * Handles security-related functionality including nonces, capabilities, and sanitization
 */
class SecurityManager
{
    /**
     * Nonce action prefix
     */
    private const NONCE_PREFIX = 'news_';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize security manager
     */
    private function init(): void
    {
        // Add security hooks
        add_action('init', [$this, 'registerCapabilities']);
        add_action('wp_ajax_news_action', [$this, 'handleAjaxAction']);
        add_action('wp_ajax_nopriv_news_action', [$this, 'handleAjaxAction']);
    }

    /**
     * Create a nonce
     */
    public function createNonce(string $action): string
    {
        return wp_create_nonce(self::NONCE_PREFIX . $action);
    }

    /**
     * Verify a nonce
     */
    public function verifyNonce(string $nonce, string $action): bool
    {
        return wp_verify_nonce($nonce, self::NONCE_PREFIX . $action);
    }

    /**
     * Check if current user has capability
     */
    public function currentUserCan(string $capability, ?int $post_id = null): bool
    {
        if ($post_id && in_array($capability, ['edit_post', 'read_post', 'delete_post'])) {
            return current_user_can($capability, $post_id);
        }
        
        return current_user_can($capability);
    }

    /**
     * Check if current user can manage news
     */
    public function canManageNews(): bool
    {
        return $this->currentUserCan('manage_news');
    }

    /**
     * Check if current user can edit a specific news post
     */
    public function canEditNewsPost(int $post_id): bool
    {
        return $this->currentUserCan('edit_news', $post_id);
    }

    /**
     * Check if current user can read a specific news post
     */
    public function canReadNewsPost(int $post_id): bool
    {
        return $this->currentUserCan('read_news', $post_id);
    }

    /**
     * Check if current user can delete a specific news post
     */
    public function canDeleteNewsPost(int $post_id): bool
    {
        return $this->currentUserCan('delete_news', $post_id);
    }

    /**
     * Check if current user can edit news
     */
    public function canEditNews(): bool
    {
        return $this->currentUserCan('edit_news');
    }

    /**
     * Check if current user can publish news
     */
    public function canPublishNews(): bool
    {
        return $this->currentUserCan('publish_news');
    }

    /**
     * Sanitize text input
     */
    public function sanitizeText(string $text): string
    {
        return sanitize_text_field($text);
    }

    /**
     * Sanitize textarea input
     */
    public function sanitizeTextarea(string $text): string
    {
        return sanitize_textarea_field($text);
    }

    /**
     * Sanitize email input
     */
    public function sanitizeEmail(string $email): string
    {
        return sanitize_email($email);
    }

    /**
     * Sanitize URL input
     */
    public function sanitizeUrl(string $url): string
    {
        return esc_url_raw($url);
    }

    /**
     * Sanitize integer input
     */
    public function sanitizeInt(mixed $value): int
    {
        return (int) $value;
    }

    /**
     * Sanitize float input
     */
    public function sanitizeFloat(mixed $value): float
    {
        return (float) $value;
    }

    /**
     * Sanitize boolean input
     */
    public function sanitizeBool(mixed $value): bool
    {
        return (bool) $value;
    }

    /**
     * Sanitize array input
     */
    public function sanitizeArray(array $array, ?callable $sanitizer = null): array
    {
        if ($sanitizer) {
            return array_map($sanitizer, $array);
        }

        return array_map([$this, 'sanitizeText'], $array);
    }

    /**
     * Escape HTML output
     */
    public function escapeHtml(string $html): string
    {
        return esc_html($html);
    }

    /**
     * Escape HTML attributes
     */
    public function escapeAttr(string $attr): string
    {
        return esc_attr($attr);
    }

    /**
     * Escape URL output
     */
    public function escapeUrl(string $url): string
    {
        return esc_url($url);
    }

    /**
     * Escape JavaScript output
     */
    public function escapeJs(string $js): string
    {
        return esc_js($js);
    }

    /**
     * Sanitize and validate data
     */
    public function validateData(array $data, array $rules): array
    {
        $validated = [];

        foreach ($rules as $field => $rule) {
            if (!isset($data[$field])) {
                if (isset($rule['required']) && $rule['required']) {
                    throw new \InvalidArgumentException("Field '{$field}' is required");
                }
                continue;
            }

            $value = $data[$field];
            $type = $rule['type'] ?? 'text';

            switch ($type) {
                case 'text':
                    $validated[$field] = $this->sanitizeText($value);
                    break;
                case 'textarea':
                    $validated[$field] = $this->sanitizeTextarea($value);
                    break;
                case 'email':
                    $validated[$field] = $this->sanitizeEmail($value);
                    break;
                case 'url':
                    $validated[$field] = $this->sanitizeUrl($value);
                    break;
                case 'int':
                    $validated[$field] = $this->sanitizeInt($value);
                    break;
                case 'float':
                    $validated[$field] = $this->sanitizeFloat($value);
                    break;
                case 'bool':
                    $validated[$field] = $this->sanitizeBool($value);
                    break;
                case 'array':
                    $sanitizer = $rule['sanitizer'] ?? null;
                    $validated[$field] = $this->sanitizeArray($value, $sanitizer);
                    break;
                default:
                    $validated[$field] = $this->sanitizeText($value);
            }

            // Apply custom validation
            if (isset($rule['validate']) && is_callable($rule['validate'])) {
                $result = $rule['validate']($validated[$field]);
                if ($result !== true) {
                    throw new \InvalidArgumentException("Field '{$field}' validation failed: {$result}");
                }
            }
        }

        return $validated;
    }

    /**
     * Register custom capabilities
     */
    public function registerCapabilities(): void
    {
        $capabilities = [
            'manage_news' => 'Manage News Plugin',
            'edit_news' => 'Edit News Articles',
            'publish_news' => 'Publish News Articles',
            'delete_news' => 'Delete News Articles',
            'edit_others_news' => 'Edit Others News Articles',
            'delete_others_news' => 'Delete Others News Articles',
        ];

        foreach ($capabilities as $cap => $description) {
            add_filter('user_has_cap', function($caps, $capabilities, $args) use ($cap) {
                if (in_array($cap, $capabilities)) {
                    $caps[$cap] = true;
                }
                return $caps;
            }, 10, 3);
        }
    }

    /**
     * Handle AJAX actions
     */
    public function handleAjaxAction(): void
    {
        // Verify nonce
        $nonce = $_POST['nonce'] ?? '';
        $action = $_POST['action'] ?? '';

        if (!$this->verifyNonce($nonce, $action)) {
            wp_die('Security check failed', 'Security Error', ['response' => 403]);
        }

        // Check capabilities
        if (!$this->canManageNews()) {
            wp_die('Insufficient permissions', 'Permission Error', ['response' => 403]);
        }

        // Handle the action
        do_action('news_ajax_action', $action, $_POST);
    }

    /**
     * Check if request is secure
     */
    public function isSecureRequest(): bool
    {
        return is_ssl() || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Get client IP address
     */
    public function getClientIp(): string
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Rate limiting
     */
    public function checkRateLimit(string $key, int $limit = 100, int $window = 3600): bool
    {
        $transient_key = 'news_rate_limit_' . md5($key . $this->getClientIp());
        $count = get_transient($transient_key);

        if ($count === false) {
            set_transient($transient_key, 1, $window);
            return true;
        }

        if ($count >= $limit) {
            return false;
        }

        set_transient($transient_key, $count + 1, $window);
        return true;
    }
}
