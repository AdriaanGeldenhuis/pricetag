-- ============================================================================
-- Migration: Add Soft Delete to Products
-- Date: 2026-02-06
-- Description: Adds deleted_at column for soft delete functionality
-- ============================================================================

-- Add deleted_at column to products table
ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- Add index for efficient filtering of non-deleted products
CREATE INDEX IF NOT EXISTS `idx_products_deleted_at` ON `products` (`deleted_at`);

-- Add composite index for common queries filtering by status and soft delete
CREATE INDEX IF NOT EXISTS `idx_products_status_deleted` ON `products` (`status`, `deleted_at`);
