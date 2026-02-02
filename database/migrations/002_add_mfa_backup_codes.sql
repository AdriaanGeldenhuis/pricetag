-- ============================================================================
-- Migration: Add MFA Backup Codes Support
-- Pricetag.co.za - Enterprise E-commerce Platform
-- Version: 1.0.2
-- ============================================================================

-- Create MFA backup codes table
CREATE TABLE IF NOT EXISTS `user_mfa_backup_codes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `code_hash` VARCHAR(255) NOT NULL,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_mfa_backup_codes_user_idx` (`user_id`),
    CONSTRAINT `user_mfa_backup_codes_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add device_fingerprint column to user_sessions if not exists
-- (The column already exists from the initial schema, this is just for safety)
-- ALTER TABLE `user_sessions` ADD COLUMN IF NOT EXISTS `device_fingerprint` VARCHAR(255) DEFAULT NULL AFTER `user_agent`;
