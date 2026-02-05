-- Migration: Add product import/export logs table
-- Date: 2026-02-05

CREATE TABLE IF NOT EXISTS product_import_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('import', 'export') NOT NULL,
    filename VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'running', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    total_products INT UNSIGNED DEFAULT 0,
    created_products INT UNSIGNED DEFAULT 0,
    updated_products INT UNSIGNED DEFAULT 0,
    failed_products INT UNSIGNED DEFAULT 0,
    errors JSON DEFAULT NULL,
    download_url VARCHAR(255) DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
