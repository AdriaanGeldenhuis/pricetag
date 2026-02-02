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

class ProductController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get filter parameters
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
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
}
