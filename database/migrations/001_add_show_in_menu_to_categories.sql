-- Migration: Add show_in_menu column to categories table
-- This fixes the 500 error when accessing admin categories

ALTER TABLE `categories`
ADD COLUMN `show_in_menu` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`;

-- Add index for menu queries
ALTER TABLE `categories`
ADD INDEX `categories_menu_idx` (`show_in_menu`);
