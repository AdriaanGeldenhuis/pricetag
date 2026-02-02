-- ============================================================================
-- Pricetag.co.za Database Schema
-- Enterprise E-commerce Platform
-- MySQL 8.0+
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- USERS & AUTHENTICATION
-- ============================================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` ENUM('customer', 'vendor', 'admin', 'super_admin') NOT NULL DEFAULT 'customer',
    `status` ENUM('pending', 'active', 'suspended', 'deleted') NOT NULL DEFAULT 'pending',
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `mfa_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `mfa_secret` VARCHAR(255) DEFAULT NULL,
    `remember_token` VARCHAR(100) DEFAULT NULL,
    `password_changed_at` TIMESTAMP NULL DEFAULT NULL,
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `last_login_ip` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_role_idx` (`role`),
    KEY `users_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('billing', 'shipping') NOT NULL DEFAULT 'shipping',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `company` VARCHAR(255) DEFAULT NULL,
    `address_line_1` VARCHAR(255) NOT NULL,
    `address_line_2` VARCHAR(255) DEFAULT NULL,
    `city` VARCHAR(100) NOT NULL,
    `province` VARCHAR(100) NOT NULL,
    `postal_code` VARCHAR(20) NOT NULL,
    `country` VARCHAR(2) NOT NULL DEFAULT 'ZA',
    `phone` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_addresses_user_idx` (`user_id`),
    CONSTRAINT `user_addresses_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `password_resets_email_idx` (`email`),
    KEY `password_resets_token_idx` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_verifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `email_verifications_user_idx` (`user_id`),
    KEY `email_verifications_token_idx` (`token`),
    CONSTRAINT `email_verifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `session_id` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `device_fingerprint` VARCHAR(255) DEFAULT NULL,
    `last_activity` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_sessions_user_idx` (`user_id`),
    KEY `user_sessions_session_idx` (`session_id`),
    CONSTRAINT `user_sessions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `login_attempts_email_idx` (`email`),
    KEY `login_attempts_ip_idx` (`ip_address`),
    KEY `login_attempts_created_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `type` VARCHAR(50) NOT NULL,
    `description` TEXT NOT NULL,
    `subject_type` VARCHAR(100) DEFAULT NULL,
    `subject_id` INT UNSIGNED DEFAULT NULL,
    `properties` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `activity_logs_user_idx` (`user_id`),
    KEY `activity_logs_type_idx` (`type`),
    KEY `activity_logs_created_idx` (`created_at`),
    CONSTRAINT `activity_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CATEGORIES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(255) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` TEXT DEFAULT NULL,
    `meta_keywords` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_menu` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `categories_slug_unique` (`slug`),
    KEY `categories_parent_idx` (`parent_id`),
    KEY `categories_active_idx` (`is_active`),
    KEY `categories_menu_idx` (`show_in_menu`),
    CONSTRAINT `categories_parent_fk` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ATTRIBUTES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `attributes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `type` ENUM('select', 'color', 'size', 'text') NOT NULL DEFAULT 'select',
    `is_filterable` TINYINT(1) NOT NULL DEFAULT 1,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attributes_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attribute_values` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `attribute_id` INT UNSIGNED NOT NULL,
    `value` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `color_code` VARCHAR(7) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `attribute_values_attribute_idx` (`attribute_id`),
    CONSTRAINT `attribute_values_attribute_fk` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PRODUCTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sku` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `type` ENUM('simple', 'variable', 'bundle', 'digital') NOT NULL DEFAULT 'simple',
    `status` ENUM('draft', 'active', 'inactive', 'out_of_stock') NOT NULL DEFAULT 'draft',

    -- Pricing
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `compare_price` DECIMAL(10,2) DEFAULT NULL,
    `cost_price` DECIMAL(10,2) DEFAULT NULL,

    -- Stock
    `manage_stock` TINYINT(1) NOT NULL DEFAULT 1,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `low_stock_threshold` INT DEFAULT 5,
    `backorders_allowed` TINYINT(1) NOT NULL DEFAULT 0,
    `lead_time_days` INT DEFAULT NULL,

    -- Physical properties
    `weight` DECIMAL(8,2) DEFAULT NULL,
    `length` DECIMAL(8,2) DEFAULT NULL,
    `width` DECIMAL(8,2) DEFAULT NULL,
    `height` DECIMAL(8,2) DEFAULT NULL,

    -- Vendor
    `vendor_id` INT UNSIGNED DEFAULT NULL,
    `vendor_sku` VARCHAR(100) DEFAULT NULL,

    -- SEO
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` TEXT DEFAULT NULL,
    `meta_keywords` VARCHAR(255) DEFAULT NULL,

    -- Flags
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_new` TINYINT(1) NOT NULL DEFAULT 0,
    `is_on_sale` TINYINT(1) NOT NULL DEFAULT 0,
    `is_taxable` TINYINT(1) NOT NULL DEFAULT 1,

    -- Stats
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `sold_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `rating_average` DECIMAL(3,2) DEFAULT NULL,
    `rating_count` INT UNSIGNED NOT NULL DEFAULT 0,

    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `products_sku_unique` (`sku`),
    UNIQUE KEY `products_slug_unique` (`slug`),
    KEY `products_status_idx` (`status`),
    KEY `products_type_idx` (`type`),
    KEY `products_price_idx` (`price`),
    KEY `products_vendor_idx` (`vendor_id`),
    FULLTEXT KEY `products_search` (`name`, `description`, `sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_categories` (
    `product_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`product_id`, `category_id`),
    KEY `product_categories_category_idx` (`category_id`),
    CONSTRAINT `product_categories_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product_categories_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `product_images_product_idx` (`product_id`),
    CONSTRAINT `product_images_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_attributes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `attribute_id` INT UNSIGNED NOT NULL,
    `attribute_value_id` INT UNSIGNED DEFAULT NULL,
    `custom_value` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `product_attributes_product_idx` (`product_id`),
    KEY `product_attributes_attribute_idx` (`attribute_id`),
    CONSTRAINT `product_attributes_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product_attributes_attribute_fk` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product_attributes_value_fk` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `sku` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `compare_price` DECIMAL(10,2) DEFAULT NULL,
    `cost_price` DECIMAL(10,2) DEFAULT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `image_id` INT UNSIGNED DEFAULT NULL,
    `weight` DECIMAL(8,2) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `product_variants_sku_unique` (`sku`),
    KEY `product_variants_product_idx` (`product_id`),
    CONSTRAINT `product_variants_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product_variants_image_fk` FOREIGN KEY (`image_id`) REFERENCES `product_images` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_variant_attributes` (
    `variant_id` INT UNSIGNED NOT NULL,
    `attribute_id` INT UNSIGNED NOT NULL,
    `attribute_value_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`variant_id`, `attribute_id`),
    KEY `product_variant_attributes_value_idx` (`attribute_value_id`),
    CONSTRAINT `product_variant_attributes_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product_variant_attributes_attribute_fk` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product_variant_attributes_value_fk` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_related` (
    `product_id` INT UNSIGNED NOT NULL,
    `related_id` INT UNSIGNED NOT NULL,
    `type` ENUM('related', 'cross_sell', 'up_sell') NOT NULL DEFAULT 'related',
    PRIMARY KEY (`product_id`, `related_id`),
    KEY `product_related_related_idx` (`related_id`),
    CONSTRAINT `product_related_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product_related_related_fk` FOREIGN KEY (`related_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CART & WISHLIST
-- ============================================================================

CREATE TABLE IF NOT EXISTS `carts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `session_id` VARCHAR(255) DEFAULT NULL,
    `coupon_code` VARCHAR(50) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `carts_user_idx` (`user_id`),
    KEY `carts_session_idx` (`session_id`),
    CONSTRAINT `carts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cart_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `variant_id` INT UNSIGNED DEFAULT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL,
    `options` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `cart_items_cart_idx` (`cart_id`),
    KEY `cart_items_product_idx` (`product_id`),
    CONSTRAINT `cart_items_cart_fk` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `cart_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `cart_items_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `variant_id` INT UNSIGNED DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `wishlists_user_product_unique` (`user_id`, `product_id`, `variant_id`),
    KEY `wishlists_product_idx` (`product_id`),
    CONSTRAINT `wishlists_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `wishlists_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `wishlists_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ORDERS & PAYMENTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `order_number` VARCHAR(50) NOT NULL,
    `invoice_number` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    `payment_status` ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'pending',

    -- Totals
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `shipping_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Coupon
    `coupon_code` VARCHAR(50) DEFAULT NULL,

    -- Customer info (snapshot)
    `customer_email` VARCHAR(255) NOT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,

    -- Billing address
    `billing_first_name` VARCHAR(100) NOT NULL,
    `billing_last_name` VARCHAR(100) NOT NULL,
    `billing_company` VARCHAR(255) DEFAULT NULL,
    `billing_address_1` VARCHAR(255) NOT NULL,
    `billing_address_2` VARCHAR(255) DEFAULT NULL,
    `billing_city` VARCHAR(100) NOT NULL,
    `billing_province` VARCHAR(100) NOT NULL,
    `billing_postal_code` VARCHAR(20) NOT NULL,
    `billing_country` VARCHAR(2) NOT NULL DEFAULT 'ZA',

    -- Shipping address
    `shipping_first_name` VARCHAR(100) NOT NULL,
    `shipping_last_name` VARCHAR(100) NOT NULL,
    `shipping_company` VARCHAR(255) DEFAULT NULL,
    `shipping_address_1` VARCHAR(255) NOT NULL,
    `shipping_address_2` VARCHAR(255) DEFAULT NULL,
    `shipping_city` VARCHAR(100) NOT NULL,
    `shipping_province` VARCHAR(100) NOT NULL,
    `shipping_postal_code` VARCHAR(20) NOT NULL,
    `shipping_country` VARCHAR(2) NOT NULL DEFAULT 'ZA',

    -- Shipping method
    `shipping_method` VARCHAR(100) DEFAULT NULL,
    `tracking_number` VARCHAR(255) DEFAULT NULL,
    `shipped_at` TIMESTAMP NULL DEFAULT NULL,
    `delivered_at` TIMESTAMP NULL DEFAULT NULL,

    -- Notes
    `customer_notes` TEXT DEFAULT NULL,
    `admin_notes` TEXT DEFAULT NULL,

    -- IP for fraud prevention
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,

    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `orders_order_number_unique` (`order_number`),
    UNIQUE KEY `orders_invoice_number_unique` (`invoice_number`),
    KEY `orders_user_idx` (`user_id`),
    KEY `orders_status_idx` (`status`),
    KEY `orders_payment_status_idx` (`payment_status`),
    KEY `orders_created_idx` (`created_at`),
    CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `variant_id` INT UNSIGNED DEFAULT NULL,
    `sku` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `variant_name` VARCHAR(255) DEFAULT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `options` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `order_items_order_idx` (`order_id`),
    KEY `order_items_product_idx` (`product_id`),
    CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
    CONSTRAINT `order_items_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `gateway` VARCHAR(50) NOT NULL DEFAULT 'yoco',
    `transaction_id` VARCHAR(255) DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'ZAR',
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `method` VARCHAR(50) DEFAULT NULL,
    `card_last_four` VARCHAR(4) DEFAULT NULL,
    `card_brand` VARCHAR(50) DEFAULT NULL,
    `error_code` VARCHAR(50) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `refunded_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `paid_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `payments_order_idx` (`order_id`),
    KEY `payments_transaction_idx` (`transaction_id`),
    KEY `payments_status_idx` (`status`),
    CONSTRAINT `payments_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_status_history` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `comment` TEXT DEFAULT NULL,
    `notify_customer` TINYINT(1) NOT NULL DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `order_status_history_order_idx` (`order_id`),
    CONSTRAINT `order_status_history_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `order_status_history_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- COUPONS
-- ============================================================================

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

CREATE TABLE IF NOT EXISTS `coupon_usage` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `coupon_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `coupon_usage_coupon_idx` (`coupon_id`),
    KEY `coupon_usage_user_idx` (`user_id`),
    CONSTRAINT `coupon_usage_coupon_fk` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `coupon_usage_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `coupon_usage_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- REVIEWS
-- ============================================================================

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
    KEY `reviews_rating_idx` (`rating`),
    CONSTRAINT `reviews_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CMS & MENUS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` TEXT DEFAULT NULL,
    `template` VARCHAR(100) DEFAULT 'default',
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menus` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `location` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `menus_location_unique` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `menu_id` INT UNSIGNED NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `type` ENUM('link', 'page', 'category', 'product') NOT NULL DEFAULT 'link',
    `reference_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `url` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(100) DEFAULT NULL,
    `badge_text` VARCHAR(50) DEFAULT NULL,
    `badge_color` VARCHAR(7) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `is_mega` TINYINT(1) NOT NULL DEFAULT 0,
    `mega_columns` TINYINT UNSIGNED NOT NULL DEFAULT 4,
    `open_in_new_tab` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `menu_items_menu_idx` (`menu_id`),
    KEY `menu_items_parent_idx` (`parent_id`),
    CONSTRAINT `menu_items_menu_fk` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
    CONSTRAINT `menu_items_parent_fk` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SETTINGS
-- ============================================================================

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

-- ============================================================================
-- HOME PAGE SECTIONS
-- ============================================================================

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

-- ============================================================================
-- BANNERS & SLIDERS
-- ============================================================================

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

-- ============================================================================
-- VENDORS (FUTURE-READY)
-- ============================================================================

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
    UNIQUE KEY `vendors_slug_unique` (`slug`),
    KEY `vendors_user_idx` (`user_id`),
    CONSTRAINT `vendors_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STOCK SYNC
-- ============================================================================

CREATE TABLE IF NOT EXISTS `stock_sync_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `vendor_id` INT UNSIGNED DEFAULT NULL,
    `type` ENUM('manual', 'scheduled', 'api') NOT NULL DEFAULT 'manual',
    `status` ENUM('pending', 'running', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `total_products` INT UNSIGNED NOT NULL DEFAULT 0,
    `updated_products` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_products` INT UNSIGNED NOT NULL DEFAULT 0,
    `failed_products` INT UNSIGNED NOT NULL DEFAULT 0,
    `errors` JSON DEFAULT NULL,
    `started_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `stock_sync_logs_vendor_idx` (`vendor_id`),
    KEY `stock_sync_logs_status_idx` (`status`),
    CONSTRAINT `stock_sync_logs_vendor_fk` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NEWSLETTER
-- ============================================================================

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

-- ============================================================================
-- SEARCH & SEO
-- ============================================================================

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

CREATE TABLE IF NOT EXISTS `search_synonyms` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `terms` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- ============================================================================
-- AI ASSISTANT
-- ============================================================================

CREATE TABLE IF NOT EXISTS `ai_conversations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `session_id` VARCHAR(255) NOT NULL,
    `context` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ai_conversations_user_idx` (`user_id`),
    KEY `ai_conversations_session_idx` (`session_id`),
    CONSTRAINT `ai_conversations_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversation_id` INT UNSIGNED NOT NULL,
    `role` ENUM('user', 'assistant', 'system') NOT NULL,
    `content` TEXT NOT NULL,
    `tokens_used` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ai_messages_conversation_idx` (`conversation_id`),
    CONSTRAINT `ai_messages_conversation_fk` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CONTACT & SUPPORT
-- ============================================================================

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

-- ============================================================================
-- INITIAL DATA
-- ============================================================================

-- Insert default settings
INSERT INTO `settings` (`group`, `key`, `value`, `type`) VALUES
('general', 'site_name', 'Pricetag', 'string'),
('general', 'site_tagline', 'Premium Online Shopping in South Africa', 'string'),
('general', 'contact_email', 'info@pricetag.co.za', 'string'),
('general', 'contact_phone', '', 'string'),
('general', 'contact_address', '', 'text'),
('general', 'vat_number', '', 'string'),
('general', 'currency', 'ZAR', 'string'),
('general', 'currency_symbol', 'R', 'string'),
('design', 'primary_color', '#2563eb', 'string'),
('design', 'secondary_color', '#1e40af', 'string'),
('design', 'accent_color', '#f59e0b', 'string'),
('design', 'logo', '', 'image'),
('design', 'favicon', '', 'image'),
('design', 'font_family', 'Inter', 'string'),
('seo', 'meta_title', 'Pricetag - Premium Online Shopping in South Africa', 'string'),
('seo', 'meta_description', 'Shop the best deals on electronics, fashion, home goods and more. Fast delivery across South Africa.', 'text'),
('seo', 'google_analytics', '', 'string'),
('seo', 'facebook_pixel', '', 'string'),
('social', 'facebook', '', 'string'),
('social', 'instagram', '', 'string'),
('social', 'twitter', '', 'string'),
('social', 'youtube', '', 'string'),
('shipping', 'free_shipping_threshold', '500', 'integer'),
('shipping', 'default_rate', '75', 'integer'),
('shipping', 'express_rate', '150', 'integer');

-- Insert default menu
INSERT INTO `menus` (`name`, `location`) VALUES
('Main Navigation', 'main'),
('Footer Links', 'footer'),
('Mobile Menu', 'mobile');

-- Insert default home sections
INSERT INTO `home_sections` (`type`, `title`, `config`, `sort_order`, `is_active`) VALUES
('hero', NULL, '{"slides": []}', 1, 1),
('trust_badges', 'Why Shop With Us', '{}', 2, 1),
('featured_categories', 'Shop by Category', '{"columns": 6}', 3, 1),
('trending_products', 'Trending Now', '{"limit": 8}', 4, 1),
('banner', NULL, '{}', 5, 0),
('new_arrivals', 'New Arrivals', '{"limit": 8}', 6, 1),
('testimonials', 'What Our Customers Say', '{}', 7, 0),
('newsletter', 'Stay Updated', '{"subtitle": "Subscribe to our newsletter for exclusive deals"}', 8, 1);

SET FOREIGN_KEY_CHECKS = 1;
