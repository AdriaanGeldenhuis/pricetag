<?php
/**
 * Security Configuration
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Configure security headers, rate limiting, and protection settings.
 */

return [
    // =========================================================================
    // HTTP Security Headers
    // =========================================================================
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    // Strict-Transport-Security (enable in production with HTTPS)
    'hsts' => [
        'enabled' => env('HSTS_ENABLED', false),
        'max_age' => 31536000, // 1 year
        'include_subdomains' => true,
        'preload' => false,
    ],

    // =========================================================================
    // Rate Limiting
    // =========================================================================
    'rate_limits' => [
        // Global rate limit
        'global' => [
            'requests' => 1000,
            'window' => 60, // seconds
        ],

        // Authentication endpoints
        'auth' => [
            'requests' => 5,
            'window' => 60,
            'endpoints' => ['/login', '/register', '/forgot-password'],
        ],

        // API endpoints
        'api' => [
            'requests' => 60,
            'window' => 60,
        ],

        // Search
        'search' => [
            'requests' => 30,
            'window' => 60,
        ],

        // Cart operations
        'cart' => [
            'requests' => 30,
            'window' => 60,
        ],
    ],

    // =========================================================================
    // CSRF Protection
    // =========================================================================
    'csrf' => [
        'enabled' => true,
        'token_name' => 'csrf_token',
        'token_length' => 32,
        'regenerate' => true, // Regenerate token after use
        'exclude' => [
            '/api/*',
            '/checkout/webhook',
        ],
    ],

    // =========================================================================
    // Session Security
    // =========================================================================
    'session' => [
        'cookie_httponly' => true,
        'cookie_secure' => env('SESSION_SECURE', false), // true in production
        'cookie_samesite' => 'Lax',
        'regenerate_id' => true,
        'lifetime' => 7200, // 2 hours
        'gc_maxlifetime' => 86400, // 24 hours
    ],

    // =========================================================================
    // Password Policy
    // =========================================================================
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special' => false,
        'max_age_days' => 0, // 0 = no expiration
        'history_count' => 0, // Remember previous passwords
    ],

    // =========================================================================
    // Login Security
    // =========================================================================
    'login' => [
        'max_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'remember_me_duration' => 2592000, // 30 days
        'log_attempts' => true,
    ],

    // =========================================================================
    // Input Validation
    // =========================================================================
    'input' => [
        'max_post_size' => '10M',
        'max_upload_size' => '5M',
        'allowed_upload_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'],
        'strip_tags' => false, // Handle via htmlspecialchars instead
        'sanitize_sql' => true, // Extra SQL injection protection
    ],

    // =========================================================================
    // Content Security
    // =========================================================================
    'content' => [
        // Trusted domains for external resources
        'trusted_domains' => [
            'fonts.googleapis.com',
            'fonts.gstatic.com',
            'cdn.jsdelivr.net',
            'unpkg.com',
            'js.yoco.com', // Yoco payment
        ],

        // Allow inline scripts (not recommended, but sometimes needed)
        'allow_inline_scripts' => true,

        // Allow eval (not recommended)
        'allow_eval' => false,
    ],

    // =========================================================================
    // IP Security
    // =========================================================================
    'ip' => [
        // Trusted proxy IPs (for correct client IP detection)
        'trusted_proxies' => env('TRUSTED_PROXIES', ''),

        // IP whitelist for admin (empty = no restriction)
        'admin_whitelist' => [],

        // IP blacklist
        'blacklist' => [],

        // Block Tor exit nodes
        'block_tor' => false,
    ],

    // =========================================================================
    // Audit Logging
    // =========================================================================
    'audit' => [
        'enabled' => true,
        'log_auth' => true, // Login/logout
        'log_admin' => true, // Admin actions
        'log_orders' => true, // Order modifications
        'log_data_changes' => true, // User data changes
        'retention_days' => 90, // How long to keep logs
    ],

    // =========================================================================
    // File Security
    // =========================================================================
    'files' => [
        // Directories that should never be directly accessible
        'protected_paths' => [
            '/app/',
            '/config/',
            '/storage/',
            '/vendor/',
        ],

        // Executable file extensions to block
        'blocked_extensions' => ['php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'cgi', 'pl', 'asp', 'aspx', 'jsp'],
    ],
];
