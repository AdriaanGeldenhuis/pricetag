-- ============================================================================
-- Migration: Add Scheduled Publishing to Products
-- Date: 2026-02-06
-- Description: Adds publish_at and unpublish_at columns for scheduling
-- ============================================================================

-- Add publish_at column for scheduled product publishing
ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `publish_at` TIMESTAMP NULL DEFAULT NULL AFTER `status`;

-- Add unpublish_at column for scheduled product unpublishing
ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `unpublish_at` TIMESTAMP NULL DEFAULT NULL AFTER `publish_at`;

-- Add index for efficient querying of scheduled products
CREATE INDEX IF NOT EXISTS `idx_products_publish_at` ON `products` (`publish_at`);
CREATE INDEX IF NOT EXISTS `idx_products_unpublish_at` ON `products` (`unpublish_at`);

-- Add composite index for scheduled publishing cron job queries
CREATE INDEX IF NOT EXISTS `idx_products_scheduled_publishing` ON `products` (`status`, `publish_at`, `unpublish_at`);
