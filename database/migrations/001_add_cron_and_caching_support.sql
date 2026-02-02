-- ============================================================================
-- Migration: Add Cron and Caching Support
-- Pricetag.co.za - Enterprise E-commerce Platform
-- Version: 1.0.1
-- ============================================================================

-- Add expires_at to password_resets for token expiration
ALTER TABLE `password_resets`
ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `token`;

-- Add expires_at to email_verifications for token expiration
ALTER TABLE `email_verifications`
ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `token`;

-- Add reminder_sent to carts for abandoned cart tracking
ALTER TABLE `carts`
ADD COLUMN `reminder_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `notes`,
ADD COLUMN `reminder_sent_at` TIMESTAMP NULL DEFAULT NULL AFTER `reminder_sent`;

-- Create scheduled task logs table
CREATE TABLE IF NOT EXISTS `scheduled_task_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_name` VARCHAR(100) NOT NULL,
    `status` ENUM('success', 'failed', 'running') NOT NULL DEFAULT 'running',
    `output` TEXT DEFAULT NULL,
    `duration` DECIMAL(10,2) DEFAULT NULL,
    `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `scheduled_task_logs_task_idx` (`task_name`),
    KEY `scheduled_task_logs_status_idx` (`status`),
    KEY `scheduled_task_logs_executed_idx` (`executed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: settings table already has unique key on (group, key)
-- This is used by ON DUPLICATE KEY UPDATE for system settings

-- Create cache_items table for database-backed caching (optional)
CREATE TABLE IF NOT EXISTS `cache_items` (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMBLOB NOT NULL,
    `expiration` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
