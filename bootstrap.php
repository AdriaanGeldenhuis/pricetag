<?php
/**
 * Bootstrap - Application Initialization
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * This file initializes the application environment, loads configuration,
 * and sets up essential services before routing begins.
 */

declare(strict_types=1);

// Define paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('ADMIN_PATH', ROOT_PATH . '/admin');

// Error reporting based on environment
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-errors.log');

// Load helper functions
require_once APP_PATH . '/helpers.php';

// Load environment variables
loadEnv(ROOT_PATH . '/.env');

// Set environment-specific error handling
if (env('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
}

// Set timezone
date_default_timezone_set('Africa/Johannesburg');

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    $sessionConfig = [
        'cookie_httponly' => true,
        'cookie_secure' => env('SESSION_SECURE', true),
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
        'gc_maxlifetime' => env('SESSION_LIFETIME', 120) * 60,
    ];

    session_save_path(STORAGE_PATH . '/sessions');

    foreach ($sessionConfig as $key => $value) {
        ini_set("session.$key", (string) $value);
    }

    session_name('pricetag_session');
    session_start();

    // Regenerate session ID periodically
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => APP_PATH . '/',
        'Admin\\' => ADMIN_PATH . '/',
    ];

    foreach ($prefixes as $prefix => $basePath) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $basePath . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
    }

    return false;
});

// Load configuration
$config = [
    'app' => require CONFIG_PATH . '/app.php',
    'database' => require CONFIG_PATH . '/database.php',
    'seo' => require CONFIG_PATH . '/seo.php',
    'payment' => require CONFIG_PATH . '/payment.php',
];

// Store config globally
$GLOBALS['config'] = $config;

// Initialize database connection
App\Core\Database::getInstance();

// Initialize CSRF protection
if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
    regenerateCsrfToken();
} elseif (time() - $_SESSION['csrf_token_time'] > config('app.security.csrf_lifetime', 3600)) {
    regenerateCsrfToken();
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
// CSP removed - causes issues with inline styles on shared hosting

// Error handler
set_exception_handler(function (Throwable $e) {
    $logFile = STORAGE_PATH . '/logs/errors.log';
    $message = sprintf(
        "[%s] %s: %s in %s:%d\nStack trace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );

    error_log($message, 3, $logFile);

    if (env('APP_DEBUG', false)) {
        echo '<h1>Error</h1>';
        echo '<pre>' . htmlspecialchars($message) . '</pre>';
    } else {
        http_response_code(500);
        include APP_PATH . '/Views/errors/500.php';
    }

    exit(1);
});
