-- ============================================================================
-- Migration: Create SKU Settings
-- Date: 2026-02-06
-- Description: Creates sku_patterns table for configurable SKU generation
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sku_patterns` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `pattern` VARCHAR(255) NOT NULL COMMENT 'Pattern template e.g., {prefix}-{number}, {category}-{number}',
    `prefix` VARCHAR(50) DEFAULT NULL COMMENT 'Static prefix for SKU generation',
    `next_number` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Next auto-increment number to use',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sku_patterns_name` (`name`),
    KEY `idx_sku_patterns_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a default SKU pattern
INSERT INTO `sku_patterns` (`name`, `pattern`, `prefix`, `next_number`, `is_default`)
VALUES ('Default', '{prefix}{number}', 'SKU-', 1, 1)
ON DUPLICATE KEY UPDATE `name` = `name`;
