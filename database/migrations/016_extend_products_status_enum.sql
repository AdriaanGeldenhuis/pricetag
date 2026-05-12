-- ============================================================================
-- Migration: Extend products.status ENUM to include 'deleted'
-- Date: 2026-05-12
-- Description: ProductService::deleteProduct() and bulkDelete() set
--              status='deleted' as part of soft delete, but the ENUM defined
--              in schema.sql omitted that value. On strict-mode MySQL this
--              threw "Data truncated"; on non-strict mode the status was
--              silently set to ''. Adding 'deleted' aligns the schema with
--              the code path.
-- ============================================================================

ALTER TABLE `products`
    MODIFY COLUMN `status`
    ENUM('draft', 'active', 'inactive', 'out_of_stock', 'deleted')
    NOT NULL DEFAULT 'draft';
