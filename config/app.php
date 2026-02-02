<?php
/**
 * Application Configuration
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

return [
    'name' => env('APP_NAME', 'Pricetag'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'https://pricetag.co.za'),
    'key' => env('APP_KEY', ''),

    'timezone' => 'Africa/Johannesburg',
    'locale' => 'en_ZA',
    'currency' => 'ZAR',
    'currency_symbol' => 'R',

    'version' => '1.0.0',

    'paths' => [
        'app' => APP_PATH,
        'public' => PUBLIC_PATH,
        'storage' => STORAGE_PATH,
        'cache' => STORAGE_PATH . '/cache',
        'logs' => STORAGE_PATH . '/logs',
        'uploads' => STORAGE_PATH . '/uploads',
    ],

    'security' => [
        'csrf_lifetime' => env('CSRF_TOKEN_LIFETIME', 3600),
        'admin_ip_whitelist' => env('ADMIN_IP_WHITELIST', ''),
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_number' => true,
        'password_require_special' => false,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
    ],

    'rate_limiting' => [
        'enabled' => true,
        'requests' => env('RATE_LIMIT_REQUESTS', 60),
        'window' => env('RATE_LIMIT_WINDOW', 60),
    ],
];
