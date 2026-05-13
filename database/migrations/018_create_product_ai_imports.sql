-- ============================================================================
-- Migration: Product AI Imports - audit trail
-- Date: 2026-05-13
-- Description: One row per AI-generated product import. Stores the raw AI
--              response so the same SKU can be re-processed later without
--              re-calling the API, and so we have a full audit trail of
--              what the model produced vs. what was saved to the product.
--              Also tracks per-call token usage and cost.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `product_ai_imports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `import_log_id` BIGINT UNSIGNED DEFAULT NULL,
    `sku` VARCHAR(100) NOT NULL,
    `ai_service` VARCHAR(32) NOT NULL DEFAULT 'unknown',
    `ai_model` VARCHAR(64) NOT NULL DEFAULT '',
    `ai_method` VARCHAR(16) NOT NULL DEFAULT 'ai',
    `ai_identified` TINYINT(1) NOT NULL DEFAULT 0,
    `confidence` VARCHAR(16) NOT NULL DEFAULT 'unknown',
    `ai_response` JSON DEFAULT NULL,
    `image_url` VARCHAR(1024) DEFAULT NULL,
    `input_tokens` INT UNSIGNED NOT NULL DEFAULT 0,
    `output_tokens` INT UNSIGNED NOT NULL DEFAULT 0,
    `cache_read_tokens` INT UNSIGNED NOT NULL DEFAULT 0,
    `estimated_cost_usd` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    `fallback_reason` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ai_imports_product` (`product_id`),
    KEY `idx_ai_imports_sku` (`sku`),
    KEY `idx_ai_imports_log` (`import_log_id`),
    KEY `idx_ai_imports_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
