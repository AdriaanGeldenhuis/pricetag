<?php
declare(strict_types=1);

/**
 * Admin Stock Sync Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles stock synchronization and imports
 */

namespace Admin\Controllers;

use App\Core\Controller;

class StockSyncController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get vendors with sync info
        $vendors = $db->query("
            SELECT v.*,
                   (SELECT COUNT(*) FROM products WHERE vendor_id = v.id) as product_count,
                   (SELECT MAX(completed_at) FROM stock_sync_logs WHERE vendor_id = v.id AND status = 'completed') as last_success
            FROM vendors v
            WHERE v.sync_enabled = 1
            ORDER BY v.name
        ")->fetchAll();

        // Get recent sync logs
        $recentLogs = $db->query("
            SELECT sl.*, v.name as vendor_name
            FROM stock_sync_logs sl
            LEFT JOIN vendors v ON sl.vendor_id = v.id
            ORDER BY sl.created_at DESC
            LIMIT 20
        ")->fetchAll();

        // Get sync statistics
        $stats = [
            'total_syncs' => (int) $db->query("SELECT COUNT(*) FROM stock_sync_logs")->fetchColumn(),
            'successful' => (int) $db->query("SELECT COUNT(*) FROM stock_sync_logs WHERE status = 'completed'")->fetchColumn(),
            'failed' => (int) $db->query("SELECT COUNT(*) FROM stock_sync_logs WHERE status = 'failed'")->fetchColumn(),
            'products_updated' => (int) $db->query("SELECT SUM(updated_products) FROM stock_sync_logs")->fetchColumn(),
        ];

        // Products needing attention
        $lowStock = $db->query("
            SELECT COUNT(*) FROM products
            WHERE manage_stock = 1
            AND stock_quantity <= low_stock_threshold
            AND stock_quantity > 0
        ")->fetchColumn();

        $outOfStock = $db->query("
            SELECT COUNT(*) FROM products
            WHERE manage_stock = 1
            AND stock_quantity = 0
        ")->fetchColumn();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/stock-sync/index', [
            'title' => 'Stock Synchronization',
            'vendors' => $vendors,
            'recentLogs' => $recentLogs,
            'stats' => $stats,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
        ]);
    }

    /**
     * Import stock from CSV file
     */
    public function import(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        if (empty($_FILES['file']['tmp_name'])) {
            flash('error', 'Please select a file to import.');
            $this->redirect('/admin/stock-sync');
            return;
        }

        $file = $_FILES['file'];

        // Validate file type
        $allowedTypes = ['text/csv', 'application/csv', 'text/plain'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            flash('error', 'Invalid file type. Please upload a CSV file.');
            $this->redirect('/admin/stock-sync');
            return;
        }

        $db = db();

        // Create sync log
        $db->query("
            INSERT INTO stock_sync_logs (type, status, started_at, created_at)
            VALUES ('manual', 'running', NOW(), NOW())
        ");
        $logId = $db->lastInsertId();

        try {
            $handle = fopen($file['tmp_name'], 'r');

            if (!$handle) {
                throw new \Exception('Could not open file.');
            }

            // Read header row
            $header = fgetcsv($handle);
            $header = array_map('strtolower', array_map('trim', $header));

            // Validate required columns
            $requiredColumns = ['sku'];
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $header)) {
                    throw new \Exception("Missing required column: {$col}");
                }
            }

            $skuIndex = array_search('sku', $header);
            $stockIndex = array_search('stock', $header) !== false ? array_search('stock', $header) : array_search('quantity', $header);
            $priceIndex = array_search('price', $header);

            $updated = 0;
            $created = 0;
            $failed = 0;
            $errors = [];
            $row = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $row++;

                if (count($data) < count($header)) {
                    $errors[] = "Row {$row}: Invalid number of columns";
                    $failed++;
                    continue;
                }

                $sku = trim($data[$skuIndex]);

                if (empty($sku)) {
                    $errors[] = "Row {$row}: SKU is empty";
                    $failed++;
                    continue;
                }

                // Find product
                $product = $db->query("SELECT id, stock_quantity FROM products WHERE sku = ?", [$sku])->fetch();

                if (!$product) {
                    // Create new product if name is provided
                    $nameIndex = array_search('name', $header);
                    if ($nameIndex !== false && !empty($data[$nameIndex])) {
                        try {
                            $slug = $this->generateSlug($data[$nameIndex]);
                            $stock = $stockIndex !== false ? (int) $data[$stockIndex] : 0;
                            $price = $priceIndex !== false ? (float) $data[$priceIndex] : 0;

                            $db->query("
                                INSERT INTO products (sku, name, slug, price, stock_quantity, status, created_at, updated_at)
                                VALUES (?, ?, ?, ?, ?, 'draft', NOW(), NOW())
                            ", [$sku, $data[$nameIndex], $slug, $price, $stock]);

                            $created++;
                        } catch (\Exception $e) {
                            $errors[] = "Row {$row}: Failed to create product - " . $e->getMessage();
                            $failed++;
                        }
                    } else {
                        $errors[] = "Row {$row}: Product with SKU '{$sku}' not found";
                        $failed++;
                    }
                    continue;
                }

                // Update product
                $updates = [];
                $params = [];

                if ($stockIndex !== false) {
                    $newStock = (int) $data[$stockIndex];
                    $updates[] = 'stock_quantity = ?';
                    $params[] = $newStock;
                }

                if ($priceIndex !== false && is_numeric($data[$priceIndex])) {
                    $updates[] = 'price = ?';
                    $params[] = (float) $data[$priceIndex];
                }

                if (!empty($updates)) {
                    $updates[] = 'updated_at = NOW()';
                    $params[] = $product['id'];

                    $db->query("UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?", $params);
                    $updated++;
                }
            }

            fclose($handle);

            // Update sync log
            $db->query("
                UPDATE stock_sync_logs SET
                    status = 'completed',
                    total_products = ?,
                    updated_products = ?,
                    created_products = ?,
                    failed_products = ?,
                    errors = ?,
                    completed_at = NOW()
                WHERE id = ?
            ", [
                $updated + $created + $failed,
                $updated,
                $created,
                $failed,
                !empty($errors) ? json_encode(array_slice($errors, 0, 100)) : null,
                $logId,
            ]);

            flash('success', "Import completed. Updated: {$updated}, Created: {$created}, Failed: {$failed}");

        } catch (\Exception $e) {
            // Update sync log with error
            $db->query("
                UPDATE stock_sync_logs SET
                    status = 'failed',
                    errors = ?,
                    completed_at = NOW()
                WHERE id = ?
            ", [json_encode([$e->getMessage()]), $logId]);

            flash('error', 'Import failed: ' . $e->getMessage());
        }

        $this->redirect('/admin/stock-sync');
    }

    /**
     * Run vendor API sync
     */
    public function run(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $vendorId = (int) ($_POST['vendor_id'] ?? 0);

        if (!$vendorId) {
            flash('error', 'Invalid vendor.');
            $this->redirect('/admin/stock-sync');
            return;
        }

        $db = db();

        $vendor = $db->query("SELECT * FROM vendors WHERE id = ? AND sync_enabled = 1", [$vendorId])->fetch();

        if (!$vendor) {
            flash('error', 'Vendor not found or sync not enabled.');
            $this->redirect('/admin/stock-sync');
            return;
        }

        // Create sync log
        $db->query("
            INSERT INTO stock_sync_logs (vendor_id, type, status, started_at, created_at)
            VALUES (?, 'api', 'running', NOW(), NOW())
        ", [$vendorId]);
        $logId = $db->lastInsertId();

        try {
            // Call vendor API
            $response = $this->callVendorApi($vendor);

            if (!$response['success']) {
                throw new \Exception($response['error'] ?? 'API call failed');
            }

            $products = $response['products'] ?? [];
            $updated = 0;
            $errors = [];

            foreach ($products as $item) {
                $sku = $item['sku'] ?? null;

                if (!$sku) {
                    continue;
                }

                $product = $db->query(
                    "SELECT id FROM products WHERE sku = ? OR vendor_sku = ?",
                    [$sku, $sku]
                )->fetch();

                if ($product) {
                    $db->query("
                        UPDATE products SET
                            stock_quantity = ?,
                            price = COALESCE(?, price),
                            updated_at = NOW()
                        WHERE id = ?
                    ", [
                        (int) ($item['stock'] ?? $item['quantity'] ?? 0),
                        isset($item['price']) ? (float) $item['price'] : null,
                        $product['id'],
                    ]);
                    $updated++;
                }
            }

            // Update sync log
            $db->query("
                UPDATE stock_sync_logs SET
                    status = 'completed',
                    total_products = ?,
                    updated_products = ?,
                    completed_at = NOW()
                WHERE id = ?
            ", [count($products), $updated, $logId]);

            // Update vendor last sync
            $db->query("UPDATE vendors SET last_sync_at = NOW() WHERE id = ?", [$vendorId]);

            flash('success', "Sync completed. Updated {$updated} products.");

        } catch (\Exception $e) {
            $db->query("
                UPDATE stock_sync_logs SET
                    status = 'failed',
                    errors = ?,
                    completed_at = NOW()
                WHERE id = ?
            ", [json_encode([$e->getMessage()]), $logId]);

            flash('error', 'Sync failed: ' . $e->getMessage());
        }

        $this->redirect('/admin/stock-sync');
    }

    /**
     * Download sample import template
     */
    public function template(): void
    {
        $filename = 'stock_import_template.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, ['sku', 'name', 'stock', 'price']);

        // Sample data
        fputcsv($output, ['SKU001', 'Sample Product 1', 100, 199.99]);
        fputcsv($output, ['SKU002', 'Sample Product 2', 50, 299.99]);
        fputcsv($output, ['SKU003', 'Sample Product 3', 0, 149.99]);

        fclose($output);
        exit;
    }

    /**
     * View sync log details
     */
    public function log(int $id): void
    {
        $db = db();

        $log = $db->query("
            SELECT sl.*, v.name as vendor_name
            FROM stock_sync_logs sl
            LEFT JOIN vendors v ON sl.vendor_id = v.id
            WHERE sl.id = ?
        ", [$id])->fetch();

        if (!$log) {
            flash('error', 'Log not found.');
            $this->redirect('/admin/stock-sync');
            return;
        }

        $log['errors'] = $log['errors'] ? json_decode($log['errors'], true) : [];

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/stock-sync/log', [
            'title' => 'Sync Log Details',
            'log' => $log,
        ]);
    }

    /**
     * Bulk stock update
     */
    public function bulkUpdate(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $products = $_POST['products'] ?? [];

        if (empty($products)) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'No products provided.'], 400);
                return;
            }
            flash('error', 'No products provided.');
            $this->redirect('/admin/stock-sync');
            return;
        }

        $db = db();
        $updated = 0;

        foreach ($products as $productId => $stock) {
            $db->query(
                "UPDATE products SET stock_quantity = ?, updated_at = NOW() WHERE id = ?",
                [(int) $stock, (int) $productId]
            );
            $updated++;
        }

        if (isAjax()) {
            $this->json(['success' => true, 'updated' => $updated, 'message' => "Updated {$updated} products."]);
            return;
        }

        flash('success', "Updated {$updated} products.");
        $this->redirect('/admin/stock-sync');
    }

    private function generateSlug(string $name): string
    {
        $db = db();
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $baseSlug = $slug;
        $counter = 1;

        while ($db->query("SELECT id FROM products WHERE slug = ?", [$slug])->fetch()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function callVendorApi(array $vendor): array
    {
        if (empty($vendor['api_endpoint'])) {
            return ['success' => false, 'error' => 'No API endpoint configured'];
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $vendor['api_endpoint'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . ($vendor['api_key'] ?? ''),
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "HTTP {$httpCode}"];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Invalid JSON response'];
        }

        return ['success' => true, 'products' => $data['products'] ?? $data];
    }
}
