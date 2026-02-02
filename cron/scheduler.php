<?php
/**
 * Cron Scheduler
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Main entry point for scheduled tasks.
 * Run via cron: * * * * * php /path/to/pricetag/cron/scheduler.php >> /path/to/storage/logs/cron.log 2>&1
 */

declare(strict_types=1);

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('CLI access only');
}

// Bootstrap the application
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Services\Scheduler;

echo "[" . date('Y-m-d H:i:s') . "] Cron scheduler started\n";

try {
    $scheduler = new Scheduler();
    $scheduler->run();
    echo "[" . date('Y-m-d H:i:s') . "] Cron scheduler completed\n";
} catch (Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
    logMessage('error', 'Cron scheduler failed', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    exit(1);
}
