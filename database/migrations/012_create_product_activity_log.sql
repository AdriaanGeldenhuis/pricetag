-- ============================================================================
-- Migration: Create Product Activity Log
-- Date: 2026-02-06
-- Description: Creates product_activities table for tracking product changes
-- ============================================================================

CREATE TABLE IF NOT EXISTS `product_activities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` ENUM('created', 'updated', 'deleted', 'restored', 'duplicated') NOT NULL,
    `changes` JSON DEFAULT NULL COMMENT 'JSON object containing old and new values for changed fields',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IPv4 or IPv6 address',
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_activities_product` (`product_id`),
    KEY `idx_product_activities_user` (`user_id`),
    KEY `idx_product_activities_action` (`action`),
    KEY `idx_product_activities_created` (`created_at`),
    KEY `idx_product_activities_product_action` (`product_id`, `action`),
    CONSTRAINT `fk_product_activities_product`
        FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_product_activities_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
