<?php
/**
 * Admin Product Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
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

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(name LIKE ? OR sku LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE $whereClause");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        // Get products
        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("
            SELECT p.*, pi.path as image
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE $whereClause
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/products/index', [
            'page_title' => 'Products',
            'active_page' => 'products',
            'products' => $products,
            'search' => $search,
            'status' => $status,
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

        $this->layout('admin');
        $this->view('pages/products/form', [
            'page_title' => 'Add Product',
            'active_page' => 'products',
            'product' => null,
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
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

        $this->layout('admin');
        $this->view('pages/products/form', [
            'page_title' => 'Edit Product',
            'active_page' => 'products',
            'product' => $product,
            'categories' => $categories,
            'productCategories' => $productCategories,
            'images' => $images,
        ]);
    }

    public function update(string $id): void
    {
        if (!$this->validateCsrf()) {
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
