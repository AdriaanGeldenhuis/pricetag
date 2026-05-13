-- ============================================================================
-- Migration: Product Import Jobs - background queue
-- Date: 2026-05-13
-- Description: Persists bulk imports so they can run in the background via
--              the cron scheduler instead of being tied to an open browser
--              tab. The full parsed payload (rows + options) goes into
--              `payload`; the scheduler picks queued jobs and processes them
--              one at a time, updating `processed_rows` so the UI can poll
--              for progress.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `product_import_jobs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `status` ENUM('queued', 'running', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'queued',
    `payload` JSON NOT NULL,
    `total_rows` INT UNSIGNED NOT NULL DEFAULT 0,
    `processed_rows` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_products` INT UNSIGNED NOT NULL DEFAULT 0,
    `updated_products` INT UNSIGNED NOT NULL DEFAULT 0,
    `failed_products` INT UNSIGNED NOT NULL DEFAULT 0,
    `errors` JSON DEFAULT NULL,
    `import_log_id` BIGINT UNSIGNED DEFAULT NULL,
    `ai_service` VARCHAR(32) NOT NULL DEFAULT 'none',
    `created_by_user_id` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `started_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `last_heartbeat_at` TIMESTAMP NULL DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_import_jobs_status` (`status`),
    KEY `idx_import_jobs_created` (`created_at`),
    KEY `idx_import_jobs_heartbeat` (`last_heartbeat_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
