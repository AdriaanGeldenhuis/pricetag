<?php
declare(strict_types=1);

/**
 * Admin Product Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\OpenAIService;
use App\Services\ClaudeService;
use App\Services\ImportJobService;

class ProductController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get filter parameters
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 100;
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $filter = $_GET['filter'] ?? '';

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = '(p.name LIKE ? OR p.sku LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($category) {
            $where[] = 'p.category_id = ?';
            $params[] = $category;
        }

        if ($status === 'active') {
            $where[] = 'p.status = 1';
        } elseif ($status === 'inactive') {
            $where[] = 'p.status = 0';
        }

        if ($filter === 'low_stock') {
            $where[] = 'p.stock <= p.low_stock_threshold AND p.stock > 0';
        } elseif ($filter === 'out_of_stock') {
            $where[] = 'p.stock = 0';
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(*) FROM products p WHERE {$whereClause}";
        $total = (int) $db->query($countSql, $params)->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        // Get products
        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT p.*, c.name as category_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $products = $db->query($sql, $params)->fetchAll();

        // Get categories for filter
        $categories = $db->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/products/index', [
            'title' => 'Products',
            'products' => $products,
            'categories' => $categories,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'count' => $total,
                'perPage' => $perPage,
            ],
            'filters' => [
                'search' => $search,
                'category' => $category,
                'status' => $status,
                'filter' => $filter,
            ],
        ]);
    }

    public function create(): void
    {
        $db = db();

        // Get categories
        $categories = $this->getCategoryTree($db);

        // Get attributes
        $attributes = $db->query("SELECT * FROM attributes ORDER BY name")->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/products/form', [
            'title' => 'Add Product',
            'product' => null,
            'categories' => $categories,
            'attributes' => $attributes,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:255',
            'sku' => 'required|max:100',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|numeric',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/products/create');
            return;
        }

        $db = db();

        // Check SKU uniqueness
        $existing = $db->query("SELECT id FROM products WHERE sku = ?", [$_POST['sku']])->fetch();
        if ($existing) {
            flash('error', 'A product with this SKU already exists.');
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/products/create');
            return;
        }

        // Generate slug
        $slug = $this->generateSlug($_POST['name']);

        // Insert product
        $sql = "
            INSERT INTO products (
                name, slug, sku, description, short_description, price, compare_price,
                cost_price, category_id, brand_id, stock, low_stock_threshold,
                weight, status, featured, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";

        $db->query($sql, [
            $_POST['name'],
            $slug,
            $_POST['sku'],
            $_POST['description'] ?? '',
            $_POST['short_description'] ?? '',
            (float) $_POST['price'],
            !empty($_POST['compare_price']) ? (float) $_POST['compare_price'] : null,
            !empty($_POST['cost_price']) ? (float) $_POST['cost_price'] : null,
            (int) $_POST['category_id'],
            !empty($_POST['brand_id']) ? (int) $_POST['brand_id'] : null,
            (int) ($_POST['stock'] ?? 0),
            (int) ($_POST['low_stock_threshold'] ?? 10),
            !empty($_POST['weight']) ? (float) $_POST['weight'] : null,
            isset($_POST['status']) ? 1 : 0,
            isset($_POST['featured']) ? 1 : 0,
        ]);

        $productId = $db->lastInsertId();

        // Handle images
        if (!empty($_FILES['images']['name'][0])) {
            $this->handleImageUploads($productId, $_FILES['images']);
        }

        // Handle attributes
        if (!empty($_POST['attributes'])) {
            $this->saveAttributes($productId, $_POST['attributes']);
        }

        flash('success', 'Product created successfully.');
        $this->redirect('/admin/products/' . $productId . '/edit');
    }

    public function edit(int $id): void
    {
        $db = db();

        $product = $db->query("SELECT * FROM products WHERE id = ?", [$id])->fetch();

        if (!$product) {
            flash('error', 'Product not found.');
            $this->redirect('/admin/products');
            return;
        }

        // Get product images
        $images = $db->query(
            "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id",
            [$id]
        )->fetchAll();

        // Get categories
        $categories = $this->getCategoryTree($db);

        // Get attributes with values
        $attributes = $db->query("SELECT * FROM attributes ORDER BY name")->fetchAll();
        $productAttributes = $db->query(
            "SELECT attribute_id, value FROM product_attributes WHERE product_id = ?",
            [$id]
        )->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/products/form', [
            'title' => 'Edit Product',
            'product' => $product,
            'images' => $images,
            'categories' => $categories,
            'attributes' => $attributes,
            'productAttributes' => $productAttributes,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $product = $db->query("SELECT * FROM products WHERE id = ?", [$id])->fetch();

        if (!$product) {
            flash('error', 'Product not found.');
            $this->redirect('/admin/products');
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:255',
            'sku' => 'required|max:100',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|numeric',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $this->redirect('/admin/products/' . $id . '/edit');
            return;
        }

        // Check SKU uniqueness (excluding current product)
        $existing = $db->query(
            "SELECT id FROM products WHERE sku = ? AND id != ?",
            [$_POST['sku'], $id]
        )->fetch();

        if ($existing) {
            flash('error', 'A product with this SKU already exists.');
            $this->redirect('/admin/products/' . $id . '/edit');
            return;
        }

        // Update slug if name changed
        $slug = $product['slug'];
        if ($_POST['name'] !== $product['name']) {
            $slug = $this->generateSlug($_POST['name'], $id);
        }

        // Update product
        $sql = "
            UPDATE products SET
                name = ?, slug = ?, sku = ?, description = ?, short_description = ?,
                price = ?, compare_price = ?, cost_price = ?, category_id = ?,
                brand_id = ?, stock = ?, low_stock_threshold = ?, weight = ?,
                status = ?, featured = ?, updated_at = NOW()
            WHERE id = ?
        ";

        $db->query($sql, [
            $_POST['name'],
            $slug,
            $_POST['sku'],
            $_POST['description'] ?? '',
            $_POST['short_description'] ?? '',
            (float) $_POST['price'],
            !empty($_POST['compare_price']) ? (float) $_POST['compare_price'] : null,
            !empty($_POST['cost_price']) ? (float) $_POST['cost_price'] : null,
            (int) $_POST['category_id'],
            !empty($_POST['brand_id']) ? (int) $_POST['brand_id'] : null,
            (int) ($_POST['stock'] ?? 0),
            (int) ($_POST['low_stock_threshold'] ?? 10),
            !empty($_POST['weight']) ? (float) $_POST['weight'] : null,
            isset($_POST['status']) ? 1 : 0,
            isset($_POST['featured']) ? 1 : 0,
            $id,
        ]);

        // Handle new images
        if (!empty($_FILES['images']['name'][0])) {
            $this->handleImageUploads($id, $_FILES['images']);
        }

        // Handle attributes
        if (!empty($_POST['attributes'])) {
            $this->saveAttributes($id, $_POST['attributes']);
        }

        flash('success', 'Product updated successfully.');
        $this->redirect('/admin/products/' . $id . '/edit');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        // Get product images for deletion
        $images = $db->query("SELECT image_url FROM product_images WHERE product_id = ?", [$id])->fetchAll();

        // Delete product (cascades to images, attributes via FK)
        $db->query("DELETE FROM products WHERE id = ?", [$id]);

        // Delete image files
        foreach ($images as $image) {
            $path = PUBLIC_PATH . $image['image_url'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        if (isAjax()) {
            $this->json(['success' => true]);
            return;
        }

        flash('success', 'Product deleted successfully.');
        $this->redirect('/admin/products');
    }

    private function getCategoryTree(\PDO $db, ?int $parentId = null, int $level = 0): array
    {
        $categories = [];
        $sql = "SELECT id, name FROM categories WHERE parent_id " . ($parentId ? "= ?" : "IS NULL") . " ORDER BY name";
        $params = $parentId ? [$parentId] : [];
        $rows = $db->query($sql, $params)->fetchAll();

        foreach ($rows as $row) {
            $row['level'] = $level;
            $row['name_display'] = str_repeat('— ', $level) . $row['name'];
            $categories[] = $row;
            $children = $this->getCategoryTree($db, $row['id'], $level + 1);
            $categories = array_merge($categories, $children);
        }

        return $categories;
    }

    private function generateSlug(string $name, ?int $excludeId = null): string
    {
        $db = db();
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM products WHERE slug = ?";
            $params = [$slug];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $existing = $db->query($sql, $params)->fetch();

            if (!$existing) {
                break;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function handleImageUploads(int $productId, array $files): void
    {
        $db = db();
        $uploadDir = PUBLIC_PATH . '/uploads/products/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $existingCount = (int) $db->query(
            "SELECT COUNT(*) FROM product_images WHERE product_id = ?",
            [$productId]
        )->fetchColumn();

        foreach ($files['name'] as $i => $name) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            $filename = $productId . '_' . time() . '_' . $i . '.' . $ext;
            $path = $uploadDir . $filename;

            if (move_uploaded_file($files['tmp_name'][$i], $path)) {
                $isPrimary = $existingCount === 0 && $i === 0;

                $db->query(
                    "INSERT INTO product_images (product_id, image_url, is_primary, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [$productId, '/uploads/products/' . $filename, $isPrimary ? 1 : 0, $existingCount + $i]
                );
            }
        }
    }

    private function saveAttributes(int $productId, array $attributes): void
    {
        $db = db();

        // Delete existing
        $db->query("DELETE FROM product_attributes WHERE product_id = ?", [$productId]);

        // Insert new
        foreach ($attributes as $attrId => $value) {
            if (!empty($value)) {
                $db->query(
                    "INSERT INTO product_attributes (product_id, attribute_id, value) VALUES (?, ?, ?)",
                    [$productId, $attrId, $value]
                );
            }
        }
    }

    /**
     * Show import/export page
     */
    public function importForm(): void
    {
        $db = db();

        // Get categories with product counts
        $categories = $db->query("
            SELECT c.*,
                   (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
            FROM categories c
            ORDER BY c.name
        ")->fetchAll();

        // Build category tree
        $categoryTree = $this->getCategoryTree($db);

        // Add product counts to tree
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['id']] = $cat['product_count'];
        }
        foreach ($categoryTree as &$cat) {
            $cat['product_count'] = $categoryMap[$cat['id']] ?? 0;
        }
        unset($cat);

        // Get total products
        $totalProducts = (int) $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

        // Get vendors for AI-mode default vendor selector
        $vendors = $db->query("
            SELECT id, name FROM vendors
            WHERE status IN ('active', 'pending')
            ORDER BY name
        ")->fetchAll();

        // Tax rate from store settings (default 15% for South Africa VAT)
        $taxRate = (float) (function_exists('getSetting') ? getSetting('tax_rate', 'store', '15') : '15');

        // Get recent import/export history
        $history = $db->query("
            SELECT * FROM product_import_logs
            ORDER BY created_at DESC
            LIMIT 10
        ")->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/products/import-export', [
            'title' => 'Import & Export Products',
            'categories' => $categoryTree,
            'totalProducts' => $totalProducts,
            'vendors' => $vendors ?: [],
            'taxRate' => $taxRate,
            'history' => $history ?: [],
        ]);
    }

    /**
     * Export products to CSV or JSON
     */
    public function export(): void
    {
        $db = db();

        // Get parameters
        $format = $_GET['format'] ?? 'csv';
        $status = $_GET['status'] ?? '';
        $stock = $_GET['stock'] ?? '';
        $featured = $_GET['featured'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $categories = $_GET['categories'] ?? [];
        $fields = $_GET['fields'] ?? ['sku', 'name', 'price', 'stock', 'category', 'status'];

        // Always include SKU
        if (!in_array('sku', $fields)) {
            array_unshift($fields, 'sku');
        }

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($status === 'active') {
            $where[] = 'p.status = 1';
        } elseif ($status === 'inactive') {
            $where[] = 'p.status = 0';
        }

        if ($stock === 'in_stock') {
            $where[] = 'p.stock > 0';
        } elseif ($stock === 'low_stock') {
            $where[] = 'p.stock <= p.low_stock_threshold AND p.stock > 0';
        } elseif ($stock === 'out_of_stock') {
            $where[] = 'p.stock = 0';
        }

        if ($featured !== '') {
            $where[] = 'p.featured = ?';
            $params[] = (int) $featured;
        }

        if ($dateFrom) {
            $where[] = 'p.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo) {
            $where[] = 'p.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }

        if (!empty($categories) && !isset($_GET['all_categories'])) {
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $where[] = "p.category_id IN ({$placeholders})";
            $params = array_merge($params, $categories);
        }

        $whereClause = implode(' AND ', $where);

        // Get products
        $sql = "
            SELECT p.*,
                   c.name as category_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image_url
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.id
        ";

        $products = $db->query($sql, $params)->fetchAll();

        // Map fields to export
        $fieldMap = [
            'sku' => 'sku',
            'name' => 'name',
            'slug' => 'slug',
            'price' => 'price',
            'compare_price' => 'compare_price',
            'cost_price' => 'cost_price',
            'stock' => 'stock',
            'category' => 'category_name',
            'description' => 'description',
            'short_description' => 'short_description',
            'weight' => 'weight',
            'status' => 'status',
            'featured' => 'featured',
            'image_url' => 'image_url',
            'created_at' => 'created_at',
        ];

        // Build export data
        $exportData = [];
        foreach ($products as $product) {
            $row = [];
            foreach ($fields as $field) {
                $dbField = $fieldMap[$field] ?? $field;
                $value = $product[$dbField] ?? '';

                // Format certain fields
                if ($field === 'status') {
                    $value = $product['status'] ? '1' : '0';
                } elseif ($field === 'featured') {
                    $value = $product['featured'] ? '1' : '0';
                } elseif ($field === 'image_url' && $value) {
                    // Make absolute URL
                    $value = url($value);
                }

                $row[$field] = $value;
            }
            $exportData[] = $row;
        }

        // Log export
        try {
            $db->query("
                INSERT INTO product_import_logs (type, filename, status, total_products, created_at)
                VALUES ('export', ?, 'completed', ?, NOW())
            ", [
                'products_export_' . date('Y-m-d_His') . '.' . $format,
                count($products)
            ]);
        } catch (\Exception $e) {
            // Log table might not exist, ignore
        }

        // Output based on format
        if ($format === 'json') {
            $this->exportJson($exportData);
        } else {
            $this->exportCsv($exportData, $fields);
        }
    }

    /**
     * Export as CSV
     */
    private function exportCsv(array $data, array $fields): void
    {
        $filename = 'products_export_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header row
        fputcsv($output, $fields);

        // Data rows
        foreach ($data as $row) {
            $values = [];
            foreach ($fields as $field) {
                $values[] = $row[$field] ?? '';
            }
            fputcsv($output, $values);
        }

        fclose($output);
        exit;
    }

    /**
     * Export as JSON
     */
    private function exportJson(array $data): void
    {
        $filename = 'products_export_' . date('Y-m-d_His') . '.json';

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        echo json_encode([
            'exported_at' => date('Y-m-d H:i:s'),
            'total' => count($data),
            'products' => $data
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * Import products from CSV or JSON
     */
    public function import(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        if (empty($_FILES['file']['tmp_name'])) {
            flash('error', 'Please select a file to import.');
            $this->redirect('/admin/products/import');
            return;
        }

        $file = $_FILES['file'];
        $updateExisting = isset($_POST['update_existing']);
        $createNew = isset($_POST['create_new']);
        $skipErrors = isset($_POST['skip_errors']);

        // Detect file type
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $isJson = $ext === 'json';

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedTypes = ['text/csv', 'application/csv', 'text/plain', 'application/json'];
        if (!in_array($mimeType, $allowedTypes) && !in_array($ext, ['csv', 'json'])) {
            flash('error', 'Invalid file type. Please upload a CSV or JSON file.');
            $this->redirect('/admin/products/import');
            return;
        }

        $db = db();

        // Create import log
        try {
            $db->query("
                INSERT INTO product_import_logs (type, filename, status, created_at)
                VALUES ('import', ?, 'running', NOW())
            ", [$file['name']]);
            $logId = $db->lastInsertId();
        } catch (\Exception $e) {
            $logId = null;
        }

        try {
            // Read and parse file
            $content = file_get_contents($file['tmp_name']);

            if ($isJson) {
                $data = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON file: ' . json_last_error_msg());
                }
                // Handle both array and {products: [...]} format
                if (isset($data['products'])) {
                    $data = $data['products'];
                }
                if (!is_array($data)) {
                    $data = [$data];
                }
            } else {
                $data = $this->parseCsvContent($content);
            }

            if (empty($data)) {
                throw new \Exception('No data found in file.');
            }

            // Process products
            $created = 0;
            $updated = 0;
            $failed = 0;
            $errors = [];

            // Get category map for name lookups
            $categoryMap = $this->getCategoryMap($db);

            foreach ($data as $index => $row) {
                $rowNum = $index + 2; // 1-based + header

                try {
                    // Normalize keys to lowercase
                    $row = array_change_key_case($row, CASE_LOWER);

                    // Get SKU
                    $sku = trim($row['sku'] ?? '');
                    if (empty($sku)) {
                        if ($skipErrors) {
                            $errors[] = "Row {$rowNum}: SKU is required";
                            $failed++;
                            continue;
                        }
                        throw new \Exception("Row {$rowNum}: SKU is required");
                    }

                    // Check if product exists
                    $existing = $db->query("SELECT id FROM products WHERE sku = ?", [$sku])->fetch();

                    if ($existing) {
                        // Update existing
                        if (!$updateExisting) {
                            $errors[] = "Row {$rowNum}: Product with SKU '{$sku}' exists, skipping (update disabled)";
                            continue;
                        }

                        $this->updateProductFromImport($db, $existing['id'], $row, $categoryMap);
                        $updated++;
                    } else {
                        // Create new
                        if (!$createNew) {
                            $errors[] = "Row {$rowNum}: Product with SKU '{$sku}' not found, skipping (create disabled)";
                            continue;
                        }

                        // Validate required fields for new product
                        $name = trim($row['name'] ?? '');
                        if (empty($name)) {
                            if ($skipErrors) {
                                $errors[] = "Row {$rowNum}: Name is required for new products";
                                $failed++;
                                continue;
                            }
                            throw new \Exception("Row {$rowNum}: Name is required for new products");
                        }

                        $this->createProductFromImport($db, $row, $categoryMap);
                        $created++;
                    }

                } catch (\Exception $e) {
                    if ($skipErrors) {
                        $errors[] = $e->getMessage();
                        $failed++;
                        continue;
                    }
                    throw $e;
                }
            }

            // Update log
            if ($logId) {
                $db->query("
                    UPDATE product_import_logs SET
                        status = 'completed',
                        total_products = ?,
                        created_products = ?,
                        updated_products = ?,
                        failed_products = ?,
                        errors = ?,
                        completed_at = NOW()
                    WHERE id = ?
                ", [
                    $created + $updated + $failed,
                    $created,
                    $updated,
                    $failed,
                    !empty($errors) ? json_encode(array_slice($errors, 0, 100)) : null,
                    $logId
                ]);
            }

            $message = "Import completed! Created: {$created}, Updated: {$updated}";
            if ($failed > 0) {
                $message .= ", Failed: {$failed}";
            }
            flash('success', $message);

        } catch (\Exception $e) {
            // Update log with error
            if ($logId) {
                $db->query("
                    UPDATE product_import_logs SET
                        status = 'failed',
                        errors = ?,
                        completed_at = NOW()
                    WHERE id = ?
                ", [json_encode([$e->getMessage()]), $logId]);
            }

            flash('error', 'Import failed: ' . $e->getMessage());
        }

        $this->redirect('/admin/products/import');
    }

    /**
     * Parse CSV content
     */
    private function parseCsvContent(string $content): array
    {
        $lines = preg_split('/\r?\n/', $content);
        $lines = array_filter($lines, fn($line) => trim($line) !== '');

        if (count($lines) < 2) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $data = [];
        foreach ($lines as $line) {
            $values = str_getcsv($line);
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = $values[$i] ?? '';
            }
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get category map for name lookups
     */
    private function getCategoryMap(\PDO $db): array
    {
        $categories = $db->query("SELECT id, name FROM categories")->fetchAll();
        $map = [];
        foreach ($categories as $cat) {
            $map[strtolower($cat['name'])] = $cat['id'];
            $map[$cat['id']] = $cat['id'];
        }
        return $map;
    }

    /**
     * Create product from import data
     */
    private function createProductFromImport(\PDO $db, array $row, array $categoryMap): int
    {
        $name = trim($row['name']);
        $sku = trim($row['sku']);
        $slug = $this->generateSlug($name);

        // Resolve category
        $categoryId = null;
        if (!empty($row['category'])) {
            $catKey = is_numeric($row['category']) ? (int) $row['category'] : strtolower(trim($row['category']));
            $categoryId = $categoryMap[$catKey] ?? null;
        }

        // Resolve vendor - either explicit vendor_id, or look up vendor name in vendors table
        $vendorId = null;
        if (!empty($row['vendor_id'])) {
            $vendorId = (int) $row['vendor_id'];
        } elseif (!empty($row['vendor'])) {
            $vendorValue = trim((string) $row['vendor']);
            if (is_numeric($vendorValue)) {
                $vendorId = (int) $vendorValue;
            } else {
                $stmt = $db->query("SELECT id FROM vendors WHERE name = ? LIMIT 1", [$vendorValue]);
                $found = $stmt ? $stmt->fetch() : null;
                $vendorId = $found ? (int) $found['id'] : null;
            }
        }

        // Status: accept string ('draft', 'active') or legacy int (0/1)
        $status = 0;
        if (isset($row['status'])) {
            if (is_string($row['status']) && !is_numeric($row['status'])) {
                $status = in_array($row['status'], ['active', 'inactive', 'draft', 'out_of_stock'], true)
                    ? $row['status'] : 'draft';
            } else {
                $status = (int) $row['status'];
            }
        }

        $db->query("
            INSERT INTO products (
                name, slug, sku, description, short_description,
                price, compare_price, cost_price, category_id, vendor_id,
                stock, low_stock_threshold, weight, length, width, height,
                meta_title, meta_description, meta_keywords,
                status, featured, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [
            $name,
            $slug,
            $sku,
            $row['description'] ?? '',
            $row['short_description'] ?? '',
            !empty($row['price']) ? (float) $row['price'] : 0,
            !empty($row['compare_price']) ? (float) $row['compare_price'] : null,
            !empty($row['cost_price']) ? (float) $row['cost_price'] : null,
            $categoryId,
            $vendorId,
            !empty($row['stock']) ? (int) $row['stock'] : 0,
            10,
            !empty($row['weight']) ? (float) $row['weight'] : null,
            !empty($row['length']) ? (float) $row['length'] : null,
            !empty($row['width']) ? (float) $row['width'] : null,
            !empty($row['height']) ? (float) $row['height'] : null,
            $row['meta_title'] ?? null,
            $row['meta_description'] ?? null,
            $row['meta_keywords'] ?? null,
            $status,
            isset($row['featured']) ? (int) $row['featured'] : 0,
        ]);

        $productId = (int) $db->lastInsertId();

        // Handle image URL
        if (!empty($row['image_url'])) {
            $this->importProductImage($db, $productId, $row['image_url']);
        }

        return $productId;
    }

    /**
     * Update product from import data
     */
    private function updateProductFromImport(\PDO $db, int $productId, array $row, array $categoryMap): void
    {
        $updates = [];
        $params = [];

        // Build dynamic update query based on provided fields
        $fieldMap = [
            'name' => 'name',
            'description' => 'description',
            'short_description' => 'short_description',
            'price' => 'price',
            'compare_price' => 'compare_price',
            'cost_price' => 'cost_price',
            'stock' => 'stock',
            'weight' => 'weight',
            'length' => 'length',
            'width' => 'width',
            'height' => 'height',
            'meta_title' => 'meta_title',
            'meta_description' => 'meta_description',
            'meta_keywords' => 'meta_keywords',
            'status' => 'status',
            'featured' => 'featured',
        ];

        foreach ($fieldMap as $csvField => $dbField) {
            if (isset($row[$csvField]) && $row[$csvField] !== '') {
                $value = $row[$csvField];

                // Type conversion
                if (in_array($dbField, ['price', 'compare_price', 'cost_price', 'weight', 'length', 'width', 'height'])) {
                    $value = (float) $value;
                } elseif ($dbField === 'status') {
                    if (is_string($value) && !is_numeric($value)) {
                        $value = in_array($value, ['active', 'inactive', 'draft', 'out_of_stock'], true) ? $value : 'draft';
                    } else {
                        $value = (int) $value;
                    }
                } elseif (in_array($dbField, ['stock', 'featured'])) {
                    $value = (int) $value;
                }

                $updates[] = "{$dbField} = ?";
                $params[] = $value;
            }
        }

        // Handle category
        if (!empty($row['category'])) {
            $catKey = is_numeric($row['category']) ? (int) $row['category'] : strtolower(trim($row['category']));
            $categoryId = $categoryMap[$catKey] ?? null;
            if ($categoryId) {
                $updates[] = "category_id = ?";
                $params[] = $categoryId;
            }
        }

        // Handle vendor
        $vendorId = null;
        if (!empty($row['vendor_id'])) {
            $vendorId = (int) $row['vendor_id'];
        } elseif (!empty($row['vendor'])) {
            $vendorValue = trim((string) $row['vendor']);
            if (is_numeric($vendorValue)) {
                $vendorId = (int) $vendorValue;
            } else {
                $stmt = $db->query("SELECT id FROM vendors WHERE name = ? LIMIT 1", [$vendorValue]);
                $found = $stmt ? $stmt->fetch() : null;
                $vendorId = $found ? (int) $found['id'] : null;
            }
        }
        if ($vendorId) {
            $updates[] = "vendor_id = ?";
            $params[] = $vendorId;
        }

        // Update slug if name changed
        if (!empty($row['name'])) {
            $slug = $this->generateSlug($row['name'], $productId);
            $updates[] = "slug = ?";
            $params[] = $slug;
        }

        if (!empty($updates)) {
            $updates[] = "updated_at = NOW()";
            $params[] = $productId;

            $db->query(
                "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?",
                $params
            );
        }

        // Handle image URL
        if (!empty($row['image_url'])) {
            $this->importProductImage($db, $productId, $row['image_url']);
        }
    }

    /**
     * Import product image from URL
     */
    /**
     * Enqueue a bulk import as a background job. Used by the front-end
     * when the import is too big to comfortably run synchronously (200+
     * AI-mode rows would tie up a browser tab for 20+ minutes). Returns
     * the new job_id; the client then polls importJobStatus().
     */
    public function importEnqueue(): void
    {
        header('Content-Type: application/json');

        if (!$this->validateCsrf()) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        $data = json_decode($_POST['data'] ?? '[]', true);
        if (empty($data) || !is_array($data)) {
            echo json_encode(['success' => false, 'error' => 'No data provided']);
            exit;
        }

        $options = $this->extractImportOptions($_POST);
        $payload = array_merge($options, ['data' => $data]);

        try {
            $jobService = new ImportJobService();
            $userId = $_SESSION['admin_user_id'] ?? $_SESSION['user_id'] ?? null;
            $jobId = $jobService->enqueue($payload, $userId ? (int) $userId : null);
            echo json_encode([
                'success' => true,
                'job_id' => $jobId,
                'total_rows' => count($data),
                'message' => 'Import queued. You can close this tab - it will keep running in the background.',
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Poll endpoint for a queued import. Returns status + progress so the
     * UI can render a live progress bar without keeping an HTTP connection
     * open for the full duration of the import.
     */
    public function importJobStatus(int $id): void
    {
        header('Content-Type: application/json');
        try {
            $job = (new ImportJobService())->get($id);
            if (!$job) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Job not found']);
                exit;
            }
            $progressPct = $job['total_rows'] > 0
                ? min(100, (int) round(($job['processed_rows'] / $job['total_rows']) * 100))
                : 0;
            echo json_encode([
                'success' => true,
                'job' => [
                    'id' => (int) $job['id'],
                    'status' => $job['status'],
                    'total_rows' => (int) $job['total_rows'],
                    'processed_rows' => (int) $job['processed_rows'],
                    'created' => (int) $job['created_products'],
                    'updated' => (int) $job['updated_products'],
                    'failed' => (int) $job['failed_products'],
                    'progress_pct' => $progressPct,
                    'errors' => $job['errors'],
                    'import_log_id' => $job['import_log_id'] ? (int) $job['import_log_id'] : null,
                    'ai_service' => $job['ai_service'],
                    'error_message' => $job['error_message'],
                    'created_at' => $job['created_at'],
                    'started_at' => $job['started_at'],
                    'completed_at' => $job['completed_at'],
                ],
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Detail page for one import log entry. Shows the summary plus every
     * AI call from product_ai_imports (per-row confidence, image, cost)
     * so the user can audit what happened.
     */
    public function importHistoryDetail(int $id): void
    {
        $db = db();
        $log = $db->query("SELECT * FROM product_import_logs WHERE id = ? LIMIT 1", [$id])->fetch();
        if (!$log) {
            http_response_code(404);
            echo 'Import log not found';
            exit;
        }

        $aiRows = [];
        try {
            $aiRows = $db->query(
                "SELECT pai.*, p.name AS product_name, p.status AS product_status
                 FROM product_ai_imports pai
                 LEFT JOIN products p ON p.id = pai.product_id
                 WHERE pai.created_at >= ? AND pai.created_at <= COALESCE(?, NOW())
                 ORDER BY pai.created_at ASC",
                [$log['created_at'], $log['completed_at'] ?? null]
            )->fetchAll();
        } catch (\Throwable $e) {
            // product_ai_imports table not yet migrated - show empty list
        }

        if (!empty($log['errors'])) {
            $log['errors'] = json_decode($log['errors'], true) ?: [];
        } else {
            $log['errors'] = [];
        }

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/products/import-history-detail', [
            'title' => 'Import #' . $log['id'],
            'log' => $log,
            'aiRows' => $aiRows,
        ]);
    }

    /**
     * Export rows that failed in a previous import as a CSV. The user can
     * re-upload this file to retry just the failed rows after fixing the
     * data (e.g. correcting a wrong SKU, picking a vendor).
     */
    public function exportFailedRows(int $id): void
    {
        $db = db();
        $log = $db->query("SELECT * FROM product_import_logs WHERE id = ? LIMIT 1", [$id])->fetch();
        if (!$log || empty($log['errors'])) {
            http_response_code(404);
            echo 'No failed rows for this import';
            exit;
        }

        $errors = json_decode($log['errors'], true) ?: [];
        $filename = 'import_' . $id . '_failed_rows.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['error_reason', 'sku_or_row']);
        foreach ($errors as $err) {
            // Errors look like "Row 5: AI could not identify SKU XYZ - created as draft"
            // Extract the SKU when present so the user can paste it back into a new import.
            $sku = '';
            if (preg_match('/SKU\s+([A-Z0-9\-]+)/i', $err, $m)) {
                $sku = $m[1];
            } elseif (preg_match('/Row\s+(\d+)/', $err, $m)) {
                $sku = 'Row ' . $m[1];
            }
            fputcsv($out, [$err, $sku]);
        }
        fclose($out);
        exit;
    }

    /**
     * Dry-run: call AI on a single row and return the parsed response
     * without saving anything. Lets the user check naming, image URL,
     * specs etc. for one product before kicking off a full bulk import.
     */
    public function importDryRun(): void
    {
        set_time_limit(120);
        header('Content-Type: application/json');

        if (!$this->validateCsrf()) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        $row = json_decode($_POST['row'] ?? '{}', true);
        if (!is_array($row)) {
            $row = [];
        }
        $marginPercent = max(0.0, (float) ($_POST['margin_percent'] ?? 0));
        $vatRate = max(0.0, (float) ($_POST['vat_rate'] ?? 0));

        $sku = trim((string) ($row['sku'] ?? ''));
        if ($sku === '') {
            echo json_encode(['success' => false, 'error' => 'SKU is required for dry-run']);
            exit;
        }

        try {
            [$aiService, $aiServiceName] = $this->resolveAiService();
            $aiResult = $aiService->generateCompleteProduct($sku, trim((string) ($row['short_description'] ?? '')), [
                'brand' => $row['brand'] ?? '',
                'category' => $row['category'] ?? '',
                'price' => $row['price'] ?? 0,
                'existingName' => $row['name'] ?? '',
                'bulk_import' => true,
            ]);

            $data = $aiResult['data'] ?? [];
            $cost = !empty($row['cost_price']) ? (float) $row['cost_price'] : null;
            $calculatedPrice = $cost !== null
                ? round($cost * (1 + $marginPercent / 100) * (1 + $vatRate / 100), 2)
                : null;

            echo json_encode([
                'success' => !empty($aiResult['success']),
                'ai_service' => $aiServiceName,
                'method' => $aiResult['method'] ?? 'unknown',
                'fallback_reason' => $aiResult['fallback_reason'] ?? null,
                'ai_identified' => !empty($data['ai_identified']),
                'preview' => [
                    'name' => $data['name'] ?? '',
                    'brand' => $this->normalizeBrand((string) ($data['brand'] ?? '')),
                    'suggested_category' => $data['suggested_category'] ?? '',
                    'short_description' => $data['short_description'] ?? '',
                    'description' => $data['description'] ?? '',
                    'image_url' => $data['image_url'] ?? '',
                    'image_candidates' => array_slice($data['image_candidates'] ?? [], 0, 4),
                    'specifications' => array_slice($data['specifications'] ?? [], 0, 12),
                    'attributes' => $data['attributes'] ?? [],
                    'weight' => $data['weight'] ?? null,
                    'dimensions' => trim(($data['length'] ?? '') . ' x ' . ($data['width'] ?? '') . ' x ' . ($data['height'] ?? ''), ' x'),
                    'is_new' => !empty($data['is_new']),
                ],
                'pricing' => [
                    'cost_excl_vat' => $cost,
                    'margin_percent' => $marginPercent,
                    'vat_rate' => $vatRate,
                    'calculated_sell_price_incl_vat' => $calculatedPrice,
                ],
                'usage' => $aiResult['usage'] ?? null,
            ], JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Pick the AI service for bulk imports. Claude (with web search) when
     * ANTHROPIC_API_KEY is configured, otherwise fall back to OpenAI so
     * existing installs keep working without an env change.
     */
    private function resolveAiService(): array
    {
        $claude = new ClaudeService();
        if ($claude->hasApiKey()) {
            return [$claude, 'claude'];
        }
        return [new OpenAIService(), 'openai'];
    }

    /**
     * Record one AI import attempt - kept separate from the row insert so the
     * same SKU can be re-imported later from the captured response without
     * paying for another AI call. Silently no-ops if the audit table doesn't
     * exist yet (migration 018 not applied) - existing installs keep working.
     */
    private function logAiImport(
        \PDO $db,
        ?int $productId,
        string $sku,
        string $aiService,
        array $aiResult
    ): void {
        try {
            $data = $aiResult['data'] ?? [];
            $usage = $aiResult['usage'] ?? [];
            $method = $aiResult['method'] ?? 'unknown';
            $identified = !empty($data['ai_identified']);
            $confidence = $identified ? 'high' : ($method === 'fallback' ? 'unknown' : 'low');

            // Strip image_candidates from the stored response - they can be 4+ huge URLs
            $storedData = $data;
            unset($storedData['image_candidates']);

            $db->query(
                "INSERT INTO product_ai_imports
                 (product_id, sku, ai_service, ai_model, ai_method, ai_identified, confidence,
                  ai_response, image_url, input_tokens, output_tokens, cache_read_tokens,
                  estimated_cost_usd, fallback_reason, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $productId,
                    $sku,
                    $aiService,
                    $usage['model'] ?? '',
                    $method,
                    $identified ? 1 : 0,
                    $confidence,
                    !empty($storedData) ? json_encode($storedData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                    !empty($data['image_url']) ? substr((string) $data['image_url'], 0, 1024) : null,
                    (int) ($usage['input_tokens'] ?? 0),
                    (int) ($usage['output_tokens'] ?? 0),
                    (int) ($usage['cache_read_input_tokens'] ?? 0),
                    $this->estimateAiCost($usage),
                    $aiResult['fallback_reason'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            // Audit table optional - log but don't break the import
            error_log("logAiImport skipped: " . $e->getMessage());
        }
    }

    private function estimateAiCost(array $usage): float
    {
        if (empty($usage['model'])) {
            return 0.0;
        }
        // Mirror ClaudeService::PRICING for cross-service consistency
        $rates = [
            'claude-sonnet-4-6' => [3.00, 15.00, 0.30],
            'claude-haiku-4-5'  => [1.00,  5.00, 0.10],
            'claude-opus-4-7'   => [5.00, 25.00, 0.50],
            'gpt-4o-mini'       => [0.15,  0.60, 0.00],
            'gpt-4o'            => [2.50, 10.00, 0.00],
        ][$usage['model']] ?? [3.00, 15.00, 0.30];
        $cost = (
            ($usage['input_tokens'] ?? 0) * $rates[0]
            + ($usage['output_tokens'] ?? 0) * $rates[1]
            + ($usage['cache_read_input_tokens'] ?? 0) * $rates[2]
        ) / 1_000_000;
        return round($cost, 6);
    }

    /**
     * Canonicalize brand strings before they're stored as attributes. AI
     * output is inconsistent ("HP" vs "HP Inc." vs "Hewlett-Packard") and
     * without this the storefront filter ends up with three duplicate
     * brand entries that all point at the same vendor.
     */
    private function normalizeBrand(string $brand): string
    {
        $brand = trim($brand);
        if ($brand === '') {
            return '';
        }
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $brand));

        $canonical = [
            'hp' => 'HP',
            'hpinc' => 'HP',
            'hewlettpackard' => 'HP',
            'hpe' => 'HPE',
            'dell' => 'Dell',
            'delltechnologies' => 'Dell',
            'asus' => 'ASUS',
            'asustek' => 'ASUS',
            'lenovo' => 'Lenovo',
            'apple' => 'Apple',
            'appleinc' => 'Apple',
            'samsung' => 'Samsung',
            'samsungelectronics' => 'Samsung',
            'lg' => 'LG',
            'lgelectronics' => 'LG',
            'sony' => 'Sony',
            'intel' => 'Intel',
            'amd' => 'AMD',
            'advancedmicrodevices' => 'AMD',
            'nvidia' => 'NVIDIA',
            'msi' => 'MSI',
            'gigabyte' => 'Gigabyte',
            'asrock' => 'ASRock',
            'corsair' => 'Corsair',
            'kingston' => 'Kingston',
            'westerndigital' => 'Western Digital',
            'wd' => 'Western Digital',
            'sandisk' => 'SanDisk',
            'seagate' => 'Seagate',
            'crucial' => 'Crucial',
            'tplink' => 'TP-Link',
            'mikrotik' => 'MikroTik',
            'cisco' => 'Cisco',
            'logitech' => 'Logitech',
            'razer' => 'Razer',
            'acer' => 'Acer',
            'msi' => 'MSI',
            'benq' => 'BenQ',
            'epson' => 'Epson',
            'canon' => 'Canon',
            'brother' => 'Brother',
            'huawei' => 'Huawei',
            'xiaomi' => 'Xiaomi',
            'redmi' => 'Xiaomi',
            'pinnacle' => 'Pinnacle',
        ];

        return $canonical[$slug] ?? $brand;
    }

    /**
     * The product_images table has both `path` (per schema.sql) and
     * `image_url` (used by existing controllers) referenced in different
     * places. Production may have either column. This figures out which
     * one exists once per request and caches the answer.
     */
    private static ?string $productImagesColumn = null;
    private function productImagesUrlColumn(\PDO $db): string
    {
        if (self::$productImagesColumn !== null) {
            return self::$productImagesColumn;
        }
        try {
            $cols = $db->query("SHOW COLUMNS FROM product_images")->fetchAll();
            $names = array_column($cols, 'Field');
            self::$productImagesColumn = in_array('image_url', $names, true) ? 'image_url' : 'path';
        } catch (\Throwable $e) {
            self::$productImagesColumn = 'image_url';
        }
        return self::$productImagesColumn;
    }

    private function importProductImage(\PDO $db, int $productId, string $imageUrl): void
    {
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return;
        }

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 15,
                    'user_agent' => 'Mozilla/5.0 (compatible; PricetagBot/1.0)',
                    'follow_location' => 1,
                    'max_redirects' => 3,
                ]
            ]);

            $imageData = @file_get_contents($imageUrl, false, $context);
            if (!$imageData || strlen($imageData) < 200) {
                // Less than 200 bytes is almost certainly an error page, not a real image
                return;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);

            $extMap = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];
            $ext = $extMap[$mimeType] ?? null;
            if (!$ext) {
                return;
            }

            // Validate dimensions - reject icons/thumbnails/error placeholders.
            // We use getimagesizefromstring to peek without writing to disk first.
            $dimensions = @getimagesizefromstring($imageData);
            if ($dimensions === false) {
                return;
            }
            [$width, $height] = $dimensions;
            if ($width < 200 || $height < 200) {
                error_log("Product {$productId}: skipping tiny image {$width}x{$height} from {$imageUrl}");
                return;
            }

            // Resize if larger than 1500px on the long edge. Storefront product cards
            // never render larger than ~1200px, so anything bigger is wasted bandwidth.
            $maxEdge = 1500;
            $longEdge = max($width, $height);
            if ($longEdge > $maxEdge && function_exists('imagecreatefromstring')) {
                $imageData = $this->resizeImage($imageData, $width, $height, $maxEdge, $ext) ?? $imageData;
            }

            $uploadDir = PUBLIC_PATH . '/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = $productId . '_' . time() . '_import.' . $ext;
            $filepath = $uploadDir . $filename;
            file_put_contents($filepath, $imageData);

            $urlColumn = $this->productImagesUrlColumn($db);
            $hasImage = (int) $db->query(
                "SELECT COUNT(*) FROM product_images WHERE product_id = ?",
                [$productId]
            )->fetchColumn();

            $db->query(
                "INSERT INTO product_images (product_id, `{$urlColumn}`, is_primary, sort_order, created_at)
                 VALUES (?, ?, ?, 0, NOW())",
                [
                    $productId,
                    '/uploads/products/' . $filename,
                    $hasImage ? 0 : 1,
                ]
            );
        } catch (\Exception $e) {
            // Silently fail for image imports
        }
    }

    /**
     * Resize an image to fit within $maxEdge px on the long side, preserving
     * aspect ratio. Returns the encoded bytes (jpeg/png/webp/gif) or null on
     * failure - caller should fall back to the original.
     */
    private function resizeImage(string $data, int $width, int $height, int $maxEdge, string $ext): ?string
    {
        $src = @imagecreatefromstring($data);
        if (!$src) {
            return null;
        }
        $ratio = $maxEdge / max($width, $height);
        $newW = (int) round($width * $ratio);
        $newH = (int) round($height * $ratio);
        $dst = imagecreatetruecolor($newW, $newH);

        // Preserve transparency for png/webp/gif
        if (in_array($ext, ['png', 'webp', 'gif'], true)) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($src);

        ob_start();
        switch ($ext) {
            case 'jpg':
                imagejpeg($dst, null, 85);
                break;
            case 'png':
                imagepng($dst, null, 7);
                break;
            case 'webp':
                if (function_exists('imagewebp')) {
                    imagewebp($dst, null, 85);
                } else {
                    imagedestroy($dst);
                    ob_end_clean();
                    return null;
                }
                break;
            case 'gif':
                imagegif($dst);
                break;
            default:
                imagedestroy($dst);
                ob_end_clean();
                return null;
        }
        $out = ob_get_clean();
        imagedestroy($dst);
        return $out !== false ? $out : null;
    }

    /**
     * Download import template
     */
    public function importTemplate(): void
    {
        $format = $_GET['format'] ?? 'csv';

        if ($format === 'json') {
            $this->downloadJsonTemplate();
        } else {
            $this->downloadCsvTemplate();
        }
    }

    /**
     * Download CSV template
     */
    private function downloadCsvTemplate(): void
    {
        $filename = 'product_import_template.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header row - minimal AI-friendly columns first
        fputcsv($output, [
            'sku', 'cost_price', 'vendor', 'category',
            'name', 'price', 'compare_price', 'stock',
            'description', 'short_description', 'weight', 'status', 'featured', 'image_url'
        ]);

        // AI-mode example: only sku, cost (excl VAT), vendor, category needed
        fputcsv($output, [
            'BX8071514600K', '4200.00', 'Pinnacle', 'CPUs',
            '', '', '', '20',
            '', '', '', '', '', ''
        ]);
        // Manual-mode example: all fields filled
        fputcsv($output, [
            'SKU002', '150.00', 'Acme Supplies', 'Clothing',
            'Cotton T-Shirt', '299.99', '349.99', '25',
            'Premium cotton t-shirt.', 'Soft, breathable cotton.',
            '0.3', 'active', '1', ''
        ]);
        // AI fallback example: unknown SKU - will be created as draft
        fputcsv($output, [
            'UNKNOWN-123', '500.00', 'Pinnacle', 'Accessories',
            '', '', '', '10', '', '', '', '', '', ''
        ]);

        fclose($output);
        exit;
    }

    /**
     * Download JSON template
     */
    private function downloadJsonTemplate(): void
    {
        $filename = 'product_import_template.json';

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $template = [
            '_notes' => [
                'ai_mode' => 'In AI mode only sku, cost_price (excl VAT), vendor and category are required - the rest is generated.',
                'price_calculation' => 'AI mode: sell price = cost_price * (1 + margin/100) * (1 + vat/100). Margin and VAT are set on the import screen.',
                'unknown_sku' => 'If AI cannot identify a SKU, the product is still created as status=draft so you can review it.',
            ],
            'products' => [
                [
                    'sku' => 'BX8071514600K',
                    'cost_price' => 4200.00,
                    'vendor' => 'Pinnacle',
                    'category' => 'CPUs',
                    'stock' => 20,
                ],
                [
                    'sku' => 'SKU002',
                    'name' => 'Cotton T-Shirt',
                    'price' => 299.99,
                    'compare_price' => 349.99,
                    'cost_price' => 150.00,
                    'vendor' => 'Acme Supplies',
                    'stock' => 25,
                    'category' => 'Clothing',
                    'description' => 'Premium cotton t-shirt.',
                    'short_description' => 'Soft, breathable cotton.',
                    'weight' => 0.3,
                    'status' => 'active',
                    'featured' => 1,
                    'image_url' => null
                ]
            ]
        ];

        echo json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Process import via AJAX (with column mapping). Used for non-AI imports
     * and small AI imports; large AI imports go through importEnqueue() and
     * the background queue. Thin wrapper around runImportLoop().
     */
    public function importProcess(): void
    {
        set_time_limit(600);
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', '0');

        header('Content-Type: application/json');

        if (!$this->validateCsrf()) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        $data = json_decode($_POST['data'] ?? '[]', true);
        if (empty($data)) {
            echo json_encode(['success' => false, 'error' => 'No data provided']);
            exit;
        }

        $options = $this->extractImportOptions($_POST);
        $db = db();

        try {
            $result = $this->runImportLoop($db, $data, $options);
            $this->writeImportLog($db, $result, $options);
            echo json_encode([
                'success' => true,
                'created' => $result['created'],
                'updated' => $result['updated'],
                'failed' => $result['failed'],
                'ai_service' => $result['ai_service'],
                'errors' => $result['errors'],
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Pull the option flags out of $_POST (or any other source array) so the
     * same defaults apply whether the import comes from synchronous AJAX or
     * a queued background job.
     */
    private function extractImportOptions(array $source): array
    {
        return [
            'update_existing' => (($source['update_existing'] ?? '1') === '1' || ($source['update_existing'] ?? null) === true),
            'create_new' => (($source['create_new'] ?? '1') === '1' || ($source['create_new'] ?? null) === true),
            'skip_errors' => (($source['skip_errors'] ?? '0') === '1' || ($source['skip_errors'] ?? null) === true),
            'ai_generate' => (($source['ai_generate'] ?? '0') === '1' || ($source['ai_generate'] ?? null) === true),
            'margin_percent' => max(0.0, (float) ($source['margin_percent'] ?? 0)),
            'vat_rate' => max(0.0, (float) ($source['vat_rate'] ?? 0)),
            'default_vendor_id' => !empty($source['default_vendor_id']) ? (int) $source['default_vendor_id'] : null,
            'default_category_id' => !empty($source['default_category_id']) ? (int) $source['default_category_id'] : null,
        ];
    }

    /**
     * The core per-row loop used by both the synchronous import endpoint and
     * the background queue worker. $heartbeat is invoked every 5 rows with
     * the current progress so the queued path can publish status updates.
     *
     * Returns ['created','updated','failed','errors','ai_service'].
     */
    private function runImportLoop(\PDO $db, array $data, array $options, ?callable $heartbeat = null): array
    {
        $updateExisting = (bool) $options['update_existing'];
        $createNew = (bool) $options['create_new'];
        $skipErrors = (bool) $options['skip_errors'];
        $aiGenerate = (bool) $options['ai_generate'];
        $marginPercent = (float) $options['margin_percent'];
        $vatRate = (float) $options['vat_rate'];
        $defaultVendorId = $options['default_vendor_id'];
        $defaultCategoryId = $options['default_category_id'];

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];
        $categoryMap = $this->getCategoryMap($db);
        $aiService = null;
        $aiServiceName = 'none';

        foreach ($data as $index => $row) {
                $rowNum = $index + 1;

                try {
                    $sku = trim($row['sku'] ?? '');
                    if (empty($sku)) {
                        if ($skipErrors) {
                            $errors[] = "Row {$rowNum}: SKU is required";
                            $failed++;
                            continue;
                        }
                        throw new \Exception("Row {$rowNum}: SKU is required");
                    }

                    // Check if product exists
                    $existing = $db->query("SELECT id FROM products WHERE sku = ?", [$sku])->fetch();

                    // Store AI data for post-insert operations (specs, attributes)
                    $aiData = null;

                    if ($existing) {
                        if (!$updateExisting) {
                            continue;
                        }

                        // AI Generate for existing products too - enrich missing data
                        if ($aiGenerate) {
                            if ($aiService === null) {
                                [$aiService, $aiServiceName] = $this->resolveAiService();
                            }
                            $existingProduct = $db->query("SELECT * FROM products WHERE id = ?", [$existing['id']])->fetch();
                            $aiResult = $aiService->generateCompleteProduct($sku, trim($row['short_description'] ?? $existingProduct['short_description'] ?? ''), [
                                'brand' => $row['brand'] ?? '',
                                'category' => $row['category'] ?? '',
                                'price' => $row['price'] ?? $existingProduct['price'] ?? 0,
                                'existingName' => trim($row['name'] ?? $existingProduct['name'] ?? ''),
                                'existingDescription' => $row['description'] ?? $existingProduct['description'] ?? '',
                                'bulk_import' => true,
                            ]);

                            if (!empty($aiResult['success']) && !empty($aiResult['data'])) {
                                $aiData = $aiResult['data'];
                                // Fill in missing fields from AI for the update
                                if (!empty($aiData['name']) && $aiData['name'] !== $sku && empty($row['name'])) {
                                    $row['name'] = $aiData['name'];
                                }
                                if (empty($existingProduct['description']) && !empty($aiData['description'])) {
                                    $row['description'] = $aiData['description'];
                                }
                                if (empty($existingProduct['short_description']) && !empty($aiData['short_description'])) {
                                    $row['short_description'] = $aiData['short_description'];
                                }
                                if (empty($existingProduct['meta_title']) && !empty($aiData['meta_title'])) {
                                    $row['meta_title'] = substr($aiData['meta_title'], 0, 255);
                                }
                                if (empty($existingProduct['meta_description']) && !empty($aiData['meta_description'])) {
                                    $row['meta_description'] = $aiData['meta_description'];
                                }
                                if (empty($existingProduct['meta_keywords']) && !empty($aiData['meta_keywords'])) {
                                    $row['meta_keywords'] = substr($aiData['meta_keywords'], 0, 255);
                                }
                                if (!empty($aiData['weight']) && empty($existingProduct['weight'])) {
                                    $row['weight'] = (float) $aiData['weight'];
                                }
                                if (!empty($aiData['length']) && empty($existingProduct['length'])) {
                                    $row['length'] = (float) $aiData['length'];
                                }
                                if (!empty($aiData['width']) && empty($existingProduct['width'])) {
                                    $row['width'] = (float) $aiData['width'];
                                }
                                if (!empty($aiData['height']) && empty($existingProduct['height'])) {
                                    $row['height'] = (float) $aiData['height'];
                                }
                                // Use AI category suggestion if product has no category
                                if (empty($existingProduct['category_id']) && !empty($aiData['suggested_category'])) {
                                    $row['category'] = $aiData['suggested_category'];
                                }
                            }
                        }

                        $this->updateProductFromImport($db, $existing['id'], $row, $categoryMap);

                        // Save AI-generated specifications and attributes for existing products
                        if ($aiData) {
                            $this->saveProductSpecifications($db, $existing['id'], $aiData['specifications'] ?? []);
                            $this->saveProductAttributes($db, $existing['id'], $aiData['attributes'] ?? [], $aiData['suggested_category'] ?? '');
                        }

                        // Download AI image only if product has no existing image
                        if ($aiData && !empty($aiData['image_url'])) {
                            $hasImage = (int) $db->query(
                                "SELECT COUNT(*) FROM product_images WHERE product_id = ?",
                                [$existing['id']]
                            )->fetchColumn();
                            if ($hasImage === 0) {
                                try {
                                    $this->importProductImage($db, $existing['id'], $aiData['image_url']);
                                } catch (\Throwable $imgErr) {
                                    $errors[] = "Row {$rowNum}: AI image download failed for {$sku}: " . $imgErr->getMessage();
                                }
                            }
                        }

                        // Audit trail
                        if ($aiGenerate && isset($aiResult)) {
                            $this->logAiImport($db, (int) $existing['id'], $sku, $aiServiceName, $aiResult);
                        }

                        $updated++;
                    } else {
                        if (!$createNew) {
                            continue;
                        }

                        $name = trim($row['name'] ?? '');
                        $shortDesc = trim($row['short_description'] ?? '');

                        // Apply AI-mode defaults for vendor/category before AI call
                        if ($aiGenerate) {
                            if (empty($row['vendor']) && $defaultVendorId) {
                                $row['vendor_id'] = $defaultVendorId;
                            }
                            if (empty($row['category']) && $defaultCategoryId) {
                                $row['category'] = (string) $defaultCategoryId;
                            }
                        }

                        // Calculate sell price from cost + margin + VAT (AI mode only, if no explicit price)
                        if ($aiGenerate && empty($row['price']) && !empty($row['cost_price'])) {
                            $cost = (float) $row['cost_price'];
                            $row['price'] = round(
                                $cost * (1 + $marginPercent / 100) * (1 + $vatRate / 100),
                                2
                            );
                        }

                        // AI Generate: use real AI service for complete product generation
                        $aiIdentified = false;
                        if ($aiGenerate) {
                            if ($aiService === null) {
                                [$aiService, $aiServiceName] = $this->resolveAiService();
                            }
                            $aiResult = $aiService->generateCompleteProduct($sku, $shortDesc, [
                                'brand' => $row['brand'] ?? '',
                                'category' => $row['category'] ?? '',
                                'price' => $row['price'] ?? 0,
                                'existingName' => $name,
                                'existingDescription' => $row['description'] ?? '',
                                'bulk_import' => true,
                            ]);

                            if (!empty($aiResult['success']) && !empty($aiResult['data'])) {
                                $aiData = $aiResult['data'];

                                // AI-generated name: always prefer it when available and not raw SKU
                                if (!empty($aiData['name']) && $aiData['name'] !== $sku) {
                                    $name = $aiData['name'];
                                    $aiIdentified = true;
                                }

                                // AI-generated descriptions
                                if (!empty($aiData['description'])) {
                                    $row['description'] = $aiData['description'];
                                }
                                if (!empty($aiData['short_description'])) {
                                    $row['short_description'] = $aiData['short_description'];
                                }

                                // AI-generated SEO
                                if (!empty($aiData['meta_title'])) {
                                    $row['meta_title'] = substr($aiData['meta_title'], 0, 255);
                                }
                                if (!empty($aiData['meta_description'])) {
                                    $row['meta_description'] = $aiData['meta_description'];
                                }
                                if (!empty($aiData['meta_keywords'])) {
                                    $row['meta_keywords'] = substr($aiData['meta_keywords'], 0, 255);
                                }

                                // AI-generated physical dimensions
                                if (!empty($aiData['weight']) && empty($row['weight'])) {
                                    $row['weight'] = (float) $aiData['weight'];
                                }
                                if (!empty($aiData['length']) && empty($row['length'])) {
                                    $row['length'] = (float) $aiData['length'];
                                }
                                if (!empty($aiData['width']) && empty($row['width'])) {
                                    $row['width'] = (float) $aiData['width'];
                                }
                                if (!empty($aiData['height']) && empty($row['height'])) {
                                    $row['height'] = (float) $aiData['height'];
                                }

                                // AI-generated category: use when no category in CSV
                                if (empty($row['category']) && !empty($aiData['suggested_category'])) {
                                    $row['category'] = $aiData['suggested_category'];
                                    // Ensure category exists in map, create if needed
                                    $catKey = strtolower(trim($aiData['suggested_category']));
                                    if (!isset($categoryMap[$catKey])) {
                                        $this->createCategoryIfNotExists($db, $aiData['suggested_category'], $categoryMap);
                                    }
                                }

                                // AI-generated is_new flag
                                if (!empty($aiData['is_new'])) {
                                    $row['is_new'] = 1;
                                }
                            }
                        }

                        if (empty($name)) {
                            if ($aiGenerate) {
                                // Unknown SKU fallback: create a draft stub so the user can review later.
                                // Only SKU, cost, vendor, category are populated. status='draft' keeps it off the storefront.
                                $name = $sku;
                                $row['name'] = $sku;
                                $row['status'] = 'draft';
                                $row['_unknown_sku'] = true;
                                $errors[] = "Row {$rowNum}: AI could not identify SKU {$sku} - created as draft for manual review";
                            } else {
                                if ($skipErrors) {
                                    $errors[] = "Row {$rowNum}: Name is required for new products";
                                    $failed++;
                                    continue;
                                }
                                throw new \Exception("Row {$rowNum}: Name is required for new products");
                            }
                        } else {
                            $row['name'] = $name;
                        }

                        $productId = $this->createProductFromImport($db, $row, $categoryMap);

                        // Save AI-generated specifications and attributes (only when AI actually identified the product)
                        if ($aiData && $aiIdentified && $productId) {
                            $this->saveProductSpecifications($db, $productId, $aiData['specifications'] ?? []);
                            $this->saveProductAttributes($db, $productId, $aiData['attributes'] ?? [], $aiData['suggested_category'] ?? '');
                        }

                        // Download product image from AI (Claude's web_search returns image URLs)
                        if ($aiData && $aiIdentified && $productId && !empty($aiData['image_url'])) {
                            try {
                                $this->importProductImage($db, $productId, $aiData['image_url']);
                            } catch (\Throwable $imgErr) {
                                // Image failures should not break the import - they go in the error list
                                $errors[] = "Row {$rowNum}: AI image download failed for {$sku}: " . $imgErr->getMessage();
                            }
                        }

                        // Audit trail - always logged, even for unknown-SKU draft stubs
                        if ($aiGenerate && isset($aiResult)) {
                            $this->logAiImport($db, $productId ?: null, $sku, $aiServiceName, $aiResult);
                        }

                        $created++;
                    }

                } catch (\Exception $e) {
                    if ($skipErrors) {
                        $errors[] = $e->getMessage();
                        $failed++;
                        continue;
                    }
                    throw $e;
                }

                // Heartbeat every 5 rows so the queue UI can show progress
                if ($heartbeat !== null && (($index + 1) % 5 === 0)) {
                    $heartbeat([
                        'processed' => $created + $updated + $failed,
                        'created' => $created,
                        'updated' => $updated,
                        'failed' => $failed,
                        'errors' => $errors,
                    ]);
                }
            }

            // Final heartbeat
            if ($heartbeat !== null) {
                $heartbeat([
                    'processed' => $created + $updated + $failed,
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ]);
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed,
                'errors' => $errors,
                'ai_service' => $aiServiceName,
            ];
    }

    /**
     * Write the final summary row to product_import_logs. Separated so the
     * sync and async import paths both produce identical history entries.
     * Returns the new log id, or null if the table doesn't exist yet.
     */
    private function writeImportLog(\PDO $db, array $result, array $options): ?int
    {
        try {
            $type = $options['ai_generate'] ? 'ai_import' : 'import';
            $filename = $options['ai_generate']
                ? "AI Import ({$result['ai_service']})"
                : 'CSV Import';
            $db->query(
                "INSERT INTO product_import_logs (type, filename, status, total_products, created_products, updated_products, failed_products, errors, created_at, completed_at)
                 VALUES (?, ?, 'completed', ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $type,
                    $filename,
                    $result['created'] + $result['updated'] + $result['failed'],
                    $result['created'],
                    $result['updated'],
                    $result['failed'],
                    !empty($result['errors']) ? json_encode(array_slice($result['errors'], 0, 100)) : null,
                ]
            );
            return (int) $db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Background-queue entry point. Called by the Scheduler when a queued
     * import is picked up. Hands off to runImportLoop() with a heartbeat
     * callback that publishes progress to product_import_jobs, then writes
     * the final import log and marks the job completed.
     */
    public function processImportJob(array $job, ImportJobService $jobService): array
    {
        @set_time_limit(0);
        $jobId = (int) $job['id'];
        $payload = $job['payload'] ?? [];
        $data = $payload['data'] ?? [];
        $options = $this->extractImportOptions($payload);

        $db = db();
        try {
            $result = $this->runImportLoop($db, $data, $options, function ($progress) use ($jobService, $jobId) {
                $jobService->heartbeat($jobId, $progress);
            });
            $logId = $this->writeImportLog($db, $result, $options);
            $jobService->complete(
                $jobId,
                [
                    'processed' => $result['created'] + $result['updated'] + $result['failed'],
                    'created' => $result['created'],
                    'updated' => $result['updated'],
                    'failed' => $result['failed'],
                    'errors' => $result['errors'],
                ],
                $logId
            );
            return $result;
        } catch (\Throwable $e) {
            $jobService->fail($jobId, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save AI-generated product specifications.
     * Replaces existing specs for the product with the new AI-generated ones.
     */
    private function saveProductSpecifications(\PDO $db, int $productId, array $specifications): void
    {
        if (empty($specifications)) {
            return;
        }

        try {
            // Check if product already has specs - only add if none exist
            $existingCount = (int) $db->query(
                "SELECT COUNT(*) FROM product_specifications WHERE product_id = ?",
                [$productId]
            )->fetchColumn();

            if ($existingCount > 0) {
                return; // Don't overwrite existing specs
            }

            $sortOrder = 0;
            foreach ($specifications as $spec) {
                $specName = trim($spec['name'] ?? '');
                $specValue = trim($spec['value'] ?? '');

                if (empty($specName) || empty($specValue)) {
                    continue;
                }

                $db->query("
                    INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ", [$productId, substr($specName, 0, 100), substr($specValue, 0, 500), $sortOrder]);

                $sortOrder++;
            }
        } catch (\Exception $e) {
            error_log("Failed to save specifications for product {$productId}: " . $e->getMessage());
        }
    }

    /**
     * Save AI-generated product attributes (filterable attributes like Series, Memory Size, etc.)
     * Creates attribute and attribute_value records if they don't exist.
     */
    private function saveProductAttributes(\PDO $db, int $productId, array $attributes, string $category): void
    {
        if (empty($attributes)) {
            return;
        }

        try {
            // Check if product already has attributes - only add if none exist
            $existingCount = (int) $db->query(
                "SELECT COUNT(*) FROM product_attributes WHERE product_id = ?",
                [$productId]
            )->fetchColumn();

            if ($existingCount > 0) {
                return; // Don't overwrite existing attributes
            }

            foreach ($attributes as $attrName => $attrValue) {
                $attrName = trim($attrName);
                $attrValue = trim($attrValue);

                if (empty($attrName) || empty($attrValue)) {
                    continue;
                }

                // Normalize brand variations (HP / HP Inc. / Hewlett-Packard -> HP)
                if (strcasecmp($attrName, 'brand') === 0 || strcasecmp($attrName, 'manufacturer') === 0) {
                    $attrValue = $this->normalizeBrand($attrValue);
                }

                // Find or create the attribute
                $attrSlug = $this->generateAttributeSlug($attrName);
                $attribute = $db->query(
                    "SELECT id FROM attributes WHERE slug = ?",
                    [$attrSlug]
                )->fetch();

                if (!$attribute) {
                    $db->query("
                        INSERT INTO attributes (name, slug, type, is_filterable, is_visible, created_at)
                        VALUES (?, ?, 'select', 1, 1, NOW())
                    ", [$attrName, $attrSlug]);
                    $attributeId = (int) $db->lastInsertId();
                } else {
                    $attributeId = (int) $attribute['id'];
                }

                // Find or create the attribute value
                $valueSlug = $this->generateAttributeSlug($attrValue);
                $value = $db->query(
                    "SELECT id FROM attribute_values WHERE attribute_id = ? AND slug = ?",
                    [$attributeId, $valueSlug]
                )->fetch();

                if (!$value) {
                    $db->query("
                        INSERT INTO attribute_values (attribute_id, value, slug, created_at)
                        VALUES (?, ?, ?, NOW())
                    ", [$attributeId, substr($attrValue, 0, 255), $valueSlug]);
                    $valueId = (int) $db->lastInsertId();
                } else {
                    $valueId = (int) $value['id'];
                }

                // Link attribute value to product
                $db->query("
                    INSERT INTO product_attributes (product_id, attribute_id, attribute_value_id)
                    VALUES (?, ?, ?)
                ", [$productId, $attributeId, $valueId]);
            }
        } catch (\Exception $e) {
            error_log("Failed to save attributes for product {$productId}: " . $e->getMessage());
        }
    }

    /**
     * Generate a URL-safe slug for attributes/values
     */
    private function generateAttributeSlug(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return substr($slug, 0, 255) ?: 'unknown';
    }

    /**
     * Create a category if it doesn't exist in the category map.
     * Used when AI suggests a category that doesn't exist yet.
     */
    private function createCategoryIfNotExists(\PDO $db, string $categoryName, array &$categoryMap): void
    {
        $catKey = strtolower(trim($categoryName));
        if (isset($categoryMap[$catKey])) {
            return;
        }

        try {
            $slug = $this->generateSlug($categoryName);
            $db->query("
                INSERT INTO categories (name, slug, parent_id, created_at, updated_at)
                VALUES (?, ?, NULL, NOW(), NOW())
            ", [$categoryName, $slug]);
            $categoryMap[$catKey] = (int) $db->lastInsertId();
        } catch (\Exception $e) {
            // Category might already exist (race condition) - try to fetch it
            $existing = $db->query("SELECT id FROM categories WHERE name = ?", [$categoryName])->fetch();
            if ($existing) {
                $categoryMap[$catKey] = (int) $existing['id'];
            }
        }
    }
}
