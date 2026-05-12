-- ============================================================================
-- Migration: AI Usage Log
-- Date: 2026-05-12
-- Description: Track every OpenAI API call so admins can see cost over time
--              and the application can warn when daily spend crosses a
--              configurable threshold. Previously the `usage` field from
--              OpenAI responses was discarded entirely.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `ai_usage_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `endpoint` VARCHAR(64) NOT NULL,
    `model` VARCHAR(64) NOT NULL,
    `prompt_tokens` INT UNSIGNED NOT NULL DEFAULT 0,
    `completion_tokens` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_tokens` INT UNSIGNED NOT NULL DEFAULT 0,
    `estimated_cost_usd` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    PRIMARY KEY (`id`),
    KEY `idx_ai_usage_created_at` (`created_at`),
    KEY `idx_ai_usage_model` (`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
