-- Migration: Create quotes and quote_items tables
-- Date: 2026-02-13

CREATE TABLE IF NOT EXISTS `quotes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `quote_number` VARCHAR(50) NOT NULL,
    `status` ENUM('draft', 'submitted', 'reviewed', 'accepted', 'rejected', 'expired', 'converted') NOT NULL DEFAULT 'draft',
    `title` VARCHAR(255) DEFAULT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `contact_name` VARCHAR(200) DEFAULT NULL,
    `contact_email` VARCHAR(255) DEFAULT NULL,
    `contact_phone` VARCHAR(20) DEFAULT NULL,
    `company_name` VARCHAR(255) DEFAULT NULL,
    `customer_notes` TEXT DEFAULT NULL,
    `admin_notes` TEXT DEFAULT NULL,
    `valid_until` DATE DEFAULT NULL,
    `converted_order_id` INT UNSIGNED DEFAULT NULL,
    `converted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `quotes_quote_number_unique` (`quote_number`),
    KEY `quotes_user_idx` (`user_id`),
    KEY `quotes_status_idx` (`status`),
    KEY `quotes_created_idx` (`created_at`),
    CONSTRAINT `quotes_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `quotes_order_fk` FOREIGN KEY (`converted_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quote_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quote_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `variant_id` INT UNSIGNED DEFAULT NULL,
    `sku` VARCHAR(100) DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `variant_name` VARCHAR(255) DEFAULT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `quote_items_quote_idx` (`quote_id`),
    KEY `quote_items_product_idx` (`product_id`),
    CONSTRAINT `quote_items_quote_fk` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `quote_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
    CONSTRAINT `quote_items_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
