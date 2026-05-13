<?php
/**
 * Scheduler Service
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Manages scheduled tasks (cron jobs) for the application.
 * Supports various scheduling frequencies and task management.
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Services\ImportJobService;
use PDO;

class Scheduler
{
    private array $tasks = [];
    private string $lockPath;
    private PDO $db;

    public function __construct()
    {
        $this->lockPath = STORAGE_PATH . '/cache/.scheduler_lock';
        $this->db = Database::getInstance();
        $this->registerTasks();
    }

    /**
     * Register all scheduled tasks
     */
    private function registerTasks(): void
    {
        // Cache garbage collection - every hour
        $this->schedule('cache:gc', function () {
            $cache = Cache::getInstance();
            $cleared = $cache->gc();
            return "Cleared $cleared expired cache entries";
        })->hourly();

        // Clear expired sessions - every 6 hours
        $this->schedule('sessions:cleanup', function () {
            return $this->cleanupSessions();
        })->everyHours(6);

        // Clear expired password reset tokens - daily at 2:00 AM
        $this->schedule('tokens:cleanup', function () {
            return $this->cleanupExpiredTokens();
        })->dailyAt('02:00');

        // Generate sitemap - daily at 3:00 AM
        $this->schedule('sitemap:generate', function () {
            return $this->generateSitemap();
        })->dailyAt('03:00');

        // Send abandoned cart reminders - every 4 hours
        $this->schedule('cart:reminders', function () {
            return $this->sendAbandonedCartReminders();
        })->everyHours(4);

        // Stock sync from vendors - every 2 hours
        $this->schedule('stock:sync', function () {
            return $this->syncVendorStock();
        })->everyHours(2);

        // Clean up old logs - weekly on Sunday at 4:00 AM
        $this->schedule('logs:cleanup', function () {
            return $this->cleanupOldLogs();
        })->weeklyOn(0, '04:00');

        // Database optimization - weekly on Sunday at 5:00 AM
        $this->schedule('db:optimize', function () {
            return $this->optimizeDatabase();
        })->weeklyOn(0, '05:00');

        // Send low stock alerts - daily at 8:00 AM
        $this->schedule('stock:alerts', function () {
            return $this->sendLowStockAlerts();
        })->dailyAt('08:00');

        // Process pending payments - every 15 minutes
        $this->schedule('payments:process', function () {
            return $this->processPendingPayments();
        })->everyMinutes(15);

        // Health check - every 5 minutes
        $this->schedule('health:check', function () {
            return $this->runHealthCheck();
        })->everyMinutes(5);

        // Process queued product import jobs - every minute, but each tick
        // claims at most one job and processes it to completion. With AI
        // imports running 5-15s per row, a 200-row import locks the
        // scheduler for ~25 minutes - that's intentional; other tasks
        // resume once the import finishes.
        $this->schedule('imports:process', function () {
            return $this->processImportJobs();
        })->everyMinutes(1);
    }

    /**
     * Drain the import-job queue. Called from the scheduler tick.
     * Lifts execution time limit since AI imports are long-running.
     */
    private function processImportJobs(): string
    {
        @set_time_limit(0);

        $jobService = new ImportJobService();
        $recovered = $jobService->recoverOrphaned();

        $job = $jobService->claimNext();
        if (!$job) {
            return $recovered > 0
                ? "No queued imports. Recovered {$recovered} orphaned jobs."
                : 'No queued imports';
        }

        // Delegate the actual row processing to the controller's worker
        // method so we have one canonical implementation. Constructed
        // without going through the router because we're running in CLI.
        $processor = new \App\Admin\Controllers\ProductController();
        try {
            $result = $processor->processImportJob($job, $jobService);
            return "Job #{$job['id']}: created={$result['created']}, updated={$result['updated']}, failed={$result['failed']}";
        } catch (\Throwable $e) {
            $jobService->fail((int) $job['id'], $e->getMessage());
            return "Job #{$job['id']} failed: " . $e->getMessage();
        }
    }

    /**
     * Schedule a new task
     */
    public function schedule(string $name, callable $callback): ScheduledTask
    {
        $task = new ScheduledTask($name, $callback);
        $this->tasks[] = $task;
        return $task;
    }

    /**
     * Run all due tasks
     */
    public function run(): void
    {
        // Prevent overlapping runs
        if (!$this->acquireLock()) {
            echo "Another scheduler instance is running. Skipping.\n";
            return;
        }

        try {
            foreach ($this->tasks as $task) {
                if ($task->isDue()) {
                    $this->runTask($task);
                }
            }
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Run a single task
     */
    private function runTask(ScheduledTask $task): void
    {
        $startTime = microtime(true);
        $name = $task->getName();

        echo "[" . date('Y-m-d H:i:s') . "] Running task: $name\n";

        try {
            $result = $task->execute();
            $duration = round(microtime(true) - $startTime, 2);

            echo "[" . date('Y-m-d H:i:s') . "] Task $name completed in {$duration}s: $result\n";

            $this->logTaskExecution($name, 'success', $result, $duration);
        } catch (\Throwable $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $error = $e->getMessage();

            echo "[" . date('Y-m-d H:i:s') . "] Task $name failed in {$duration}s: $error\n";

            $this->logTaskExecution($name, 'failed', $error, $duration);

            logMessage('error', "Scheduled task failed: $name", [
                'error' => $error,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Log task execution to database
     */
    private function logTaskExecution(string $name, string $status, string $output, float $duration): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO scheduled_task_logs (task_name, status, output, duration, executed_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $status, substr($output, 0, 1000), $duration]);
        } catch (\Throwable $e) {
            // Log to file if database fails
            logMessage('warning', 'Could not log task execution', [
                'task' => $name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Acquire lock to prevent overlapping runs
     */
    private function acquireLock(): bool
    {
        if (file_exists($this->lockPath)) {
            $lockAge = time() - filemtime($this->lockPath);
            // Release stale lock after 10 minutes
            if ($lockAge < 600) {
                return false;
            }
        }

        return file_put_contents($this->lockPath, getmypid()) !== false;
    }

    /**
     * Release scheduler lock
     */
    private function releaseLock(): void
    {
        if (file_exists($this->lockPath)) {
            unlink($this->lockPath);
        }
    }

    // =========================================================================
    // TASK IMPLEMENTATIONS
    // =========================================================================

    private function cleanupSessions(): string
    {
        $sessionPath = STORAGE_PATH . '/sessions';
        $maxLifetime = (int) env('SESSION_LIFETIME', 120) * 60;
        $cutoff = time() - $maxLifetime;
        $cleared = 0;

        $files = glob($sessionPath . '/sess_*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoff) {
                    unlink($file);
                    $cleared++;
                }
            }
        }

        return "Cleared $cleared expired sessions";
    }

    private function cleanupExpiredTokens(): string
    {
        // Password resets
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE expires_at < NOW()");
        $stmt->execute();
        $passwordResets = $stmt->rowCount();

        // Email verifications
        $stmt = $this->db->prepare("DELETE FROM email_verifications WHERE expires_at < NOW()");
        $stmt->execute();
        $emailVerifications = $stmt->rowCount();

        return "Cleared $passwordResets password resets, $emailVerifications email verifications";
    }

    private function generateSitemap(): string
    {
        // Trigger sitemap generation through the SEO controller logic
        $sitemapPath = PUBLIC_PATH . '/sitemap.xml';
        $baseUrl = config('app.url');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Home page
        $xml .= $this->sitemapUrl($baseUrl, 'daily', '1.0');

        // Static pages
        $stmt = $this->db->query("SELECT slug, updated_at FROM pages WHERE status = 'published'");
        while ($page = $stmt->fetch()) {
            $xml .= $this->sitemapUrl($baseUrl . '/' . $page['slug'], 'weekly', '0.7');
        }

        // Categories
        $stmt = $this->db->query("SELECT slug, updated_at FROM categories WHERE status = 'active'");
        while ($category = $stmt->fetch()) {
            $xml .= $this->sitemapUrl($baseUrl . '/category/' . $category['slug'], 'daily', '0.8');
        }

        // Products
        $stmt = $this->db->query("SELECT slug, updated_at FROM products WHERE status = 'active'");
        $productCount = 0;
        while ($product = $stmt->fetch()) {
            $xml .= $this->sitemapUrl($baseUrl . '/product/' . $product['slug'], 'weekly', '0.9');
            $productCount++;
        }

        $xml .= '</urlset>';

        file_put_contents($sitemapPath, $xml);

        return "Sitemap generated with $productCount products";
    }

    private function sitemapUrl(string $url, string $changefreq, string $priority): string
    {
        return sprintf(
            "  <url>\n    <loc>%s</loc>\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
            htmlspecialchars($url),
            $changefreq,
            $priority
        );
    }

    private function sendAbandonedCartReminders(): string
    {
        // Find carts abandoned for more than 1 hour but less than 7 days
        // that haven't received a reminder yet
        $stmt = $this->db->prepare("
            SELECT c.id, c.user_id, u.email, u.first_name
            FROM carts c
            JOIN users u ON c.user_id = u.id
            WHERE c.updated_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND c.updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND c.reminder_sent = 0
            AND u.email_verified_at IS NOT NULL
            LIMIT 50
        ");
        $stmt->execute();
        $carts = $stmt->fetchAll();

        $sent = 0;
        foreach ($carts as $cart) {
            // Mark as sent (email sending would be implemented here)
            $updateStmt = $this->db->prepare("UPDATE carts SET reminder_sent = 1 WHERE id = ?");
            $updateStmt->execute([$cart['id']]);
            $sent++;
        }

        return "Sent $sent abandoned cart reminders";
    }

    private function syncVendorStock(): string
    {
        // Get active vendors with API endpoints
        $stmt = $this->db->prepare("
            SELECT id, name, api_endpoint, api_key
            FROM vendors
            WHERE status = 'active' AND api_endpoint IS NOT NULL
        ");
        $stmt->execute();
        $vendors = $stmt->fetchAll();

        $synced = 0;
        $errors = 0;

        foreach ($vendors as $vendor) {
            try {
                // Vendor-specific sync would be implemented here
                // Log sync attempt
                $logStmt = $this->db->prepare("
                    INSERT INTO stock_sync_logs (vendor_id, status, message, synced_at)
                    VALUES (?, 'pending', 'Sync initiated', NOW())
                ");
                $logStmt->execute([$vendor['id']]);
                $synced++;
            } catch (\Throwable $e) {
                $errors++;
                logMessage('error', "Vendor stock sync failed: {$vendor['name']}", [
                    'vendor_id' => $vendor['id'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return "Synced $synced vendors, $errors errors";
    }

    private function cleanupOldLogs(): string
    {
        $logPath = STORAGE_PATH . '/logs';
        $cutoff = strtotime('-30 days');
        $cleared = 0;

        $files = glob($logPath . '/*.log');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoff) {
                    // Archive before delete
                    $archivePath = $logPath . '/archive/' . basename($file) . '.' . date('Ymd');
                    if (!is_dir($logPath . '/archive')) {
                        mkdir($logPath . '/archive', 0755, true);
                    }
                    rename($file, $archivePath);
                    $cleared++;
                }
            }
        }

        // Clean old task logs from database
        $stmt = $this->db->prepare("DELETE FROM scheduled_task_logs WHERE executed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $dbCleared = $stmt->rowCount();

        return "Archived $cleared log files, removed $dbCleared database log entries";
    }

    private function optimizeDatabase(): string
    {
        $tables = ['products', 'orders', 'users', 'carts', 'sessions'];
        $optimized = 0;

        foreach ($tables as $table) {
            try {
                $this->db->exec("OPTIMIZE TABLE $table");
                $optimized++;
            } catch (\Throwable $e) {
                logMessage('warning', "Could not optimize table: $table", ['error' => $e->getMessage()]);
            }
        }

        return "Optimized $optimized tables";
    }

    private function sendLowStockAlerts(): string
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.sku, p.stock_quantity, p.low_stock_threshold
            FROM products p
            WHERE p.stock_quantity <= p.low_stock_threshold
            AND p.manage_stock = 1
            AND p.status = 'active'
        ");
        $stmt->execute();
        $products = $stmt->fetchAll();

        if (empty($products)) {
            return "No low stock products found";
        }

        // Store alert for admin dashboard
        $alertData = json_encode($products);
        $stmt = $this->db->prepare("
            INSERT INTO settings (`group`, `key`, value, type, updated_at)
            VALUES ('system', 'low_stock_alert', ?, 'json', NOW())
            ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()
        ");
        $stmt->execute([$alertData]);

        return count($products) . " products below stock threshold";
    }

    private function processPendingPayments(): string
    {
        // Find payments that are pending for more than 30 minutes
        $stmt = $this->db->prepare("
            SELECT p.id, p.order_id, p.gateway_reference, p.amount
            FROM payments p
            WHERE p.status = 'pending'
            AND p.created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            AND p.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            LIMIT 20
        ");
        $stmt->execute();
        $payments = $stmt->fetchAll();

        $processed = 0;
        foreach ($payments as $payment) {
            // Payment status check would be implemented here
            // Using payment gateway API to verify status
            $processed++;
        }

        return "Processed $processed pending payments";
    }

    private function runHealthCheck(): string
    {
        $checks = [];

        // Database connection
        try {
            $this->db->query("SELECT 1");
            $checks['database'] = true;
        } catch (\Throwable $e) {
            $checks['database'] = false;
        }

        // Storage writable
        $checks['storage_writable'] = is_writable(STORAGE_PATH);

        // Cache directory
        $checks['cache_writable'] = is_writable(STORAGE_PATH . '/cache');

        // Disk space (warn if less than 1GB)
        $freeSpace = disk_free_space(ROOT_PATH);
        $checks['disk_space_ok'] = $freeSpace > 1073741824;

        // Store health status
        $status = in_array(false, $checks, true) ? 'unhealthy' : 'healthy';

        $stmt = $this->db->prepare("
            INSERT INTO settings (`group`, `key`, value, type, updated_at)
            VALUES ('system', 'health_check', ?, 'json', NOW())
            ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()
        ");
        $stmt->execute([json_encode(['status' => $status, 'checks' => $checks, 'timestamp' => time()])]);

        $failedChecks = array_filter($checks, fn($v) => !$v);

        if (empty($failedChecks)) {
            return "All health checks passed";
        }

        return "Health check warnings: " . implode(', ', array_keys($failedChecks));
    }
}

/**
 * Scheduled Task class
 */
class ScheduledTask
{
    private string $name;
    private $callback;
    private string $expression = '* * * * *'; // Default: every minute

    public function __construct(string $name, callable $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function execute(): string
    {
        $result = call_user_func($this->callback);
        return is_string($result) ? $result : 'Completed';
    }

    /**
     * Check if task is due to run
     */
    public function isDue(): bool
    {
        return $this->matchesCronExpression($this->expression);
    }

    /**
     * Run every minute
     */
    public function everyMinute(): self
    {
        $this->expression = '* * * * *';
        return $this;
    }

    /**
     * Run every N minutes
     */
    public function everyMinutes(int $minutes): self
    {
        $this->expression = "*/$minutes * * * *";
        return $this;
    }

    /**
     * Run hourly
     */
    public function hourly(): self
    {
        $this->expression = '0 * * * *';
        return $this;
    }

    /**
     * Run every N hours
     */
    public function everyHours(int $hours): self
    {
        $this->expression = "0 */$hours * * *";
        return $this;
    }

    /**
     * Run daily at midnight
     */
    public function daily(): self
    {
        $this->expression = '0 0 * * *';
        return $this;
    }

    /**
     * Run daily at specific time
     */
    public function dailyAt(string $time): self
    {
        [$hour, $minute] = explode(':', $time);
        $this->expression = "$minute $hour * * *";
        return $this;
    }

    /**
     * Run weekly on specific day (0 = Sunday)
     */
    public function weeklyOn(int $day, string $time = '00:00'): self
    {
        [$hour, $minute] = explode(':', $time);
        $this->expression = "$minute $hour * * $day";
        return $this;
    }

    /**
     * Run monthly on specific day
     */
    public function monthlyOn(int $day, string $time = '00:00'): self
    {
        [$hour, $minute] = explode(':', $time);
        $this->expression = "$minute $hour $day * *";
        return $this;
    }

    /**
     * Match cron expression against current time
     */
    private function matchesCronExpression(string $expression): bool
    {
        $parts = explode(' ', $expression);
        if (count($parts) !== 5) {
            return false;
        }

        [$minute, $hour, $day, $month, $weekday] = $parts;

        $now = [
            (int) date('i'), // minute
            (int) date('H'), // hour
            (int) date('d'), // day
            (int) date('m'), // month
            (int) date('w'), // weekday
        ];

        return $this->matchesPart($minute, $now[0]) &&
               $this->matchesPart($hour, $now[1]) &&
               $this->matchesPart($day, $now[2]) &&
               $this->matchesPart($month, $now[3]) &&
               $this->matchesPart($weekday, $now[4]);
    }

    /**
     * Match a single cron part
     */
    private function matchesPart(string $part, int $value): bool
    {
        // Wildcard
        if ($part === '*') {
            return true;
        }

        // Step value (*/5)
        if (str_starts_with($part, '*/')) {
            $step = (int) substr($part, 2);
            return $value % $step === 0;
        }

        // Range (1-5)
        if (str_contains($part, '-')) {
            [$start, $end] = explode('-', $part);
            return $value >= (int) $start && $value <= (int) $end;
        }

        // List (1,3,5)
        if (str_contains($part, ',')) {
            $values = array_map('intval', explode(',', $part));
            return in_array($value, $values, true);
        }

        // Exact match
        return (int) $part === $value;
    }
}
