<?php
/**
 * Admin Product Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles product management with vendors and attributes.
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $vendor = $_GET['vendor'] ?? '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status) {
            $where[] = "p.status = ?";
            $params[] = $status;
        }

        if ($vendor) {
            $where[] = "p.vendor_id = ?";
            $params[] = $vendor;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $stmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereClause");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        // Get products
        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("
            SELECT p.*, pi.path as image, v.name as vendor_name
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            LEFT JOIN vendors v ON v.id = p.vendor_id
            WHERE $whereClause
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Get vendors for filter
        $vendors = $this->getVendors();

        $this->layout('admin');
        $this->view('pages/products/index', [
            'page_title' => 'Products',
            'active_page' => 'products',
            'products' => $products,
            'vendors' => $vendors,
            'search' => $search,
            'status' => $status,
            'vendor_filter' => $vendor,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function create(): void
    {
        $categories = Category::getTree();
        $vendors = $this->getVendors();
        $attributes = $this->getAttributesWithValues();

        $this->layout('admin');
        $this->view('pages/products/form', [
            'page_title' => 'Add Product',
            'active_page' => 'products',
            'product' => null,
            'categories' => $categories,
            'vendors' => $vendors,
            'attributes' => $attributes,
            'productAttributes' => [],
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/products/create');
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:255',
            'sku' => 'required|unique:products,sku',
            'price' => 'required|numeric',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please check your input');
            $this->redirect('/admin/products/create');
            return;
        }

        $slug = slugify($_POST['name']);

        // Ensure unique slug
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE slug LIKE ?");
        $stmt->execute([$slug . '%']);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $product = Product::create([
            'sku' => $_POST['sku'],
            'name' => $_POST['name'],
            'slug' => $slug,
            'description' => $_POST['description'] ?? null,
            'short_description' => $_POST['short_description'] ?? null,
            'type' => $_POST['type'] ?? 'simple',
            'status' => $_POST['status'] ?? 'draft',
            'price' => (float) $_POST['price'],
            'compare_price' => !empty($_POST['compare_price']) ? (float) $_POST['compare_price'] : null,
            'cost_price' => !empty($_POST['cost_price']) ? (float) $_POST['cost_price'] : null,
            'manage_stock' => !empty($_POST['manage_stock']),
            'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
            'low_stock_threshold' => (int) ($_POST['low_stock_threshold'] ?? 5),
            'vendor_id' => !empty($_POST['vendor_id']) ? (int) $_POST['vendor_id'] : null,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'is_featured' => !empty($_POST['is_featured']),
            'is_new' => !empty($_POST['is_new']),
            'is_on_sale' => !empty($_POST['is_on_sale']),
        ]);

        // Handle categories
        if (!empty($_POST['categories'])) {
            $isPrimary = true;
            foreach ($_POST['categories'] as $catId) {
                $stmt = $db->prepare("INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)");
                $stmt->execute([$product->id, $catId, $isPrimary ? 1 : 0]);
                $isPrimary = false;
            }
        }

        // Handle attributes
        $this->saveProductAttributes($db, $product->id);

        // Handle image upload
        if (!empty($_FILES['image']['tmp_name'])) {
            $this->handleImageUpload($product->id, $_FILES['image']);
        }

        flash('success', 'Product created successfully');
        $this->redirect('/admin/products/' . $product->id . '/edit');
    }

    public function edit(string $id): void
    {
        $product = Product::find((int) $id);

        if (!$product) {
            flash('error', 'Product not found');
            $this->redirect('/admin/products');
            return;
        }

        $categories = Category::getTree();
        $productCategories = array_column($product->getCategories(), 'id');
        $images = $product->getImages();
        $vendors = $this->getVendors();
        $attributes = $this->getAttributesWithValues();
        $productAttributes = $this->getProductAttributes($product->id);

        $this->layout('admin');
        $this->view('pages/products/form', [
            'page_title' => 'Edit Product',
            'active_page' => 'products',
            'product' => $product,
            'categories' => $categories,
            'productCategories' => $productCategories,
            'images' => $images,
            'vendors' => $vendors,
            'attributes' => $attributes,
            'productAttributes' => $productAttributes,
        ]);
    }

    public function update(string $id): void
    {
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/products/' . $id . '/edit');
            return;
        }

        $product = Product::find((int) $id);

        if (!$product) {
            flash('error', 'Product not found');
            $this->redirect('/admin/products');
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:255',
            'price' => 'required|numeric',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please check your input');
            $this->redirect('/admin/products/' . $id . '/edit');
            return;
        }

        // Update product
        $product->name = $_POST['name'];
        $product->description = $_POST['description'] ?? null;
        $product->short_description = $_POST['short_description'] ?? null;
        $product->type = $_POST['type'] ?? 'simple';
        $product->status = $_POST['status'] ?? 'draft';
        $product->price = (float) $_POST['price'];
        $product->compare_price = !empty($_POST['compare_price']) ? (float) $_POST['compare_price'] : null;
        $product->cost_price = !empty($_POST['cost_price']) ? (float) $_POST['cost_price'] : null;
        $product->manage_stock = !empty($_POST['manage_stock']);
        $product->stock_quantity = (int) ($_POST['stock_quantity'] ?? 0);
        $product->low_stock_threshold = (int) ($_POST['low_stock_threshold'] ?? 5);
        $product->vendor_id = !empty($_POST['vendor_id']) ? (int) $_POST['vendor_id'] : null;
        $product->meta_title = $_POST['meta_title'] ?? null;
        $product->meta_description = $_POST['meta_description'] ?? null;
        $product->is_featured = !empty($_POST['is_featured']);
        $product->is_new = !empty($_POST['is_new']);
        $product->is_on_sale = !empty($_POST['is_on_sale']);
        $product->save();

        // Update categories
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->execute([$product->id]);

        if (!empty($_POST['categories'])) {
            $isPrimary = true;
            foreach ($_POST['categories'] as $catId) {
                $stmt = $db->prepare("INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)");
                $stmt->execute([$product->id, $catId, $isPrimary ? 1 : 0]);
                $isPrimary = false;
            }
        }

        // Update attributes
        $this->saveProductAttributes($db, $product->id);

        // Handle new image upload
        if (!empty($_FILES['image']['tmp_name'])) {
            $this->handleImageUpload($product->id, $_FILES['image']);
        }

        flash('success', 'Product updated successfully');
        $this->redirect('/admin/products/' . $id . '/edit');
    }

    public function destroy(string $id): void
    {
        if (!$this->validateCsrf()) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid security token']);
            }
            return;
        }

        $product = Product::find((int) $id);

        if ($product) {
            $product->delete();
            flash('success', 'Product deleted');
        }

        if (isAjax()) {
            $this->json(['success' => true]);
            return;
        }

        $this->redirect('/admin/products');
    }

    /**
     * Delete product image (AJAX)
     */
    public function deleteImage(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $db = Database::getInstance();

        // Get image info
        $stmt = $db->prepare("SELECT * FROM product_images WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetch();

        if (!$image) {
            $this->json(['success' => false, 'message' => 'Image not found']);
            return;
        }

        // Delete file
        $fullPath = STORAGE_PATH . '/uploads/' . $image['path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Delete from database
        $stmt = $db->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt->execute([$id]);

        // If this was primary, set another image as primary
        if ($image['is_primary']) {
            $stmt = $db->prepare("UPDATE product_images SET is_primary = 1 WHERE product_id = ? LIMIT 1");
            $stmt->execute([$image['product_id']]);
        }

        $this->json(['success' => true]);
    }

    /**
     * Get all vendors
     */
    private function getVendors(): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT id, name, status FROM vendors WHERE status = 'active' ORDER BY name ASC");
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get all attributes with their values
     */
    private function getAttributesWithValues(): array
    {
        try {
            $db = Database::getInstance();

            // Get all attributes
            $stmt = $db->query("SELECT * FROM attributes ORDER BY sort_order, name ASC");
            $attributes = $stmt->fetchAll() ?: [];

            // Get all attribute values
            $stmt = $db->query("SELECT * FROM attribute_values ORDER BY attribute_id, sort_order, value ASC");
            $allValues = $stmt->fetchAll() ?: [];

            // Group values by attribute_id
            $valuesByAttribute = [];
            foreach ($allValues as $value) {
                $valuesByAttribute[$value['attribute_id']][] = $value;
            }

            // Add values to each attribute
            foreach ($attributes as &$attr) {
                $attr['values'] = $valuesByAttribute[$attr['id']] ?? [];
            }

            return $attributes;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get attributes for a specific product
     */
    private function getProductAttributes(int $productId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT attribute_id, attribute_value_id, custom_value
                FROM product_attributes
                WHERE product_id = ?
            ");
            $stmt->execute([$productId]);
            $rows = $stmt->fetchAll() ?: [];

            // Build associative array keyed by attribute_id
            $result = [];
            foreach ($rows as $row) {
                $result[$row['attribute_id']] = [
                    'value_id' => $row['attribute_value_id'],
                    'custom_value' => $row['custom_value'],
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Save product attributes from POST data
     */
    private function saveProductAttributes(\PDO $db, int $productId): void
    {
        // Delete existing attributes
        $stmt = $db->prepare("DELETE FROM product_attributes WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Get attribute values from POST
        $attributeValues = $_POST['attribute_values'] ?? [];
        $customValues = $_POST['attribute_custom'] ?? [];

        foreach ($attributeValues as $attributeId => $valueId) {
            if (empty($valueId) && empty($customValues[$attributeId])) {
                continue; // Skip empty attributes
            }

            $stmt = $db->prepare("
                INSERT INTO product_attributes (product_id, attribute_id, attribute_value_id, custom_value)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $productId,
                $attributeId,
                !empty($valueId) ? $valueId : null,
                !empty($customValues[$attributeId]) ? $customValues[$attributeId] : null,
            ]);
        }
    }

    private function handleImageUpload(int $productId, array $file): void
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            flash('error', 'Invalid image type');
            return;
        }

        if ($file['size'] > $maxSize) {
            flash('error', 'Image too large (max 5MB)');
            return;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product-' . $productId . '-' . time() . '.' . $ext;
        $path = 'products/' . $filename;
        $fullPath = STORAGE_PATH . '/uploads/' . $path;

        // Create directory if needed
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            $db = Database::getInstance();

            // Check if product has images
            $stmt = $db->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);
            $hasImages = $stmt->fetchColumn() > 0;

            $stmt = $db->prepare("INSERT INTO product_images (product_id, path, is_primary) VALUES (?, ?, ?)");
            $stmt->execute([$productId, $path, $hasImages ? 0 : 1]);
        }
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        $viewPath = ADMIN_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '$view' not found at '$viewPath'");
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if (isset($this->data['_layout'])) {
            $layoutPath = ADMIN_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
                return;
            }
        }

        echo $content;
    }

    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }
}
