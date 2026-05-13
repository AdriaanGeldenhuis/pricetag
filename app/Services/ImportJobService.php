<?php
/**
 * ImportJobService - background queue for bulk product imports
 * Pricetag.co.za
 *
 * Bulk AI-mode imports of 100+ products take 10-25 minutes (5-15s per AI
 * call). Running that synchronously over AJAX ties the import to an open
 * browser tab and risks timing out at the front-end load balancer.
 *
 * This service persists the parsed payload to product_import_jobs and lets
 * the cron scheduler pick it up. The admin UI polls for progress.
 *
 * Heartbeat semantics: a running job updates last_heartbeat_at every batch.
 * recoverOrphaned() resets jobs whose heartbeat is older than 10 minutes
 * back to 'queued' so a dead worker doesn't leave them stuck.
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

class ImportJobService
{
    private const ORPHAN_THRESHOLD_MINUTES = 10;

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Run a SQL statement with optional bound params. Wraps the
     * prepare/execute dance so callers can chain ->fetch() / ->fetchAll()
     * / ->fetchColumn() / ->rowCount() like they would on a raw
     * PDO::query() result.
     */
    private function q(string $sql, array $params = []): \PDOStatement
    {
        if (empty($params)) {
            return $this->db->query($sql);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Add a new import to the queue. Returns the job id.
     */
    public function enqueue(array $payload, ?int $userId = null): int
    {
        $rows = is_array($payload['data'] ?? null) ? count($payload['data']) : 0;
        $aiService = (string) ($payload['ai_service'] ?? 'none');

        $this->q(
            "INSERT INTO product_import_jobs
             (status, payload, total_rows, ai_service, created_by_user_id, created_at)
             VALUES ('queued', ?, ?, ?, ?, NOW())",
            [
                json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                $rows,
                $aiService,
                $userId,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Atomically claim the next queued job. Returns null if nothing's queued
     * or another worker beat us to it.
     */
    public function claimNext(): ?array
    {
        $this->db->beginTransaction();
        try {
            $row = $this->q(
                "SELECT * FROM product_import_jobs
                 WHERE status = 'queued'
                 ORDER BY created_at ASC
                 LIMIT 1
                 FOR UPDATE"
            )->fetch();

            if (!$row) {
                $this->db->rollBack();
                return null;
            }

            $this->q(
                "UPDATE product_import_jobs
                 SET status = 'running', started_at = NOW(), last_heartbeat_at = NOW()
                 WHERE id = ? AND status = 'queued'",
                [$row['id']]
            );
            $this->db->commit();

            $row['payload'] = json_decode($row['payload'], true) ?: [];
            return $row;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('ImportJobService::claimNext failed: ' . $e->getMessage());
            return null;
        }
    }

    public function heartbeat(int $jobId, array $progress): void
    {
        try {
            $this->q(
                "UPDATE product_import_jobs
                 SET processed_rows = ?, created_products = ?, updated_products = ?,
                     failed_products = ?, errors = ?, last_heartbeat_at = NOW()
                 WHERE id = ?",
                [
                    (int) ($progress['processed'] ?? 0),
                    (int) ($progress['created'] ?? 0),
                    (int) ($progress['updated'] ?? 0),
                    (int) ($progress['failed'] ?? 0),
                    !empty($progress['errors']) ? json_encode(array_slice($progress['errors'], 0, 200)) : null,
                    $jobId,
                ]
            );
        } catch (\Throwable $e) {
            error_log('ImportJobService::heartbeat failed: ' . $e->getMessage());
        }
    }

    public function complete(int $jobId, array $progress, ?int $importLogId = null): void
    {
        $this->q(
            "UPDATE product_import_jobs
             SET status = 'completed', completed_at = NOW(), last_heartbeat_at = NOW(),
                 processed_rows = ?, created_products = ?, updated_products = ?,
                 failed_products = ?, errors = ?, import_log_id = ?
             WHERE id = ?",
            [
                (int) ($progress['processed'] ?? 0),
                (int) ($progress['created'] ?? 0),
                (int) ($progress['updated'] ?? 0),
                (int) ($progress['failed'] ?? 0),
                !empty($progress['errors']) ? json_encode(array_slice($progress['errors'], 0, 200)) : null,
                $importLogId,
                $jobId,
            ]
        );
    }

    public function fail(int $jobId, string $reason, array $progress = []): void
    {
        $this->q(
            "UPDATE product_import_jobs
             SET status = 'failed', completed_at = NOW(), error_message = ?,
                 processed_rows = ?, created_products = ?, updated_products = ?,
                 failed_products = ?, errors = ?
             WHERE id = ?",
            [
                substr($reason, 0, 65535),
                (int) ($progress['processed'] ?? 0),
                (int) ($progress['created'] ?? 0),
                (int) ($progress['updated'] ?? 0),
                (int) ($progress['failed'] ?? 0),
                !empty($progress['errors']) ? json_encode(array_slice($progress['errors'], 0, 200)) : null,
                $jobId,
            ]
        );
    }

    /**
     * Reset jobs whose heartbeat is stale - the worker crashed or was killed.
     * Returns the number of jobs recovered.
     */
    public function recoverOrphaned(): int
    {
        $stmt = $this->q(
            "UPDATE product_import_jobs
             SET status = 'queued', started_at = NULL, last_heartbeat_at = NULL
             WHERE status = 'running'
               AND last_heartbeat_at IS NOT NULL
               AND last_heartbeat_at < (NOW() - INTERVAL ? MINUTE)",
            [self::ORPHAN_THRESHOLD_MINUTES]
        );
        return $stmt ? (int) $stmt->rowCount() : 0;
    }

    public function get(int $jobId): ?array
    {
        $row = $this->q(
            "SELECT id, status, total_rows, processed_rows, created_products, updated_products,
                    failed_products, errors, ai_service, import_log_id, error_message,
                    created_at, started_at, completed_at, last_heartbeat_at
             FROM product_import_jobs WHERE id = ? LIMIT 1",
            [$jobId]
        )->fetch();
        if (!$row) {
            return null;
        }
        if (!empty($row['errors'])) {
            $row['errors'] = json_decode($row['errors'], true) ?: [];
        } else {
            $row['errors'] = [];
        }
        return $row;
    }

    public function cancel(int $jobId): bool
    {
        $stmt = $this->q(
            "UPDATE product_import_jobs
             SET status = 'cancelled', completed_at = NOW()
             WHERE id = ? AND status IN ('queued', 'running')",
            [$jobId]
        );
        return $stmt && $stmt->rowCount() > 0;
    }

    public function hasActiveJobs(): bool
    {
        $count = (int) $this->q(
            "SELECT COUNT(*) FROM product_import_jobs WHERE status IN ('queued', 'running')"
        )->fetchColumn();
        return $count > 0;
    }
}
