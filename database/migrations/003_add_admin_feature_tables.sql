-- ============================================================================
-- Migration: Add Admin Feature Tables
-- Adds tables for: banners, home_sections, coupons, newsletter, reviews,
-- redirects, search_terms, search_synonyms, contact_submissions, vendors
-- ============================================================================

-- Banners & Sliders
CREATE TABLE IF NOT EXISTS `banners` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `location` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `subtitle` TEXT DEFAULT NULL,
    `image` VARCHAR(255) NOT NULL,
    `mobile_image` VARCHAR(255) DEFAULT NULL,
    `url` VARCHAR(255) DEFAULT NULL,
    `button_text` VARCHAR(100) DEFAULT NULL,
    `text_color` VARCHAR(7) DEFAULT '#ffffff',
    `overlay_opacity` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `starts_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `banners_location_idx` (`location`),
    KEY `banners_active_idx` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Home Page Sections
CREATE TABLE IF NOT EXISTS `home_sections` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `subtitle` TEXT DEFAULT NULL,
    `config` JSON DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `home_sections_type_idx` (`type`),
    KEY `home_sections_active_order_idx` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupons
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL,
    `type` ENUM('percentage', 'fixed', 'free_shipping') NOT NULL DEFAULT 'percentage',
    `value` DECIMAL(10,2) NOT NULL,
    `minimum_amount` DECIMAL(10,2) DEFAULT NULL,
    `maximum_discount` DECIMAL(10,2) DEFAULT NULL,
    `usage_limit` INT UNSIGNED DEFAULT NULL,
    `usage_per_user` INT UNSIGNED DEFAULT 1,
    `used_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `starts_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `coupons_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupon Usage
CREATE TABLE IF NOT EXISTS `coupon_usage` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `coupon_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `coupon_usage_coupon_idx` (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter Subscribers
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'subscribed', 'unsubscribed') NOT NULL DEFAULT 'pending',
    `token` VARCHAR(100) DEFAULT NULL,
    `subscribed_at` TIMESTAMP NULL DEFAULT NULL,
    `unsubscribed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `newsletter_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `order_item_id` INT UNSIGNED DEFAULT NULL,
    `rating` TINYINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `content` TEXT DEFAULT NULL,
    `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
    `helpful_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `reviews_product_idx` (`product_id`),
    KEY `reviews_user_idx` (`user_id`),
    KEY `reviews_rating_idx` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search Terms
CREATE TABLE IF NOT EXISTS `search_terms` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `term` VARCHAR(255) NOT NULL,
    `count` INT UNSIGNED NOT NULL DEFAULT 1,
    `results_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `redirect_url` VARCHAR(255) DEFAULT NULL,
    `last_searched_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `search_terms_term_unique` (`term`),
    KEY `search_terms_count_idx` (`count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search Synonyms
CREATE TABLE IF NOT EXISTS `search_synonyms` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `terms` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- URL Redirects
CREATE TABLE IF NOT EXISTS `redirects` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `from_url` VARCHAR(255) NOT NULL,
    `to_url` VARCHAR(255) NOT NULL,
    `status_code` SMALLINT UNSIGNED NOT NULL DEFAULT 301,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `redirects_from_url_unique` (`from_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact Submissions
CREATE TABLE IF NOT EXISTS `contact_submissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `subject` VARCHAR(255) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `contact_submissions_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vendors
CREATE TABLE IF NOT EXISTS `vendors` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `api_endpoint` VARCHAR(255) DEFAULT NULL,
    `api_key` VARCHAR(255) DEFAULT NULL,
    `sync_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `last_sync_at` TIMESTAMP NULL DEFAULT NULL,
    `commission_rate` DECIMAL(5,2) DEFAULT NULL,
    `status` ENUM('pending', 'active', 'suspended') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `vendors_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings (ensure it exists with proper structure)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `key` VARCHAR(100) NOT NULL,
    `value` LONGTEXT DEFAULT NULL,
    `type` ENUM('string', 'integer', 'boolean', 'json', 'text', 'image') NOT NULL DEFAULT 'string',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `settings_group_key_unique` (`group`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default home sections if table is empty
INSERT IGNORE INTO `home_sections` (`type`, `title`, `config`, `sort_order`, `is_active`) VALUES
('hero', NULL, '{"slides": []}', 1, 1),
('trust_badges', 'Why Shop With Us', '{}', 2, 1),
('featured_categories', 'Shop by Category', '{"columns": 6}', 3, 1),
('trending_products', 'Trending Now', '{"limit": 8}', 4, 1),
('banner', NULL, '{}', 5, 0),
('new_arrivals', 'New Arrivals', '{"limit": 8}', 6, 1),
('testimonials', 'What Our Customers Say', '{}', 7, 0),
('newsletter', 'Stay Updated', '{"subtitle": "Subscribe to our newsletter for exclusive deals"}', 8, 1);
