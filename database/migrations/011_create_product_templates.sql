-- ============================================================================
-- Migration: Create Product Templates
-- Date: 2026-02-06
-- Description: Creates product_templates table for reusable product templates
-- ============================================================================

CREATE TABLE IF NOT EXISTS `product_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `default_data` JSON DEFAULT NULL COMMENT 'Default values for product fields (price, stock, attributes, etc.)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_templates_category` (`category_id`),
    KEY `idx_product_templates_name` (`name`),
    CONSTRAINT `fk_product_templates_category`
        FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
