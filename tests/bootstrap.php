<?php
/**
 * Test bootstrap
 *
 * Loads helpers + the PSR-4 autoloader without starting a session or
 * eagerly connecting to MySQL. Tests that need the database call
 * TestCase::requireDb() and are skipped gracefully when the connection
 * is unavailable (e.g. CI sandboxes without a server).
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('ADMIN_PATH', ROOT_PATH . '/admin');

// Quiet error logging during tests
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-errors.log');

require_once APP_PATH . '/helpers.php';

// Load .env.test if present, falling back to .env. loadEnv() in helpers.php
// silently no-ops on missing files.
if (file_exists(ROOT_PATH . '/.env.test')) {
    loadEnv(ROOT_PATH . '/.env.test');
} else {
    loadEnv(ROOT_PATH . '/.env');
}

date_default_timezone_set('Africa/Johannesburg');

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

// Make config() available without booting the full app.
$GLOBALS['config'] = [
    'app' => @include CONFIG_PATH . '/app.php' ?: [],
    'database' => @include CONFIG_PATH . '/database.php' ?: [],
    'seo' => @include CONFIG_PATH . '/seo.php' ?: [],
];

require_once __DIR__ . '/TestCase.php';
