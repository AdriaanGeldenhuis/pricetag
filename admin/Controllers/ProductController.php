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
use App\Services\OpenAIService;
use App\Services\ClaudeService;
use App\Services\ImportJobService;
use App\Services\ProductImageService;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 100;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $vendor = $_GET['vendor'] ?? '';
        $category = $_GET['category'] ?? '';
        $brand = $_GET['brand'] ?? '';

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

        if ($category) {
            $where[] = "EXISTS (SELECT 1 FROM product_categories pc2 WHERE pc2.product_id = p.id AND pc2.category_id = ?)";
            $params[] = $category;
        }

        if ($brand) {
            $where[] = "EXISTS (SELECT 1 FROM product_attributes pa2 JOIN attribute_values av2 ON pa2.attribute_value_id = av2.id JOIN attributes a2 ON av2.attribute_id = a2.id WHERE pa2.product_id = p.id AND LOWER(a2.name) = 'brand' AND av2.id = ?)";
            $params[] = $brand;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $stmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereClause");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        // Get products
        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("
            SELECT p.*, pi.path as image, v.name as vendor_name, c.name as category_name
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            LEFT JOIN vendors v ON v.id = p.vendor_id
            LEFT JOIN product_categories pc ON pc.product_id = p.id AND pc.is_primary = 1
            LEFT JOIN categories c ON c.id = pc.category_id
            WHERE $whereClause
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Get vendors, categories and brands for filters
        $vendors = $this->getVendors();
        $categories = Category::getTree();
        $brands = $this->getBrands();

        $this->layout('admin');
        $this->view('pages/products/index', [
            'page_title' => 'Products',
            'active_page' => 'products',
            'products' => $products,
            'vendors' => $vendors,
            'categories' => $categories,
            'brands' => $brands,
            'search' => $search,
            'status' => $status,
            'vendor_filter' => $vendor,
            'category_filter' => $category,
            'brand_filter' => $brand,
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

        $categories = $_POST['categories'] ?? [];
        $data = $this->buildProductDataFromPost();
        if (!empty($categories)) {
            $data['categories'] = array_map('intval', $categories);
            $data['primary_category_id'] = (int) $categories[0];
        }

        try {
            $product = ProductService::getInstance()->createProduct($data);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/admin/products/create');
            return;
        }

        // Attributes, variants and image uploads still come straight from $_POST/$_FILES;
        // the admin form's shape doesn't match ProductService's existing helpers (those
        // were built for AI-generated content). Keep them here for now.
        $db = Database::getInstance();
        $this->saveProductAttributes($db, $product->id);

        if (!empty($_FILES['images']['tmp_name'][0])) {
            $this->handleImageUploads($product->id, $_FILES['images']);
        }

        if (($product->type ?? 'simple') === 'variable') {
            $this->saveProductVariants($db, $product->id);
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
        $specifications = $this->getProductSpecifications($product->id);
        $reviews = $this->getProductReviews($product->id);

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
            'specifications' => $specifications,
            'reviews' => $reviews,
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

        $categories = $_POST['categories'] ?? [];
        $data = $this->buildProductDataFromPost();
        // Don't change SKU on update unless explicitly posted; ProductService's
        // unique check otherwise complains about the existing value.
        unset($data['sku']);
        if (!empty($categories)) {
            $data['categories'] = array_map('intval', $categories);
            $data['primary_category_id'] = (int) $categories[0];
        }

        try {
            $product = ProductService::getInstance()->updateProduct((int) $id, $data);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/admin/products/' . $id . '/edit');
            return;
        }

        $db = Database::getInstance();

        // Update attributes
        $this->saveProductAttributes($db, $product->id);

        // Update specifications
        $this->saveProductSpecifications($db, $product->id);

        // Handle new image uploads (multiple)
        if (!empty($_FILES['images']['tmp_name'][0])) {
            $this->handleImageUploads($product->id, $_FILES['images']);
        }

        // Handle variants for variable products
        if ($product->type === 'variable') {
            $this->saveProductVariants($db, $product->id);
        }

        // Handle variant deletions
        if (!empty($_POST['delete_variants'])) {
            $deleteIds = array_map('intval', $_POST['delete_variants']);
            $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
            $stmt = $db->prepare("DELETE FROM product_variants WHERE id IN ($placeholders) AND product_id = ?");
            $stmt->execute([...$deleteIds, $product->id]);
        }

        flash('success', 'Product updated successfully');
        $this->redirect('/admin/products/' . $id . '/edit');
    }

    /**
     * Map the product form's $_POST shape into the array shape ProductService
     * expects. Single source of truth for store() and update().
     */
    private function buildProductDataFromPost(): array
    {
        return [
            'sku' => $_POST['sku'] ?? null,
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? null,
            'short_description' => $_POST['short_description'] ?? null,
            'type' => $_POST['type'] ?? 'simple',
            'status' => $_POST['status'] ?? 'draft',
            'price' => isset($_POST['price']) ? (float) $_POST['price'] : 0.0,
            'compare_price' => !empty($_POST['compare_price']) ? (float) $_POST['compare_price'] : null,
            'cost_price' => !empty($_POST['cost_price']) ? (float) $_POST['cost_price'] : null,
            'manage_stock' => !empty($_POST['manage_stock']) ? 1 : 0,
            'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
            'low_stock_threshold' => (int) ($_POST['low_stock_threshold'] ?? 5),
            'weight' => !empty($_POST['weight']) ? (float) $_POST['weight'] : null,
            'length' => !empty($_POST['length']) ? (float) $_POST['length'] : null,
            'width' => !empty($_POST['width']) ? (float) $_POST['width'] : null,
            'height' => !empty($_POST['height']) ? (float) $_POST['height'] : null,
            'vendor_id' => !empty($_POST['vendor_id']) ? (int) $_POST['vendor_id'] : null,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'meta_keywords' => $_POST['meta_keywords'] ?? null,
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_new' => !empty($_POST['is_new']) ? 1 : 0,
            'is_on_sale' => !empty($_POST['is_on_sale']) ? 1 : 0,
        ];
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
            // Delete all product images (files from disk + DB records) before deleting product
            $imageService = new ProductImageService();
            $imageService->deleteAllProductImages((int) $id);

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

        $imagePath = $image['path'];

        // Delete from database first
        $stmt = $db->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt->execute([$id]);

        // Check if this image file is used by any other product
        $stmt = $db->prepare("SELECT COUNT(*) FROM product_images WHERE path = ?");
        $stmt->execute([$imagePath]);
        $usageCount = (int) $stmt->fetchColumn();

        // Only delete the file if no other products are using it
        if ($usageCount === 0) {
            $fullPath = STORAGE_PATH . '/uploads/' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // If this was primary, set another image as primary (use lowest sort_order, then lowest id)
        if ($image['is_primary']) {
            $stmt = $db->prepare("UPDATE product_images SET is_primary = 1 WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
            $stmt->execute([$image['product_id']]);
        }

        $this->json(['success' => true]);
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
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

        // Reset all images for this product to non-primary
        $stmt = $db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
        $stmt->execute([$image['product_id']]);

        // Set this image as primary
        $stmt = $db->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
        $stmt->execute([$id]);

        $this->json(['success' => true]);
    }

    /**
     * Reorder images
     */
    public function reorderImages(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $images = $input['images'] ?? [];

        if (empty($images)) {
            $this->json(['success' => false, 'message' => 'No images provided']);
            return;
        }

        $db = Database::getInstance();

        foreach ($images as $imageData) {
            $imageId = (int) ($imageData['id'] ?? 0);
            $sortOrder = (int) ($imageData['sort_order'] ?? 0);

            if ($imageId > 0) {
                $stmt = $db->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
                $stmt->execute([$sortOrder, $imageId]);
            }
        }

        $this->json(['success' => true]);
    }

    /**
     * Bulk action for products (quick status change / delete)
     */
    public function bulkAction(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $action = $input['action'] ?? '';
        $ids = $input['ids'] ?? [];

        if (empty($action) || empty($ids)) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Validate action
        $validActions = ['active', 'draft', 'inactive', 'delete', 'ai-images'];
        if (!in_array($action, $validActions)) {
            $this->json(['success' => false, 'message' => 'Invalid action'], 400);
            return;
        }

        // Sanitize IDs to integers
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn($id) => $id > 0);

        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'No valid products selected'], 400);
            return;
        }

        $db = Database::getInstance();

        try {
            if ($action === 'ai-images') {
                // Generate AI images for selected products
                $imageService = new ProductImageService();
                $totalGenerated = 0;
                $processed = 0;

                foreach ($ids as $pid) {
                    $product = Product::find($pid);
                    if (!$product) continue;

                    // Get brand
                    $stmt = $db->prepare("
                        SELECT av.value as brand
                        FROM product_attributes pa
                        JOIN attribute_values av ON pa.attribute_value_id = av.id
                        JOIN attributes a ON av.attribute_id = a.id
                        WHERE pa.product_id = ? AND LOWER(a.name) = 'brand'
                        LIMIT 1
                    ");
                    $stmt->execute([$pid]);
                    $brandRow = $stmt->fetch();
                    $brand = $brandRow ? $brandRow['brand'] : '';

                    // Get category
                    $cats = $product->getCategories();
                    $catName = !empty($cats) ? $cats[0]['name'] : '';

                    // Get specs
                    $stmt = $db->prepare("SELECT spec_name, spec_value FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC LIMIT 10");
                    $stmt->execute([$pid]);
                    $specs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    $specArr = [];
                    foreach ($specs as $s) {
                        $specArr[] = ['name' => $s['spec_name'], 'value' => $s['spec_value']];
                    }

                    $result = $imageService->generateProductImages($pid, [
                        'name' => $product->name,
                        'brand' => $brand,
                        'sku' => $product->sku,
                        'category' => $catName,
                        'short_description' => $product->short_description ?? '',
                        'specifications' => $specArr,
                    ]);

                    $totalGenerated += $result['generated'];
                    $processed++;
                }

                $this->json([
                    'success' => true,
                    'message' => "Generated {$totalGenerated} images for {$processed} product(s)",
                ]);
                return;
            } elseif ($action === 'delete') {
                // Delete products and their related data
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                // Delete related data first
                $db->prepare("DELETE FROM product_categories WHERE product_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM product_images WHERE product_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM product_attributes WHERE product_id IN ($placeholders)")->execute($ids);
                $db->prepare("DELETE FROM product_variants WHERE product_id IN ($placeholders)")->execute($ids);

                // Delete products
                $stmt = $db->prepare("DELETE FROM products WHERE id IN ($placeholders)");
                $stmt->execute($ids);

                $this->json(['success' => true, 'message' => count($ids) . ' product(s) deleted']);
            } else {
                // Update status
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $db->prepare("UPDATE products SET status = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([$action], $ids));

                $this->json(['success' => true, 'message' => count($ids) . ' product(s) updated']);
            }
        } catch (\Throwable $e) {
            error_log("Bulk action error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Bulk edit products (advanced field updates)
     */
    public function bulkEdit(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'No products selected'], 400);
            return;
        }

        // Sanitize IDs
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn($id) => $id > 0);

        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'No valid products selected'], 400);
            return;
        }

        $db = Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        try {
            $db->beginTransaction();

            // Handle price updates
            if (!empty($input['price_action'])) {
                $priceValue = (float) ($input['price_value'] ?? 0);

                switch ($input['price_action']) {
                    case 'set':
                        $stmt = $db->prepare("UPDATE products SET price = ? WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$priceValue], $ids));
                        break;
                    case 'increase_percent':
                        $stmt = $db->prepare("UPDATE products SET price = price * (1 + ? / 100) WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$priceValue], $ids));
                        break;
                    case 'decrease_percent':
                        $stmt = $db->prepare("UPDATE products SET price = price * (1 - ? / 100) WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$priceValue], $ids));
                        break;
                    case 'increase_fixed':
                        $stmt = $db->prepare("UPDATE products SET price = price + ? WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$priceValue], $ids));
                        break;
                    case 'decrease_fixed':
                        $stmt = $db->prepare("UPDATE products SET price = GREATEST(0, price - ?) WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$priceValue], $ids));
                        break;
                }
            }

            // Handle stock updates
            if (!empty($input['stock_action'])) {
                $stockValue = (int) ($input['stock_value'] ?? 0);

                switch ($input['stock_action']) {
                    case 'set':
                        $stmt = $db->prepare("UPDATE products SET stock_quantity = ? WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$stockValue], $ids));
                        break;
                    case 'add':
                        $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$stockValue], $ids));
                        break;
                    case 'subtract':
                        $stmt = $db->prepare("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$stockValue], $ids));
                        break;
                }
            }

            // Handle vendor update
            if (isset($input['vendor_id'])) {
                $vendorId = $input['vendor_id'] === '' ? null : (int) $input['vendor_id'];
                $stmt = $db->prepare("UPDATE products SET vendor_id = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([$vendorId], $ids));
            }

            // Handle status update
            if (!empty($input['status'])) {
                $validStatuses = ['active', 'draft', 'inactive'];
                if (in_array($input['status'], $validStatuses)) {
                    $stmt = $db->prepare("UPDATE products SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$input['status']], $ids));
                }
            }

            // Handle flags
            if (isset($input['is_featured'])) {
                $stmt = $db->prepare("UPDATE products SET is_featured = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([(int) $input['is_featured']], $ids));
            }
            if (isset($input['is_new'])) {
                $stmt = $db->prepare("UPDATE products SET is_new = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([(int) $input['is_new']], $ids));
            }
            if (isset($input['is_on_sale'])) {
                $stmt = $db->prepare("UPDATE products SET is_on_sale = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([(int) $input['is_on_sale']], $ids));
            }

            $db->commit();

            $this->json([
                'success' => true,
                'message' => count($ids) . ' product(s) updated successfully'
            ]);
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Bulk edit error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while updating products'], 500);
        }
    }

    /**
     * Get all vendors
     */
    private function getVendors(): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $this->q($db, "SELECT id, name, status FROM vendors WHERE status = 'active' ORDER BY name ASC");
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get all brand attribute values for filter
     */
    private function getBrands(): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $this->q($db, "
                SELECT av.id, av.value as name
                FROM attribute_values av
                JOIN attributes a ON av.attribute_id = a.id
                WHERE LOWER(a.name) = 'brand'
                ORDER BY av.value ASC
            ");
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
            $stmt = $this->q($db, "SELECT * FROM attributes ORDER BY sort_order, name ASC");
            $attributes = $stmt->fetchAll() ?: [];

            // Get all attribute values
            $stmt = $this->q($db, "SELECT * FROM attribute_values ORDER BY attribute_id, sort_order, value ASC");
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

    /**
     * Get specifications for a product
     */
    private function getProductSpecifications(int $productId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$productId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Save product specifications from POST data
     */
    private function saveProductSpecifications(\PDO $db, int $productId): void
    {
        try {
            // Delete existing specifications
            $stmt = $db->prepare("DELETE FROM product_specifications WHERE product_id = ?");
            $stmt->execute([$productId]);

            $specNames = $_POST['spec_name'] ?? [];
            $specValues = $_POST['spec_value'] ?? [];

            foreach ($specNames as $i => $name) {
                $name = trim($name);
                $value = trim($specValues[$i] ?? '');

                if (empty($name) || empty($value)) {
                    continue;
                }

                $stmt = $db->prepare("
                    INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$productId, $name, $value, $i]);
            }
        } catch (\Throwable $e) {
            // Table might not exist yet - log and continue
            error_log("Could not save specifications: " . $e->getMessage());
        }
    }

    /**
     * Save product variants
     */
    private function saveProductVariants(\PDO $db, int $productId): void
    {
        $variants = $_POST['variants'] ?? [];

        foreach ($variants as $variantData) {
            $name = trim($variantData['name'] ?? '');
            $sku = trim($variantData['sku'] ?? '');
            $price = (float) ($variantData['price'] ?? 0);
            $stockQuantity = (int) ($variantData['stock_quantity'] ?? 0);
            $variantId = !empty($variantData['id']) ? (int) $variantData['id'] : null;

            // Skip empty variants
            if (empty($sku) && empty($name) && $price <= 0) {
                continue;
            }

            if ($variantId) {
                // Update existing variant
                $stmt = $db->prepare("
                    UPDATE product_variants
                    SET name = ?, sku = ?, price = ?, stock_quantity = ?, updated_at = NOW()
                    WHERE id = ? AND product_id = ?
                ");
                $stmt->execute([$name, $sku, $price, $stockQuantity, $variantId, $productId]);
            } else {
                // Insert new variant
                $stmt = $db->prepare("
                    INSERT INTO product_variants (product_id, name, sku, price, stock_quantity, is_active)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$productId, $name, $sku, $price, $stockQuantity]);
            }
        }
    }

    /**
     * Get reviews for a product
     */
    private function getProductReviews(int $productId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$productId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Update a review (AJAX)
     */
    public function updateReview(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        $review = $stmt->fetch();

        if (!$review) {
            $this->json(['success' => false, 'message' => 'Review not found']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $db->prepare("
            UPDATE reviews
            SET rating = ?, title = ?, content = ?, is_approved = ?
            WHERE id = ?
        ");
        $stmt->execute([
            (int) ($data['rating'] ?? $review['rating']),
            $data['title'] ?? $review['title'],
            $data['content'] ?? $review['content'],
            !empty($data['is_approved']) ? 1 : 0,
            $id
        ]);

        $this->json(['success' => true]);
    }

    /**
     * Delete a review (AJAX)
     */
    public function deleteReview(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);

        $this->json(['success' => true]);
    }

    /**
     * Generate AI content for product (AJAX)
     */
    public function generateAiContent(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $product = Product::find((int) $id);

        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $openai = new OpenAIService();

        if (!$openai->isConfigured()) {
            $this->json(['success' => false, 'message' => 'OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file.']);
            return;
        }

        // Get product category
        $categories = $product->getCategories();
        $categoryName = !empty($categories) ? $categories[0]['name'] : '';

        $result = $openai->generateProductContent([
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => $product->description,
            'short_description' => $product->short_description,
            'price' => $product->price,
            'category' => $categoryName,
        ]);

        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']]);
            return;
        }

        $this->json([
            'success' => true,
            'data' => $result['data'],
        ]);
    }

    /**
     * Search and fill AI product info for new products (AJAX)
     */
    public function searchAiInfo(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $productName = $data['name'] ?? '';
        $sku = $data['sku'] ?? '';

        if (empty($productName)) {
            $this->json(['success' => false, 'message' => 'Product name is required']);
            return;
        }

        $openai = new OpenAIService();

        if (!$openai->isConfigured()) {
            $this->json(['success' => false, 'message' => 'OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file.']);
            return;
        }

        $result = $openai->searchProductInfo($productName, $sku);

        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']]);
            return;
        }

        $this->json([
            'success' => true,
            'data' => $result['data'],
        ]);
    }

    /**
     * Regenerate product info from SKU using AI (AJAX)
     * Now uses the same generateCompleteProduct() pipeline as everything else.
     * Pattern matching identifies the product, AI writes the content.
     */
    public function regenerateFromSku(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $product = Product::find((int) $id);

        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found']);
            return;
        }

        if (empty($product->sku)) {
            $this->json(['success' => false, 'message' => 'Product has no SKU']);
            return;
        }

        [$aiService, $aiServiceName] = $this->resolveAiService();

        // Get product brand from attributes
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT av.value as brand
            FROM product_attributes pa
            JOIN attribute_values av ON pa.attribute_value_id = av.id
            JOIN attributes a ON av.attribute_id = a.id
            WHERE pa.product_id = ? AND LOWER(a.name) = 'brand'
            LIMIT 1
        ");
        $stmt->execute([$product->id]);
        $brandRow = $stmt->fetch();
        $brand = $brandRow ? $brandRow['brand'] : '';

        // Get product category
        $categories = $product->getCategories();
        $categoryName = !empty($categories) ? $categories[0]['name'] : '';

        // Use the SAME pipeline as everything else - pattern match first, then AI
        $result = $aiService->generateCompleteProduct($product->sku, $product->short_description ?? '', [
            'brand' => $brand,
            'category' => $categoryName,
            'price' => $product->price,
            'existingName' => $product->name,
            'existingDescription' => $product->description,
        ]);

        if (empty($result['success']) || empty($result['data']['name']) || $result['data']['name'] === $product->sku) {
            $this->json(['success' => false, 'message' => 'Could not identify product from SKU. Try the web search option instead.']);
            return;
        }

        $this->json([
            'success' => true,
            'data' => $result['data'],
        ]);
    }

    /**
     * Make a product production-ready using comprehensive AI (AJAX)
     * Fills ALL missing fields: name, descriptions, SEO, specs, category, weight
     */
    public function makeProductionReady(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token']);
            return;
        }

        $product = Product::find((int) $id);

        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found']);
            return;
        }

        [$aiService, $aiServiceName] = $this->resolveAiService();

        if ($aiServiceName === 'openai' && !$aiService->isConfigured()) {
            $this->json(['success' => false, 'message' => 'No AI key configured. Add ANTHROPIC_API_KEY (recommended, has image search) or OPENAI_API_KEY to .env.']);
            return;
        }

        // Get product brand from attributes
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT av.value as brand
            FROM product_attributes pa
            JOIN attribute_values av ON pa.attribute_value_id = av.id
            JOIN attributes a ON av.attribute_id = a.id
            WHERE pa.product_id = ? AND LOWER(a.name) = 'brand'
            LIMIT 1
        ");
        $stmt->execute([$product->id]);
        $brandRow = $stmt->fetch();
        $brand = $brandRow ? $brandRow['brand'] : '';

        // Get product category
        $categories = $product->getCategories();
        $categoryName = !empty($categories) ? $categories[0]['name'] : '';

        // Call comprehensive AI generation
        $result = $aiService->generateCompleteProduct($product->sku, $product->short_description ?? '', [
            'brand' => $brand,
            'category' => $categoryName,
            'price' => $product->price,
            'existingName' => $product->name,
            'existingDescription' => $product->description,
        ]);

        if (empty($result['success']) || empty($result['data'])) {
            $reason = $result['fallback_reason'] ?? $result['error'] ?? 'AI generation failed';
            $this->json(['success' => false, 'message' => "AI generation failed (ai={$aiServiceName}, {$reason})"]);
            return;
        }

        $aiData = $result['data'];

        // ?force=1 (POST or GET) means the admin explicitly asked us to
        // overwrite their hand-typed content. Without it the service only
        // fills empty fields. This stops a stray double-click from
        // silently nuking a manually-tuned name or description.
        $force = !empty($_REQUEST['force']);

        // The actual write fan-out lives in ProductService::applyAiDataToProduct
        // so the importer hits the same code path. See that method for the
        // safety rules (transaction, sanitization, clamping, force flag).
        $applyResult = ProductService::getInstance()->applyAiDataToProduct(
            $product->id,
            $aiData,
            ['force' => $force, 'generate_images' => true]
        );

        if (!$applyResult['success']) {
            $this->json([
                'success' => false,
                'message' => $applyResult['error'] ?? 'AI update failed',
            ]);
            return;
        }

        $applied = $applyResult['applied_fields'];
        $skipped = $applyResult['skipped_fields'];
        $imagesGenerated = $applyResult['images_generated'];

        // Did OpenAI actually answer, or did we silently fall back to a
        // pattern-match? The frontend surfaces this so admins know when
        // the model was unavailable.
        $method = $result['method'] ?? 'ai';
        $fallbackReason = $result['fallback_reason'] ?? null;
        $methodLabel = match ($method) {
            'ai'       => 'AI',
            'pattern'  => 'SKU pattern match (AI unavailable)',
            'fallback' => 'fallback (AI unavailable)',
            default    => $method,
        };

        $message = 'Product enhanced via ' . $methodLabel . '. ' . count($applied) . ' fields updated.';
        if (!empty($skipped) && !$force) {
            $message .= ' ' . count($skipped) . ' field(s) kept (use force to overwrite).';
        }
        if ($imagesGenerated > 0) {
            $message .= ' ' . $imagesGenerated . ' images generated.';
        }
        if ($method !== 'ai') {
            $message .= ' Tip: check OPENAI_API_KEY and try again for a deeper result.';
        }

        $this->json([
            'success' => true,
            'method' => $method,
            'fallback_reason' => $fallbackReason,
            'data' => $aiData,
            'updates_applied' => $applied,
            'skipped_fields' => $skipped,
            'force_used' => $force,
            'specs_saved' => $applyResult['specs_saved'],
            'attributes_saved' => $applyResult['attributes_saved'],
            'category_assigned' => $applyResult['category_assigned'],
            'brand_assigned' => $applyResult['brand_assigned'],
            'images_generated' => $imagesGenerated,
            'message' => $message,
        ]);
    }

    /**
     * Generate AI product images for a single product (AJAX)
     */
    public function generateAiImages(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $product = Product::find((int) $id);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $db = Database::getInstance();

        // Get brand
        $stmt = $db->prepare("
            SELECT av.value as brand
            FROM product_attributes pa
            JOIN attribute_values av ON pa.attribute_value_id = av.id
            JOIN attributes a ON av.attribute_id = a.id
            WHERE pa.product_id = ? AND LOWER(a.name) = 'brand'
            LIMIT 1
        ");
        $stmt->execute([$product->id]);
        $brandRow = $stmt->fetch();
        $brand = $brandRow ? $brandRow['brand'] : '';

        // Get category
        $categories = $product->getCategories();
        $categoryName = !empty($categories) ? $categories[0]['name'] : '';

        // Get specifications (wrapped in try-catch in case table doesn't exist yet)
        $specArray = [];
        try {
            $stmt = $db->prepare("SELECT spec_name, spec_value FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC LIMIT 10");
            $stmt->execute([$product->id]);
            $specs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($specs as $spec) {
                $specArray[] = ['name' => $spec['spec_name'], 'value' => $spec['spec_value']];
            }
        } catch (\Throwable $e) {
            // Table may not exist yet
        }

        // Use output buffering to catch any stray PHP warnings from GD/file ops
        ob_start();
        try {
            $imageService = new ProductImageService();
            $result = $imageService->generateProductImages($product->id, [
                'name' => $product->name,
                'brand' => $brand,
                'sku' => $product->sku,
                'category' => $categoryName,
                'short_description' => $product->short_description ?? '',
                'specifications' => $specArray,
            ]);
        } catch (\Throwable $e) {
            $result = ['success' => false, 'generated' => 0, 'message' => $e->getMessage()];
        }
        $warnings = ob_get_clean();
        if ($warnings) {
            error_log("AI image generation warnings: " . $warnings);
        }

        $this->json([
            'success' => $result['success'],
            'generated' => $result['generated'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Duplicate a product (AJAX)
     */
    public function duplicate(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $product = Product::find((int) $id);

        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // Generate unique slug
            $baseSlug = $product->slug . '-copy';
            $slug = $baseSlug;
            $counter = 1;
            while (true) {
                $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
                $stmt->execute([$slug]);
                if (!$stmt->fetch()) break;
                $slug = $baseSlug . '-' . (++$counter);
                if ($counter > 100) {
                    $slug = $baseSlug . '-' . uniqid();
                    break;
                }
            }

            // Generate unique SKU
            $baseSku = $product->sku . '-COPY';
            $newSku = $baseSku;
            $counter = 1;
            while (true) {
                $stmt = $db->prepare("SELECT id FROM products WHERE sku = ? LIMIT 1");
                $stmt->execute([$newSku]);
                if (!$stmt->fetch()) break;
                $newSku = $baseSku . '-' . (++$counter);
                if ($counter > 100) {
                    $newSku = $baseSku . '-' . uniqid();
                    break;
                }
            }

            // Create duplicate product
            $newProduct = Product::create([
                'sku' => $newSku,
                'name' => $product->name . ' (Copy)',
                'slug' => $slug,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'type' => $product->type ?? 'simple',
                'status' => 'draft', // Always start as draft
                'price' => $product->price,
                'compare_price' => $product->compare_price,
                'cost_price' => $product->cost_price,
                'manage_stock' => $product->manage_stock,
                'stock_quantity' => $product->stock_quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'weight' => $product->weight,
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
                'vendor_id' => $product->vendor_id,
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'is_featured' => 0,
                'is_new' => 0,
                'is_on_sale' => $product->is_on_sale,
            ]);

            // Copy categories
            $stmt = $db->prepare("SELECT category_id, is_primary FROM product_categories WHERE product_id = ?");
            $stmt->execute([$product->id]);
            $categories = $stmt->fetchAll();
            foreach ($categories as $cat) {
                $stmt = $db->prepare("INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)");
                $stmt->execute([$newProduct->id, $cat['category_id'], $cat['is_primary']]);
            }

            // Copy attributes
            $stmt = $db->prepare("SELECT attribute_id, attribute_value_id, custom_value FROM product_attributes WHERE product_id = ?");
            $stmt->execute([$product->id]);
            $attributes = $stmt->fetchAll();
            foreach ($attributes as $attr) {
                $stmt = $db->prepare("INSERT INTO product_attributes (product_id, attribute_id, attribute_value_id, custom_value) VALUES (?, ?, ?, ?)");
                $stmt->execute([$newProduct->id, $attr['attribute_id'], $attr['attribute_value_id'], $attr['custom_value']]);
            }

            // Copy specifications
            $stmt = $db->prepare("SELECT spec_name, spec_value, sort_order FROM product_specifications WHERE product_id = ?");
            $stmt->execute([$product->id]);
            $specs = $stmt->fetchAll();
            foreach ($specs as $spec) {
                $stmt = $db->prepare("INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$newProduct->id, $spec['spec_name'], $spec['spec_value'], $spec['sort_order']]);
            }

            // Copy images (reference same files, don't duplicate actual files)
            $stmt = $db->prepare("SELECT path, alt_text, is_primary, sort_order FROM product_images WHERE product_id = ?");
            $stmt->execute([$product->id]);
            $images = $stmt->fetchAll();
            foreach ($images as $img) {
                $stmt = $db->prepare("INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$newProduct->id, $img['path'], $img['alt_text'], $img['is_primary'], $img['sort_order']]);
            }

            $db->commit();

            $this->json([
                'success' => true,
                'product_id' => $newProduct->id,
                'message' => 'Product duplicated successfully'
            ]);
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Product duplication error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to duplicate product']);
        }
    }

    /**
     * Autosave product (AJAX)
     */
    public function autosave(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $product = Product::find((int) $id);

        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found']);
            return;
        }

        // Accept both JSON body and FormData (multipart/form-data)
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data)) {
            $data = $_POST;
        }

        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'No data provided']);
            return;
        }

        // Only update safe fields
        $allowedFields = [
            'name', 'description', 'short_description', 'price', 'compare_price',
            'cost_price', 'stock_quantity', 'weight', 'meta_title', 'meta_description'
        ];

        $updates = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $product->$field = $data[$field];
                $updates[] = $field;
            }
        }

        if (empty($updates)) {
            $this->json(['success' => false, 'message' => 'No valid fields to update']);
            return;
        }

        try {
            $product->save();
            $this->json([
                'success' => true,
                'message' => 'Auto-saved',
                'updated_fields' => $updates,
                'saved_at' => date('H:i:s')
            ]);
        } catch (\Throwable $e) {
            error_log("Autosave error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to save']);
        }
    }

    /**
     * Export products to CSV
     */
    public function export(): void
    {
        $db = Database::getInstance();

        // Get filters from query string
        $status = $_GET['status'] ?? '';
        $vendor = $_GET['vendor'] ?? '';
        $category = $_GET['category'] ?? '';

        $where = ['1=1'];
        $params = [];

        if ($status) {
            $where[] = "p.status = ?";
            $params[] = $status;
        }

        if ($vendor) {
            $where[] = "p.vendor_id = ?";
            $params[] = $vendor;
        }

        if ($category) {
            $where[] = "p.id IN (SELECT product_id FROM product_categories WHERE category_id = ?)";
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $db->prepare("
            SELECT
                p.id,
                p.sku,
                p.name,
                p.slug,
                p.description,
                p.short_description,
                p.type,
                p.status,
                p.price,
                p.compare_price,
                p.cost_price,
                p.manage_stock,
                p.stock_quantity,
                p.low_stock_threshold,
                p.weight,
                p.is_featured,
                p.is_new,
                p.is_on_sale,
                p.meta_title,
                p.meta_description,
                v.name as vendor_name,
                GROUP_CONCAT(DISTINCT c.name SEPARATOR '|') as categories
            FROM products p
            LEFT JOIN vendors v ON v.id = p.vendor_id
            LEFT JOIN product_categories pc ON pc.product_id = p.id
            LEFT JOIN categories c ON c.id = pc.category_id
            WHERE $whereClause
            GROUP BY p.id
            ORDER BY p.id ASC
        ");
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="products_export_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header row
        fputcsv($output, [
            'ID',
            'SKU',
            'Name',
            'Slug',
            'Description',
            'Short Description',
            'Type',
            'Status',
            'Price',
            'Compare Price',
            'Cost Price',
            'Manage Stock',
            'Stock Quantity',
            'Low Stock Threshold',
            'Weight',
            'Featured',
            'New',
            'On Sale',
            'Meta Title',
            'Meta Description',
            'Vendor',
            'Categories',
        ]);

        // Write data rows
        foreach ($products as $product) {
            fputcsv($output, [
                $product['id'],
                $product['sku'],
                $product['name'],
                $product['slug'],
                $product['description'],
                $product['short_description'],
                $product['type'],
                $product['status'],
                $product['price'],
                $product['compare_price'],
                $product['cost_price'],
                $product['manage_stock'] ? 'yes' : 'no',
                $product['stock_quantity'],
                $product['low_stock_threshold'],
                $product['weight'],
                $product['is_featured'] ? 'yes' : 'no',
                $product['is_new'] ? 'yes' : 'no',
                $product['is_on_sale'] ? 'yes' : 'no',
                $product['meta_title'],
                $product['meta_description'],
                $product['vendor_name'],
                $product['categories'],
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Show import form
     */
    public function importForm(): void
    {
        $db = Database::getInstance();
        $categories = Category::getTree();

        // Vendors for the AI-mode default-vendor selector
        $vendors = [];
        try {
            $vendors = $this->q($db, "SELECT id, name FROM vendors WHERE status IN ('active','pending') ORDER BY name")->fetchAll() ?: [];
        } catch (\Throwable $e) {
            // vendors table may be missing on some installs
        }

        // Tax rate from store settings (default 15% for South African VAT)
        $taxRate = 15.0;
        try {
            if (function_exists('getSetting')) {
                $taxRate = (float) getSetting('tax_rate', 'store', '15');
            }
        } catch (\Throwable $e) {
            // fallback to 15
        }

        // Recent imports for the new "Recent Imports" history list
        $history = [];
        try {
            $history = $this->q($db, "SELECT * FROM product_import_logs ORDER BY created_at DESC LIMIT 10")->fetchAll() ?: [];
        } catch (\Throwable $e) {
            // table may not exist yet
        }

        $this->layout('admin');
        $this->view('pages/products/import', [
            'page_title' => 'Import / Export Products',
            'active_page' => 'products',
            'categories' => $categories,
            'vendors' => $vendors,
            'taxRate' => $taxRate,
            'history' => $history,
        ]);
    }

    /**
     * Process CSV import
     */
    public function import(): void
    {
        if (!$this->validateCsrf()) {
            flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/admin/products/import');
            return;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            flash('error', 'Please select a CSV file to import.');
            $this->redirect('/admin/products/import');
            return;
        }

        $file = $_FILES['csv_file'];

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedTypes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
        if (!in_array($mimeType, $allowedTypes)) {
            flash('error', 'Invalid file type. Please upload a CSV file.');
            $this->redirect('/admin/products/import');
            return;
        }

        // Read CSV file
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            flash('error', 'Could not read the uploaded file.');
            $this->redirect('/admin/products/import');
            return;
        }

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            flash('error', 'CSV file is empty or invalid.');
            $this->redirect('/admin/products/import');
            return;
        }

        // Normalize header names
        $header = array_map(function ($col) {
            return strtolower(trim(str_replace([' ', '-'], '_', $col)));
        }, $header);

        // Required columns
        $requiredColumns = ['sku', 'name', 'price'];
        $missingColumns = array_diff($requiredColumns, $header);
        if (!empty($missingColumns)) {
            fclose($handle);
            flash('error', 'Missing required columns: ' . implode(', ', $missingColumns));
            $this->redirect('/admin/products/import');
            return;
        }

        $db = Database::getInstance();
        $updateExisting = !empty($_POST['update_existing']);
        $aiGenerate = !empty($_POST['ai_generate']);
        $aiFields = $_POST['ai_fields'] ?? [];
        $imported = 0;
        $updated = 0;
        $aiGenerated = 0;
        $errors = [];
        $rowNum = 1;

        // Initialize AI service if needed
        $openai = null;
        if ($aiGenerate) {
            $openai = new OpenAIService();
            if (!$openai->isConfigured()) {
                flash('error', 'AI generation requested but OpenAI API key is not configured.');
                $this->redirect('/admin/products/import');
                return;
            }
        }

        // Get vendor lookup
        $vendorLookup = [];
        $stmt = $this->q($db, "SELECT id, name FROM vendors");
        foreach ($stmt->fetchAll() as $v) {
            $vendorLookup[strtolower($v['name'])] = $v['id'];
        }

        // Get category lookup
        $categoryLookup = [];
        $stmt = $this->q($db, "SELECT id, name FROM categories");
        foreach ($stmt->fetchAll() as $c) {
            $categoryLookup[strtolower($c['name'])] = $c['id'];
        }

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map row to associative array
            $data = [];
            foreach ($header as $i => $col) {
                $data[$col] = $row[$i] ?? '';
            }

            // Validate required fields
            if (empty($data['sku']) || empty($data['name']) || !isset($data['price'])) {
                $errors[] = "Row $rowNum: Missing required fields (sku, name, or price)";
                continue;
            }

            // Check if product exists
            $stmt = $db->prepare("SELECT id FROM products WHERE sku = ?");
            $stmt->execute([$data['sku']]);
            $existingId = $stmt->fetchColumn();

            if ($existingId && !$updateExisting) {
                $errors[] = "Row $rowNum: SKU '{$data['sku']}' already exists (skipped)";
                continue;
            }

            // Prepare product data
            $slug = $this->getColumnValue($data, 'slug');
            if (!$slug) {
                $slug = slugify($data['name']);
                // Ensure unique
                $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE slug LIKE ? AND id != ?");
                $stmt->execute([$slug . '%', $existingId ?: 0]);
                $count = $stmt->fetchColumn();
                if ($count > 0) {
                    $slug .= '-' . ($count + 1);
                }
            }

            // Lookup vendor
            $vendorId = null;
            $vendorName = $this->getColumnValue($data, 'vendor', 'vendor_name');
            if ($vendorName && isset($vendorLookup[strtolower($vendorName)])) {
                $vendorId = $vendorLookup[strtolower($vendorName)];
            }

            $productData = [
                'sku' => $data['sku'],
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $this->getColumnValue($data, 'description'),
                'short_description' => $this->getColumnValue($data, 'short_description'),
                'type' => $this->getColumnValue($data, 'type') ?: 'simple',
                'status' => $this->getColumnValue($data, 'status') ?: 'draft',
                'price' => (float) $data['price'],
                'compare_price' => $this->getColumnValue($data, 'compare_price') ?: null,
                'cost_price' => $this->getColumnValue($data, 'cost_price') ?: null,
                'manage_stock' => $this->parseBool($this->getColumnValue($data, 'manage_stock')),
                'stock_quantity' => (int) ($this->getColumnValue($data, 'stock_quantity', 'stock') ?: 0),
                'low_stock_threshold' => (int) ($this->getColumnValue($data, 'low_stock_threshold') ?: 5),
                'weight' => $this->getColumnValue($data, 'weight') ?: null,
                'vendor_id' => $vendorId,
                'is_featured' => $this->parseBool($this->getColumnValue($data, 'is_featured', 'featured')),
                'is_new' => $this->parseBool($this->getColumnValue($data, 'is_new', 'new')),
                'is_on_sale' => $this->parseBool($this->getColumnValue($data, 'is_on_sale', 'on_sale')),
                'meta_title' => $this->getColumnValue($data, 'meta_title'),
                'meta_description' => $this->getColumnValue($data, 'meta_description'),
            ];

            try {
                if ($existingId) {
                    // Update existing
                    $setClauses = [];
                    $params = [];
                    foreach ($productData as $col => $val) {
                        if ($col !== 'sku') { // Don't update SKU
                            $setClauses[] = "`$col` = ?";
                            $params[] = $val;
                        }
                    }
                    $params[] = $existingId;

                    $stmt = $db->prepare("UPDATE products SET " . implode(', ', $setClauses) . " WHERE id = ?");
                    $stmt->execute($params);
                    $productId = $existingId;
                    $updated++;
                } else {
                    // Insert new
                    $columns = array_keys($productData);
                    $placeholders = array_fill(0, count($columns), '?');

                    $stmt = $db->prepare("
                        INSERT INTO products (" . implode(', ', array_map(fn($c) => "`$c`", $columns)) . ")
                        VALUES (" . implode(', ', $placeholders) . ")
                    ");
                    $stmt->execute(array_values($productData));
                    $productId = $db->lastInsertId();
                    $imported++;
                }

                // Handle categories
                $categoryStr = $this->getColumnValue($data, 'categories', 'category');
                if ($categoryStr) {
                    // Delete existing category associations
                    $stmt = $db->prepare("DELETE FROM product_categories WHERE product_id = ?");
                    $stmt->execute([$productId]);

                    $categoryNames = array_map('trim', explode('|', $categoryStr));
                    $isPrimary = true;
                    foreach ($categoryNames as $catName) {
                        if (isset($categoryLookup[strtolower($catName)])) {
                            $catId = $categoryLookup[strtolower($catName)];
                            $stmt = $db->prepare("INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)");
                            $stmt->execute([$productId, $catId, $isPrimary ? 1 : 0]);
                            $isPrimary = false;
                        }
                    }
                }

                // AI Generation for missing fields
                if ($openai && !empty($aiFields)) {
                    $needsAi = false;
                    $missingFields = [];

                    // Check which fields are empty and need AI generation
                    if (in_array('description', $aiFields) && empty($productData['description'])) {
                        $needsAi = true;
                        $missingFields[] = 'description';
                    }
                    if (in_array('short_description', $aiFields) && empty($productData['short_description'])) {
                        $needsAi = true;
                        $missingFields[] = 'short_description';
                    }
                    if (in_array('meta_title', $aiFields) && empty($productData['meta_title'])) {
                        $needsAi = true;
                        $missingFields[] = 'meta_title';
                    }
                    if (in_array('meta_description', $aiFields) && empty($productData['meta_description'])) {
                        $needsAi = true;
                        $missingFields[] = 'meta_description';
                    }

                    if ($needsAi) {
                        try {
                            $aiResult = $openai->searchProductInfo($data['name'], $data['sku']);

                            if (!empty($aiResult['success']) && !empty($aiResult['data'])) {
                                $aiData = $aiResult['data'];
                                $updateFields = [];
                                $updateParams = [];

                                if (in_array('description', $missingFields) && !empty($aiData['description'])) {
                                    $updateFields[] = 'description = ?';
                                    $updateParams[] = $aiData['description'];
                                }
                                if (in_array('short_description', $missingFields) && !empty($aiData['short_description'])) {
                                    $updateFields[] = 'short_description = ?';
                                    $updateParams[] = $aiData['short_description'];
                                }
                                if (in_array('meta_title', $missingFields) && !empty($aiData['meta_title'])) {
                                    $updateFields[] = 'meta_title = ?';
                                    $updateParams[] = $aiData['meta_title'];
                                }
                                if (in_array('meta_description', $missingFields) && !empty($aiData['meta_description'])) {
                                    $updateFields[] = 'meta_description = ?';
                                    $updateParams[] = $aiData['meta_description'];
                                }

                                if (!empty($updateFields)) {
                                    $updateParams[] = $productId;
                                    $stmt = $db->prepare("UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?");
                                    $stmt->execute($updateParams);
                                }

                                // Handle AI-generated specifications
                                if (in_array('specifications', $aiFields) && !empty($aiData['specifications'])) {
                                    foreach ($aiData['specifications'] as $specIndex => $spec) {
                                        if (!empty($spec['name']) && !empty($spec['value'])) {
                                            $stmt = $db->prepare("
                                                INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order)
                                                VALUES (?, ?, ?, ?)
                                            ");
                                            $stmt->execute([$productId, $spec['name'], $spec['value'], $specIndex]);
                                        }
                                    }
                                }

                                $aiGenerated++;
                            }
                        } catch (\Throwable $aiError) {
                            // Log AI error but don't fail the import
                            error_log("AI generation error for product {$data['sku']}: " . $aiError->getMessage());
                        }
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = "Row $rowNum: Database error - " . $e->getMessage();
            }
        }

        fclose($handle);

        // Build result message
        $messages = [];
        if ($imported > 0) {
            $messages[] = "$imported products imported";
        }
        if ($updated > 0) {
            $messages[] = "$updated products updated";
        }
        if ($aiGenerated > 0) {
            $messages[] = "$aiGenerated products enhanced with AI";
        }
        if (empty($messages)) {
            $messages[] = "No products were imported";
        }

        if (!empty($errors)) {
            $_SESSION['import_errors'] = array_slice($errors, 0, 20); // Store first 20 errors
            if (count($errors) > 20) {
                $_SESSION['import_errors'][] = "... and " . (count($errors) - 20) . " more errors";
            }
        }

        flash('success', implode(', ', $messages));
        $this->redirect('/admin/products/import');
    }

    /**
     * Process AJAX import (drag & drop column mapping). Thin wrapper around
     * runImportLoop() so the same code path handles synchronous imports here
     * and queued background imports via processImportJob().
     *
     * Large AI imports (>50 rows) are queued via importEnqueue() instead so
     * the user can close the browser tab.
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

        // Normalize South African price format on the way in: "R1 392.78"
        // and "1,392.78" both become "1392.78" before runImportLoop sees them.
        foreach ($data as &$_row) {
            foreach (['price', 'compare_price', 'cost_price'] as $_field) {
                if (isset($_row[$_field]) && is_string($_row[$_field]) && $_row[$_field] !== '') {
                    $v = preg_replace('/^R\s*/i', '', $_row[$_field]);
                    $v = str_replace([' ', ','], ['', ''], $v);
                    $_row[$_field] = $v;
                }
            }
        }
        unset($_row);

        $options = $this->extractImportOptions($_POST);
        $db = Database::getInstance();

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
     * Generate product name using AI (simple template)
     */
    private function aiGenerateName(string $sku, array $row): string
    {
        // Simple name generation based on available data
        $parts = [];
        if (!empty($row['vendor'])) $parts[] = $row['vendor'];
        if (!empty($row['category'])) $parts[] = $row['category'];
        $parts[] = $sku;
        return implode(' ', $parts);
    }

    /**
     * Generate product description using AI (simple template)
     */
    private function aiGenerateDescription(string $name, array $row): string
    {
        $desc = "Introducing the $name. ";
        if (!empty($row['vendor'])) {
            $desc .= "Manufactured by {$row['vendor']}. ";
        }
        $desc .= "High quality product available at competitive prices. Order now for fast delivery.";
        return $desc;
    }

    /**
     * Extract a proper product name from short_description
     * e.g., "Intel Core i5 12400 Up to 4.4 GHz; 6 Core..." -> "Intel Core i5-12400 Processor"
     * e.g., "Intel Celeron G6900 LGA 1700 3.4 GHz..." -> "Intel Celeron G6900 Processor"
     */
    private function extractProductNameFromDescription(string $shortDesc, string $sku): ?string
    {
        if (empty($shortDesc)) {
            return null;
        }

        // Intel Core patterns: "Intel Core i5 12400", "Intel Core i7-12700K", "Intel® Core™ i9 14900KF"
        if (preg_match('/Intel[®™\s]*Core[®™\s]*(i[3579])[\s\-]*(\d{4,5})([A-Z]*)/i', $shortDesc, $matches)) {
            $tier = strtolower($matches[1]); // i3, i5, i7, i9
            $model = $matches[2]; // 12400, 14900
            $suffix = strtoupper($matches[3] ?? ''); // K, KF, F, etc.

            $name = "Intel Core " . ucfirst($tier) . "-" . $model;
            if ($suffix) {
                $name .= $suffix;
            }
            $name .= " Processor";
            return $name;
        }

        // Intel Celeron: "Intel Celeron G6900"
        if (preg_match('/Intel[®™\s]*Celeron[®™\s]*(G\d{4})/i', $shortDesc, $matches)) {
            return "Intel Celeron " . strtoupper($matches[1]) . " Processor";
        }

        // Intel Pentium: "Intel Pentium Gold G7400"
        if (preg_match('/Intel[®™\s]*Pentium[®™\s]*(Gold\s*)?(G\d{4})/i', $shortDesc, $matches)) {
            $gold = !empty($matches[1]) ? 'Gold ' : '';
            return "Intel Pentium " . $gold . strtoupper($matches[2]) . " Processor";
        }

        // Intel Core Ultra: "Intel Core Ultra 5/7/9"
        if (preg_match('/Intel[®™\s]*Core[®™\s]*Ultra\s*(\d)\s*(\d{3}[A-Z]*)/i', $shortDesc, $matches)) {
            return "Intel Core Ultra " . $matches[1] . " " . strtoupper($matches[2]) . " Processor";
        }

        // AMD Ryzen: "AMD Ryzen 5 5600X", "Ryzen 7 7800X3D"
        if (preg_match('/(?:AMD\s*)?Ryzen\s*(\d)\s*(\d{4})([A-Z0-9]*)/i', $shortDesc, $matches)) {
            $tier = $matches[1];
            $model = $matches[2];
            $suffix = strtoupper($matches[3] ?? '');
            return "AMD Ryzen " . $tier . " " . $model . $suffix . " Processor";
        }

        // NVIDIA GeForce: "GeForce RTX 4090", "RTX 4080 Ti"
        if (preg_match('/(GeForce\s*)?(RTX|GTX)\s*(\d{4})(\s*Ti|\s*SUPER)?/i', $shortDesc, $matches)) {
            $series = strtoupper($matches[2]);
            $model = $matches[3];
            $variant = isset($matches[4]) ? ' ' . trim(ucfirst(strtolower($matches[4]))) : '';
            return "NVIDIA GeForce " . $series . " " . $model . $variant . " Graphics Card";
        }

        // AMD Radeon: "Radeon RX 7900 XTX"
        if (preg_match('/Radeon\s*(RX)\s*(\d{4})(\s*XT[X]?)?/i', $shortDesc, $matches)) {
            $model = $matches[2];
            $variant = isset($matches[3]) ? ' ' . strtoupper(trim($matches[3])) : '';
            return "AMD Radeon RX " . $model . $variant . " Graphics Card";
        }

        return null;
    }

    /**
     * Get column value with fallback names
     */
    private function getColumnValue(array $data, string ...$names): ?string
    {
        foreach ($names as $name) {
            if (isset($data[$name]) && $data[$name] !== '') {
                return $data[$name];
            }
        }
        return null;
    }

    /**
     * Parse boolean from various formats
     */
    private function parseBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'yes', 'true', 'y', 'on']);
    }

    /**
     * Handle multiple image uploads with WebP conversion and 600x600 cropping
     */
    private function handleImageUploads(int $productId, array $files): void
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Normalize files array structure
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;
        $uploadedCount = 0;

        $db = Database::getInstance();

        // Check if product already has images
        $stmt = $db->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
        $stmt->execute([$productId]);
        $existingImages = (int) $stmt->fetchColumn();

        for ($i = 0; $i < $fileCount; $i++) {
            // Handle both single and multiple file uploads
            if (is_array($files['name'])) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
            } else {
                $file = $files;
            }

            // Skip empty uploads
            if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            // Validate type
            if (!in_array($file['type'], $allowedTypes)) {
                flash('error', "Image '{$file['name']}' has invalid type. Skipped.");
                continue;
            }

            // Validate size
            if ($file['size'] > $maxSize) {
                flash('error', "Image '{$file['name']}' is too large (max 5MB). Skipped.");
                continue;
            }

            // Process and save image
            $result = $this->processImage($file['tmp_name'], $productId);

            if ($result) {
                // Insert into database
                $isPrimary = ($existingImages === 0 && $uploadedCount === 0) ? 1 : 0;
                $stmt = $db->prepare("INSERT INTO product_images (product_id, path, is_primary) VALUES (?, ?, ?)");
                $stmt->execute([$productId, $result, $isPrimary]);
                $uploadedCount++;
            }
        }

        if ($uploadedCount > 0) {
            flash('success', "$uploadedCount image(s) uploaded and processed successfully.");
        }
    }

    /**
     * Process image: convert to WebP and resize to 600x600
     */
    private function processImage(string $tmpPath, int $productId): ?string
    {
        // Create GD image from source
        $imageInfo = getimagesize($tmpPath);
        if (!$imageInfo) {
            return null;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create source image based on type
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($tmpPath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($tmpPath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($tmpPath);
                break;
            default:
                return null;
        }

        if (!$sourceImage) {
            return null;
        }

        // Calculate crop dimensions for 1024x1024 (center crop)
        $targetSize = 1024;

        // Determine crop area (center square)
        $cropSize = min($sourceWidth, $sourceHeight);
        $cropX = ($sourceWidth - $cropSize) / 2;
        $cropY = ($sourceHeight - $cropSize) / 2;

        // Create destination image
        $destImage = imagecreatetruecolor($targetSize, $targetSize);

        // Preserve transparency for PNG
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);
        $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
        imagefilledrectangle($destImage, 0, 0, $targetSize, $targetSize, $transparent);

        // Resize and crop
        imagecopyresampled(
            $destImage,
            $sourceImage,
            0, 0,
            (int) $cropX, (int) $cropY,
            $targetSize, $targetSize,
            $cropSize, $cropSize
        );

        // Generate filename
        $filename = 'product-' . $productId . '-' . time() . '-' . uniqid() . '.webp';
        $path = 'products/' . $filename;
        $fullPath = STORAGE_PATH . '/uploads/' . $path;

        // Create directory if needed
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Save as WebP with quality 85
        $success = imagewebp($destImage, $fullPath, 85);

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($destImage);

        return $success ? $path : null;
    }

    /**
     * Legacy single image upload handler (for backwards compatibility)
     */
    private function handleImageUpload(int $productId, array $file): void
    {
        $this->handleImageUploads($productId, $file);
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

    // ============================================================================
    // AI Import Pipeline (Phases 1-4)
    // ============================================================================

    /**
     * Pick the AI service for bulk imports. Claude (with web search) when
     * ANTHROPIC_API_KEY is configured, otherwise fall back to OpenAI so
     * existing installs keep working without an env change.
     */
    /**
     * Run a SQL statement with optional bound params. Wraps the
     * prepare/execute dance so callers can chain ->fetch() / ->fetchAll()
     * / ->fetchColumn() / ->rowCount() like they would on a raw PDO::query()
     * result. Falls back to PDO::query() when there are no params (no need
     * to prepare).
     */
    private function q(\PDO $db, string $sql, array $params = []): \PDOStatement
    {
        if (empty($params)) {
            return $db->query($sql);
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Pick the AI service for this request.
     *
     * When $preferredModel is passed (from the import UI's model picker),
     * the routing is deterministic: any "claude-*" id goes to ClaudeService,
     * any "gpt-*" id goes to OpenAIService, with the chosen model set as
     * an override. Caller's tag string follows so failure messages can
     * say "ai=claude (claude-haiku-4-5)" instead of just "ai=claude".
     *
     * Without a preference we fall back to: Claude if its key is present,
     * OpenAI otherwise.
     */
    private function resolveAiService(?string $preferredModel = null): array
    {
        $preferredModel = trim((string) $preferredModel);
        if ($preferredModel !== '') {
            if (str_starts_with($preferredModel, 'claude-')) {
                $svc = new ClaudeService();
                $svc->setModel($preferredModel);
                return [$svc, 'claude (' . $preferredModel . ')'];
            }
            if (str_starts_with($preferredModel, 'gpt-')) {
                $svc = new OpenAIService();
                $svc->setModel($preferredModel);
                return [$svc, 'openai (' . $preferredModel . ')'];
            }
        }
        $claude = new ClaudeService();
        if ($claude->hasApiKey()) {
            return [$claude, 'claude'];
        }
        return [new OpenAIService(), 'openai'];
    }

    /**
     * Whitelist of models the import UI is allowed to ask for. Keeps
     * arbitrary model strings out of the request body without going
     * through CSRF/auth on a more invasive surface.
     */
    private const ALLOWED_IMPORT_MODELS = [
        'claude-opus-4-7',
        'claude-sonnet-4-6',
        'claude-haiku-4-5',
        'gpt-5-mini',
        'gpt-5-nano',
        'gpt-4.1-mini',
        'gpt-4o-mini',
    ];

    private function sanitizeImportModel(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $raw = trim($raw);
        return in_array($raw, self::ALLOWED_IMPORT_MODELS, true) ? $raw : null;
    }

    /**
     * Build a short, user-facing reason string for why the AI did not return
     * a usable product name. Pulls from the AI service's structured result
     * so the import error message tells the operator *why* a row stubbed out.
     */
    private function describeAiFailure(?array $aiResult, string $serviceName): string
    {
        if ($aiResult === null) {
            return "ai={$serviceName}, no_call";
        }
        $method = $aiResult['method'] ?? 'unknown';
        $usage = $aiResult['usage'] ?? [];
        $tokenSummary = '';
        if (!empty($usage)) {
            $in = (int) ($usage['input_tokens'] ?? 0);
            $out = (int) ($usage['output_tokens'] ?? 0);
            $tokenSummary = " [tokens in/out: {$in}/{$out}]";
        }
        if ($method === 'fallback') {
            $reason = (string) ($aiResult['fallback_reason'] ?? 'unknown');
            return "ai={$serviceName}, fallback: " . substr($reason, 0, 120) . $tokenSummary;
        }
        $data = $aiResult['data'] ?? [];
        if (empty($data['ai_identified'])) {
            return "ai={$serviceName}, model returned ai_identified=false" . $tokenSummary;
        }
        if (empty($data['name'])) {
            return "ai={$serviceName}, model returned empty name" . $tokenSummary;
        }
        return "ai={$serviceName}, name rejected by validator" . $tokenSummary;
    }

    /**
     * Return a short, user-facing list of which content fields the AI
     * didn't fill. Empty string when the response was complete. Used so
     * the operator can see *which* fields are missing per row without
     * needing server-log access - the symptom "description blank, no
     * specs, no images" looks identical to "AI worked fine but the
     * product simply isn't well-known" until you can see this list.
     */
    private function describeMissingAiFields(?array $aiData, int $imagesSaved = 0): string
    {
        if (!is_array($aiData)) {
            return '';
        }
        $missing = [];
        if (empty(trim((string) ($aiData['description'] ?? '')))) {
            $missing[] = 'description';
        }
        if (empty(trim((string) ($aiData['short_description'] ?? '')))) {
            $missing[] = 'short_description';
        }
        if (empty($aiData['specifications']) || !is_array($aiData['specifications'])) {
            $missing[] = 'specifications';
        }
        if (empty($aiData['attributes']) || !is_array($aiData['attributes']) || count($aiData['attributes']) === 0) {
            $missing[] = 'attributes';
        }
        // Only flag images as missing if NEITHER the AI returned URLs
        // NOR the image-search fallback (generateProductImages, inside
        // applyAiDataToProduct) actually saved anything. The previous
        // check fired on AI-returned URLs alone, which always claimed
        // "missing: images" for OpenAI (it never returns image URLs)
        // even when the fallback succeeded - misleading the operator
        // into thinking images weren't saved when they actually were.
        if ($imagesSaved === 0) {
            $missing[] = 'images';
        }
        if (empty(trim((string) ($aiData['meta_title'] ?? ''))) && empty(trim((string) ($aiData['meta_description'] ?? '')))) {
            $missing[] = 'seo';
        }
        return implode(', ', $missing);
    }

    /**
     * Tell the operator the most useful next step based on which fields
     * came back empty and which AI service ran. Images are the common
     * failure case for OpenAI rows (no built-in web image search) and
     * Claude rows return URLs but they sometimes 404 on download - the
     * recommendation differs.
     */
    private function describeRemediation(string $missingCsv, string $serviceName): string
    {
        $isImagesOnly = trim($missingCsv) === 'images';
        $isClaude = str_starts_with($serviceName, 'claude');
        if ($isImagesOnly) {
            if ($isClaude) {
                return 'Claude returned image URLs but downloading failed - try the Media tab to upload manually, or rerun with a different model.';
            }
            return 'OpenAI cannot search the web for image URLs - switch the model to one of the Claude options (Haiku 4.5 is fastest) to get manufacturer images automatically, or upload via the Media tab.';
        }
        return 'Re-run on the edit page (AI: Make Production Ready) or try a different model.';
    }

    /**
     * Pull import option flags out of $_POST or a queued job payload with
     * the same defaults applied either way.
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
            'ai_model' => $this->sanitizeImportModel($source['ai_model'] ?? null),
        ];
    }

    /**
     * Compress a ProductImageService::getDebugLog() trail into a single
     * line the operator can read inline in the import errors table.
     * Example: "fetch bing.com=HTTP 200(45KB) | search bing=0 urls 'q1' | fetch ddg=HTTP 0(curl_error) | search ddg=0 urls 'q1' | download cdn.dell.com=non_image_content_type(text/html)"
     */
    private function summarizeImagesDebug(array $entries): string
    {
        $parts = [];
        foreach ($entries as $e) {
            $kind = (string) ($e['kind'] ?? '');
            if ($kind === 'fetch') {
                $host = (string) parse_url((string) ($e['url'] ?? ''), PHP_URL_HOST);
                if ($e['outcome'] === 'ok') {
                    $bytes = (int) ($e['bytes'] ?? 0);
                    $parts[] = "fetch {$host}=HTTP 200 (" . round($bytes / 1024) . 'KB)';
                } else {
                    $http = (int) ($e['http_code'] ?? 0);
                    $err = (string) ($e['error'] ?? '');
                    $parts[] = "fetch {$host}=HTTP {$http}" . ($err ? "({$err})" : '');
                }
            } elseif ($kind === 'search') {
                $engine = (string) ($e['engine'] ?? '');
                $found = (int) ($e['urls_found'] ?? 0);
                $q = (string) ($e['query'] ?? '');
                $parts[] = "search {$engine}={$found} urls '{$q}'";
            } elseif ($kind === 'download') {
                $host = (string) parse_url((string) ($e['url'] ?? ''), PHP_URL_HOST);
                if ($e['outcome'] === 'saved') {
                    $parts[] = "download {$host}=saved (" . (string) ($e['dims'] ?? '?') . ')';
                } else {
                    $reason = (string) ($e['reason'] ?? '?');
                    $extra = '';
                    if (!empty($e['content_type'])) {
                        $extra = '(' . (string) $e['content_type'] . ')';
                    } elseif (!empty($e['http_code'])) {
                        $extra = '(HTTP ' . (string) $e['http_code'] . ')';
                    } elseif (!empty($e['dims'])) {
                        $extra = '(' . (string) $e['dims'] . ')';
                    }
                    $parts[] = "download {$host}={$reason}{$extra}";
                }
            } elseif ($kind === 'fallback_search_start') {
                $needed = (int) ($e['needed'] ?? 0);
                $parts[] = "fallback_start needed={$needed}";
            } elseif ($kind === 'exception') {
                $parts[] = 'exception: ' . substr((string) ($e['reason'] ?? ''), 0, 80);
            }
        }
        $line = implode(' | ', $parts);
        if (strlen($line) > 800) {
            $line = substr($line, 0, 800) . '... [truncated]';
        }
        return $line !== '' ? $line : 'no debug entries';
    }

    /**
     * Append the full trail to storage/logs/ai-image-import.log so a
     * support session can pull the full byte-level history per SKU
     * without scrolling the import errors UI.
     */
    private function logImageDebug(string $sku, int $imagesSaved, array $entries): void
    {
        $line = sprintf(
            "[%s] sku=%s saved=%d entries=%s\n",
            date('Y-m-d H:i:s'),
            $sku,
            $imagesSaved,
            json_encode($entries, JSON_UNESCAPED_SLASHES)
        );
        @error_log($line, 3, STORAGE_PATH . '/logs/ai-image-import.log');
    }

    /**
     * Map of "category name (lowercased)" -> id and "category id (int)" -> id,
     * so a CSV row can refer to a category by either form.
     */
    private function getCategoryMap(\PDO $db): array
    {
        $map = [];
        $rows = $this->q($db, "SELECT id, name FROM categories")->fetchAll() ?: [];
        foreach ($rows as $r) {
            $map[strtolower(trim($r['name']))] = (int) $r['id'];
            $map[(int) $r['id']] = (int) $r['id'];
        }
        return $map;
    }

    private function createCategoryIfNotExists(\PDO $db, string $name, array &$categoryMap): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
        $slug = trim($slug, '-');
        try {
            $this->q($db, 
                "INSERT INTO categories (name, slug, is_active, created_at) VALUES (?, ?, 1, NOW())",
                [$name, $slug]
            );
            $id = (int) $db->lastInsertId();
            $categoryMap[strtolower($name)] = $id;
            $categoryMap[$id] = $id;
            return $id;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function generateSlug(string $name, ?int $excludeId = null): string
    {
        $base = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($name)));
        $base = trim($base, '-');
        if ($base === '') {
            $base = 'product-' . time();
        }
        $db = Database::getInstance();
        $slug = $base;
        $i = 2;
        while (true) {
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1");
            $stmt->execute([$slug, $excludeId ?: 0]);
            if (!$stmt->fetchColumn()) {
                return $slug;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
    }

    private function generateAttributeSlug(string $value): string
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($value)));
        return trim($slug, '-');
    }

    /**
     * Canonicalize brand names so "HP" / "HP Inc." / "Hewlett-Packard" don't
     * end up as three separate brand entries in storefront filters.
     */
    private function normalizeBrand(string $brand): string
    {
        $brand = trim($brand);
        if ($brand === '') {
            return '';
        }
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $brand));
        $canonical = [
            'hp' => 'HP', 'hpinc' => 'HP', 'hewlettpackard' => 'HP', 'hpe' => 'HPE',
            'dell' => 'Dell', 'delltechnologies' => 'Dell',
            'asus' => 'ASUS', 'asustek' => 'ASUS',
            'lenovo' => 'Lenovo',
            'apple' => 'Apple', 'appleinc' => 'Apple',
            'samsung' => 'Samsung', 'samsungelectronics' => 'Samsung',
            'lg' => 'LG', 'lgelectronics' => 'LG',
            'sony' => 'Sony',
            'intel' => 'Intel',
            'amd' => 'AMD', 'advancedmicrodevices' => 'AMD',
            'nvidia' => 'NVIDIA',
            'msi' => 'MSI',
            'gigabyte' => 'Gigabyte', 'asrock' => 'ASRock',
            'corsair' => 'Corsair', 'kingston' => 'Kingston',
            'westerndigital' => 'Western Digital', 'wd' => 'Western Digital',
            'sandisk' => 'SanDisk', 'seagate' => 'Seagate', 'crucial' => 'Crucial',
            'tplink' => 'TP-Link', 'mikrotik' => 'MikroTik', 'cisco' => 'Cisco',
            'logitech' => 'Logitech', 'razer' => 'Razer', 'acer' => 'Acer',
            'benq' => 'BenQ', 'epson' => 'Epson', 'canon' => 'Canon', 'brother' => 'Brother',
            'huawei' => 'Huawei', 'xiaomi' => 'Xiaomi', 'redmi' => 'Xiaomi',
            'pinnacle' => 'Pinnacle',
        ];
        return $canonical[$slug] ?? $brand;
    }

    /**
     * product_images has both `path` (schema.sql) and `image_url` (legacy)
     * referenced in different places. Detect which exists per request.
     */
    private static ?string $productImagesColumn = null;
    private function productImagesUrlColumn(\PDO $db): string
    {
        if (self::$productImagesColumn !== null) {
            return self::$productImagesColumn;
        }
        try {
            $cols = $this->q($db, "SHOW COLUMNS FROM product_images")->fetchAll();
            $names = array_column($cols, 'Field');
            self::$productImagesColumn = in_array('image_url', $names, true) ? 'image_url' : 'path';
        } catch (\Throwable $e) {
            self::$productImagesColumn = 'image_url';
        }
        return self::$productImagesColumn;
    }

    /**
     * Download a product image, validate it's a real image (mime + dimensions),
     * resize to 1500px max edge, save to /uploads/products/.
     */
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
                return;
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);
            $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            $ext = $extMap[$mimeType] ?? null;
            if (!$ext) {
                return;
            }
            $dimensions = @getimagesizefromstring($imageData);
            if ($dimensions === false) {
                return;
            }
            [$w, $h] = $dimensions;
            if ($w < 200 || $h < 200) {
                error_log("Product {$productId}: skipping tiny image {$w}x{$h} from {$imageUrl}");
                return;
            }
            $maxEdge = 1500;
            if (max($w, $h) > $maxEdge && function_exists('imagecreatefromstring')) {
                $imageData = $this->resizeImage($imageData, $w, $h, $maxEdge, $ext) ?? $imageData;
            }
            $uploadDir = PUBLIC_PATH . '/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = $productId . '_' . time() . '_import.' . $ext;
            file_put_contents($uploadDir . $filename, $imageData);
            $urlColumn = $this->productImagesUrlColumn($db);
            $hasImage = (int) $this->q($db, "SELECT COUNT(*) FROM product_images WHERE product_id = ?", [$productId])->fetchColumn();
            $this->q($db, 
                "INSERT INTO product_images (product_id, `{$urlColumn}`, is_primary, sort_order, created_at) VALUES (?, ?, ?, 0, NOW())",
                [$productId, '/uploads/products/' . $filename, $hasImage ? 0 : 1]
            );
        } catch (\Exception $e) {
            // image errors should not break the import
        }
    }

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
            case 'jpg': imagejpeg($dst, null, 85); break;
            case 'png': imagepng($dst, null, 7); break;
            case 'webp':
                if (function_exists('imagewebp')) { imagewebp($dst, null, 85); }
                else { imagedestroy($dst); ob_end_clean(); return null; }
                break;
            case 'gif': imagegif($dst); break;
            default: imagedestroy($dst); ob_end_clean(); return null;
        }
        $out = ob_get_clean();
        imagedestroy($dst);
        return $out !== false ? $out : null;
    }

    /**
     * Save AI-generated specs to product_specifications. Distinct from
     * saveProductSpecifications() which reads from $_POST on the edit form.
     */
    private function saveAiProductSpecifications(\PDO $db, int $productId, array $specs): void
    {
        if (empty($specs)) {
            return;
        }
        try {
            $hasExisting = (int) $this->q($db, "SELECT COUNT(*) FROM product_specifications WHERE product_id = ?", [$productId])->fetchColumn();
            if ($hasExisting > 0) {
                return;
            }
            $sortOrder = 0;
            foreach ($specs as $s) {
                $sn = trim((string) ($s['name'] ?? ''));
                $sv = trim((string) ($s['value'] ?? ''));
                if ($sn === '' || $sv === '') {
                    continue;
                }
                $this->q($db, 
                    "INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [$productId, substr($sn, 0, 100), substr($sv, 0, 500), $sortOrder]
                );
                $sortOrder++;
            }
        } catch (\Throwable $e) {
            error_log("saveAiProductSpecifications: " . $e->getMessage());
        }
    }

    /**
     * Save AI-generated attributes to product_attributes / attributes /
     * attribute_values. Distinct from saveProductAttributes() which reads
     * from $_POST on the edit form. Brand values get normalized.
     */
    private function saveAiProductAttributes(\PDO $db, int $productId, array $attributes, string $category): void
    {
        if (empty($attributes)) {
            return;
        }
        try {
            $hasExisting = (int) $this->q($db, "SELECT COUNT(*) FROM product_attributes WHERE product_id = ?", [$productId])->fetchColumn();
            if ($hasExisting > 0) {
                return;
            }
            foreach ($attributes as $attrName => $attrValue) {
                $attrName = trim((string) $attrName);
                $attrValue = trim((string) $attrValue);
                if ($attrName === '' || $attrValue === '') {
                    continue;
                }
                if (strcasecmp($attrName, 'brand') === 0 || strcasecmp($attrName, 'manufacturer') === 0) {
                    $attrValue = $this->normalizeBrand($attrValue);
                }
                $attrSlug = $this->generateAttributeSlug($attrName);
                $row = $this->q($db, "SELECT id FROM attributes WHERE slug = ?", [$attrSlug])->fetch();
                if (!$row) {
                    $this->q($db, 
                        "INSERT INTO attributes (name, slug, type, is_filterable, is_visible, created_at) VALUES (?, ?, 'select', 1, 1, NOW())",
                        [$attrName, $attrSlug]
                    );
                    $attributeId = (int) $db->lastInsertId();
                } else {
                    $attributeId = (int) $row['id'];
                }
                $valueSlug = $this->generateAttributeSlug($attrValue);
                $vrow = $this->q($db, "SELECT id FROM attribute_values WHERE attribute_id = ? AND slug = ?", [$attributeId, $valueSlug])->fetch();
                if (!$vrow) {
                    $this->q($db, 
                        "INSERT INTO attribute_values (attribute_id, value, slug, created_at) VALUES (?, ?, ?, NOW())",
                        [$attributeId, substr($attrValue, 0, 255), $valueSlug]
                    );
                    $valueId = (int) $db->lastInsertId();
                } else {
                    $valueId = (int) $vrow['id'];
                }
                $this->q($db, 
                    "INSERT INTO product_attributes (product_id, attribute_id, attribute_value_id) VALUES (?, ?, ?)",
                    [$productId, $attributeId, $valueId]
                );
            }
        } catch (\Throwable $e) {
            error_log("saveAiProductAttributes: " . $e->getMessage());
        }
    }

    /**
     * Insert a new product from a CSV row (with AI augmentations already
     * merged in). Uses the same field set as the live form's create() path
     * but driven by row data rather than $_POST.
     */
    private function createProductFromImport(\PDO $db, array $row, array $categoryMap): int
    {
        $name = trim((string) $row['name']);
        $sku = trim((string) $row['sku']);
        $slug = $this->generateSlug($name);

        $categoryId = null;
        if (!empty($row['category'])) {
            $catKey = is_numeric($row['category']) ? (int) $row['category'] : strtolower(trim((string) $row['category']));
            $categoryId = $categoryMap[$catKey] ?? null;
        }

        $vendorId = null;
        if (!empty($row['vendor_id'])) {
            $vendorId = (int) $row['vendor_id'];
        } elseif (!empty($row['vendor'])) {
            $v = trim((string) $row['vendor']);
            if (is_numeric($v)) {
                $vendorId = (int) $v;
            } else {
                $stmt = $this->q($db, "SELECT id FROM vendors WHERE name = ? LIMIT 1", [$v]);
                $found = $stmt ? $stmt->fetch() : null;
                $vendorId = $found ? (int) $found['id'] : null;
            }
        }

        $status = 'active';
        if (isset($row['status'])) {
            if (is_string($row['status']) && !is_numeric($row['status'])) {
                $status = in_array($row['status'], ['active', 'inactive', 'draft', 'out_of_stock'], true) ? $row['status'] : 'draft';
            } else {
                $status = ((int) $row['status']) === 0 ? 'draft' : 'active';
            }
        }

        $this->q($db,
            "INSERT INTO products
             (name, slug, sku, description, short_description, price, compare_price, cost_price,
              vendor_id, stock_quantity, low_stock_threshold, weight, length, width, height,
              meta_title, meta_description, meta_keywords, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $name, $slug, $sku,
                $row['description'] ?? '',
                $row['short_description'] ?? '',
                !empty($row['price']) ? (float) $row['price'] : 0,
                !empty($row['compare_price']) ? (float) $row['compare_price'] : null,
                !empty($row['cost_price']) ? (float) $row['cost_price'] : null,
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
            ]
        );
        $productId = (int) $db->lastInsertId();
        $this->syncImportProductCategory($db, $productId, $categoryId);
        return $productId;
    }

    private function updateProductFromImport(\PDO $db, int $productId, array $row, array $categoryMap): void
    {
        $updates = [];
        $params = [];
        $fieldMap = [
            'name' => 'name', 'description' => 'description', 'short_description' => 'short_description',
            'price' => 'price', 'compare_price' => 'compare_price', 'cost_price' => 'cost_price',
            'stock' => 'stock_quantity', 'weight' => 'weight', 'length' => 'length',
            'width' => 'width', 'height' => 'height',
            'meta_title' => 'meta_title', 'meta_description' => 'meta_description', 'meta_keywords' => 'meta_keywords',
            'status' => 'status',
        ];
        foreach ($fieldMap as $csvField => $dbField) {
            if (isset($row[$csvField]) && $row[$csvField] !== '') {
                $value = $row[$csvField];
                if (in_array($dbField, ['price', 'compare_price', 'cost_price', 'weight', 'length', 'width', 'height'], true)) {
                    $value = (float) $value;
                } elseif ($dbField === 'status') {
                    if (is_string($value) && !is_numeric($value)) {
                        $value = in_array($value, ['active', 'inactive', 'draft', 'out_of_stock'], true) ? $value : 'draft';
                    } else {
                        $value = ((int) $value) === 0 ? 'draft' : 'active';
                    }
                } elseif ($dbField === 'stock_quantity') {
                    $value = (int) $value;
                }
                $updates[] = "{$dbField} = ?";
                $params[] = $value;
            }
        }
        $categoryId = null;
        if (!empty($row['category'])) {
            $catKey = is_numeric($row['category']) ? (int) $row['category'] : strtolower(trim((string) $row['category']));
            $categoryId = $categoryMap[$catKey] ?? null;
        }
        $vendorId = null;
        if (!empty($row['vendor_id'])) {
            $vendorId = (int) $row['vendor_id'];
        } elseif (!empty($row['vendor'])) {
            $v = trim((string) $row['vendor']);
            if (is_numeric($v)) {
                $vendorId = (int) $v;
            } else {
                $stmt = $this->q($db, "SELECT id FROM vendors WHERE name = ? LIMIT 1", [$v]);
                $found = $stmt ? $stmt->fetch() : null;
                $vendorId = $found ? (int) $found['id'] : null;
            }
        }
        if ($vendorId) {
            $updates[] = "vendor_id = ?";
            $params[] = $vendorId;
        }
        if (!empty($row['name'])) {
            $slug = $this->generateSlug((string) $row['name'], $productId);
            $updates[] = "slug = ?";
            $params[] = $slug;
        }
        if (!empty($updates)) {
            $updates[] = "updated_at = NOW()";
            $params[] = $productId;
            $this->q($db, "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        }
        $this->syncImportProductCategory($db, $productId, $categoryId);
    }

    /**
     * Categories on products are a M2M relation via `product_categories`,
     * not a column on `products`. When an import row carries a single
     * category, replace any existing rows and mark this one primary.
     */
    private function syncImportProductCategory(\PDO $db, int $productId, ?int $categoryId): void
    {
        if ($categoryId === null) {
            return;
        }
        $this->q($db, "DELETE FROM product_categories WHERE product_id = ?", [$productId]);
        $this->q($db,
            "INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, 1)",
            [$productId, $categoryId]
        );
    }

    /**
     * Record one AI import attempt - kept separate from the row insert so
     * we can audit what the model returned vs. what was saved. Silently
     * no-ops if migration 018 hasn't been applied.
     */
    private function logAiImport(\PDO $db, ?int $productId, string $sku, string $aiService, array $aiResult): void
    {
        try {
            $data = $aiResult['data'] ?? [];
            $usage = $aiResult['usage'] ?? [];
            $method = $aiResult['method'] ?? 'unknown';
            $identified = !empty($data['ai_identified']);
            $confidence = $identified ? 'high' : ($method === 'fallback' ? 'unknown' : 'low');
            $storedData = $data;
            unset($storedData['image_candidates']);
            $this->q($db, 
                "INSERT INTO product_ai_imports
                 (product_id, sku, ai_service, ai_model, ai_method, ai_identified, confidence,
                  ai_response, image_url, input_tokens, output_tokens, cache_read_tokens,
                  estimated_cost_usd, fallback_reason, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $productId, $sku, $aiService,
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
            error_log("logAiImport skipped: " . $e->getMessage());
        }
    }

    private function estimateAiCost(array $usage): float
    {
        if (empty($usage['model'])) {
            return 0.0;
        }
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
     * The core per-row loop used by both the sync import endpoint and the
     * background queue worker. $heartbeat is invoked every 5 rows with the
     * current progress so the queued path can publish status updates.
     */
    private function runImportLoop(\PDO $db, array $data, array $options, ?callable $heartbeat = null): array
    {
        // A row with AI enabled can spend several minutes inside Anthropic
        // (web_search + adaptive thinking + structured output). Don't let
        // PHP's max_execution_time guillotine the worker mid-row.
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        @ignore_user_abort(true);

        $updateExisting = (bool) $options['update_existing'];
        $createNew = (bool) $options['create_new'];
        $skipErrors = (bool) $options['skip_errors'];
        $aiGenerate = (bool) $options['ai_generate'];
        $marginPercent = (float) $options['margin_percent'];
        $vatRate = (float) $options['vat_rate'];
        $defaultVendorId = $options['default_vendor_id'];
        $defaultCategoryId = $options['default_category_id'];
        $aiModel = $options['ai_model'] ?? null;

        $created = 0; $updated = 0; $failed = 0;
        $errors = [];
        $categoryMap = $this->getCategoryMap($db);
        $aiService = null; $aiServiceName = 'none';

        foreach ($data as $index => $row) {
            $rowNum = $index + 1;
            $aiResult = null;
            $aiData = null;
            try {
                $sku = trim((string) ($row['sku'] ?? ''));
                if ($sku === '') {
                    if ($skipErrors) {
                        $errors[] = "Row {$rowNum}: SKU is required";
                        $failed++;
                        continue;
                    }
                    throw new \Exception("Row {$rowNum}: SKU is required");
                }

                $existing = $this->q($db, "SELECT id FROM products WHERE sku = ?", [$sku])->fetch();

                if ($existing) {
                    if (!$updateExisting) {
                        continue;
                    }
                    if ($aiGenerate) {
                        if ($aiService === null) {
                            [$aiService, $aiServiceName] = $this->resolveAiService($aiModel);
                        }
                        $existingProduct = $this->q($db, "SELECT * FROM products WHERE id = ?", [$existing['id']])->fetch();
                        $aiResult = $aiService->generateCompleteProduct($sku, trim($row['short_description'] ?? $existingProduct['short_description'] ?? ''), [
                            'brand' => $row['brand'] ?? '',
                            'category' => $row['category'] ?? '',
                            'price' => $row['price'] ?? $existingProduct['price'] ?? 0,
                            'existingName' => trim($row['name'] ?? $existingProduct['name'] ?? ''),
                            'existingDescription' => $row['description'] ?? $existingProduct['description'] ?? '',
                            // Intentionally not passing bulk_import=true.
                            // That flag skips fetchManufacturerData() inside
                            // OpenAIService, which is what supplies the real
                            // product context (DuckDuckGo search + product
                            // page scrape). Without it the AI has only the
                            // SKU + supplier line to work with and returns
                            // empty description/specs/attributes. That's
                            // what made every gpt-4o-mini / gpt-5-mini row
                            // come back with 20% of the content the
                            // per-product button produces (the button never
                            // passed bulk_import=true). +10-30s/row cost
                            // - use the queued import for batches >5 rows.
                        ]);
                        if (!empty($aiResult['success']) && !empty($aiResult['data'])) {
                            $aiData = $aiResult['data'];
                            if (!empty($aiData['name']) && $aiData['name'] !== $sku && empty($row['name'])) {
                                $row['name'] = $aiData['name'];
                            }
                            foreach (['description', 'short_description', 'meta_title', 'meta_description', 'meta_keywords'] as $f) {
                                if (empty($existingProduct[$f]) && !empty($aiData[$f])) {
                                    $row[$f] = $f === 'meta_title' || $f === 'meta_keywords' ? substr($aiData[$f], 0, 255) : $aiData[$f];
                                }
                            }
                            foreach (['weight', 'length', 'width', 'height'] as $f) {
                                if (!empty($aiData[$f]) && empty($existingProduct[$f])) {
                                    $row[$f] = (float) $aiData[$f];
                                }
                            }
                            if (!empty($aiData['suggested_category']) && empty($row['category'])) {
                                $hasCategory = (int) $this->q($db, "SELECT COUNT(*) FROM product_categories WHERE product_id = ?", [$existing['id']])->fetchColumn();
                                if ($hasCategory === 0) {
                                    $row['category'] = $aiData['suggested_category'];
                                }
                            }
                        }
                    }
                    $this->updateProductFromImport($db, (int) $existing['id'], $row, $categoryMap);
                    if ($aiData) {
                        // Single comprehensive write path: specs, attributes,
                        // brand attribute, category sync, AND up to 4 images
                        // from aiData.image_url + image_candidates (Claude's
                        // web_search returns the manufacturer URL). force=false
                        // so we don't clobber fields already set above.
                        $applyResult = null;
                        try {
                            $applyResult = ProductService::getInstance()->applyAiDataToProduct(
                                (int) $existing['id'],
                                $aiData,
                                ['force' => false, 'generate_images' => true]
                            );
                        } catch (\Throwable $applyErr) {
                            $errors[] = "Row {$rowNum}: AI data apply failed for {$sku}: " . $applyErr->getMessage();
                        }
                        $imagesSaved = (int) ($applyResult['images_generated'] ?? 0);
                        $missing = $this->describeMissingAiFields($aiData, $imagesSaved);
                        if ($missing !== '') {
                            $errors[] = "Row {$rowNum}: AI returned partial data for {$sku} - missing: {$missing}. " . $this->describeRemediation($missing, $aiServiceName);
                        }
                        $imagesDebug = $applyResult['images_debug'] ?? [];
                        if (!empty($imagesDebug)) {
                            $this->logImageDebug($sku, $imagesSaved, $imagesDebug);
                            if ($imagesSaved === 0) {
                                $errors[] = "Row {$rowNum}: image trail for {$sku}: " . $this->summarizeImagesDebug($imagesDebug);
                            }
                        }
                    }
                    if ($aiGenerate && $aiResult !== null) {
                        $this->logAiImport($db, (int) $existing['id'], $sku, $aiServiceName, $aiResult);
                    }
                    $updated++;
                } else {
                    if (!$createNew) {
                        continue;
                    }
                    $name = trim((string) ($row['name'] ?? ''));
                    $shortDesc = trim((string) ($row['short_description'] ?? ''));

                    if ($aiGenerate) {
                        if (empty($row['vendor']) && $defaultVendorId) {
                            $row['vendor_id'] = $defaultVendorId;
                        }
                        if (empty($row['category']) && $defaultCategoryId) {
                            $row['category'] = (string) $defaultCategoryId;
                        }
                    }
                    if ($aiGenerate && empty($row['price']) && !empty($row['cost_price'])) {
                        $cost = (float) $row['cost_price'];
                        $row['price'] = round($cost * (1 + $marginPercent / 100) * (1 + $vatRate / 100), 2);
                    }

                    $aiIdentified = false;
                    if ($aiGenerate) {
                        if ($aiService === null) {
                            [$aiService, $aiServiceName] = $this->resolveAiService($aiModel);
                        }
                        $aiResult = $aiService->generateCompleteProduct($sku, $shortDesc, [
                            'brand' => $row['brand'] ?? '',
                            'category' => $row['category'] ?? '',
                            'price' => $row['price'] ?? 0,
                            'existingName' => $name,
                            'existingDescription' => $row['description'] ?? '',
                            // bulk_import=true intentionally NOT passed - see
                            // the matching block above for the rationale.
                        ]);
                        if (!empty($aiResult['success']) && !empty($aiResult['data'])) {
                            $aiData = $aiResult['data'];
                            if (!empty($aiData['name']) && $aiData['name'] !== $sku) {
                                $name = $aiData['name'];
                                $aiIdentified = true;
                            }
                            foreach (['description', 'short_description', 'meta_title', 'meta_description', 'meta_keywords'] as $f) {
                                if (!empty($aiData[$f])) {
                                    $row[$f] = $f === 'meta_title' || $f === 'meta_keywords' ? substr($aiData[$f], 0, 255) : $aiData[$f];
                                }
                            }
                            foreach (['weight', 'length', 'width', 'height'] as $f) {
                                if (!empty($aiData[$f]) && empty($row[$f])) {
                                    $row[$f] = (float) $aiData[$f];
                                }
                            }
                            if (empty($row['category']) && !empty($aiData['suggested_category'])) {
                                $row['category'] = $aiData['suggested_category'];
                                $catKey = strtolower(trim($aiData['suggested_category']));
                                if (!isset($categoryMap[$catKey])) {
                                    $this->createCategoryIfNotExists($db, $aiData['suggested_category'], $categoryMap);
                                }
                            }
                            if (!empty($aiData['is_new'])) {
                                $row['is_new'] = 1;
                            }
                        }
                    }

                    if (empty($name)) {
                        if ($aiGenerate) {
                            $name = $sku;
                            $row['name'] = $sku;
                            $row['status'] = 'draft';
                            $reason = $this->describeAiFailure($aiResult, $aiServiceName);
                            $errors[] = "Row {$rowNum}: AI could not identify SKU {$sku} ({$reason}) - created as draft for manual review";
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

                    // Apply AI data (specs, attributes, brand, up to 4 images
                    // from image_url + image_candidates) regardless of whether
                    // ai_identified is true. OpenAI doesn't return an
                    // ai_identified flag at all - gating on it dropped every
                    // OpenAI row's specs/attributes/images on the floor.
                    if ($aiData && $productId) {
                        $applyResult = null;
                        try {
                            $applyResult = ProductService::getInstance()->applyAiDataToProduct(
                                $productId,
                                $aiData,
                                ['force' => false, 'generate_images' => true]
                            );
                        } catch (\Throwable $applyErr) {
                            $errors[] = "Row {$rowNum}: AI data apply failed for {$sku}: " . $applyErr->getMessage();
                        }
                        $imagesSaved = (int) ($applyResult['images_generated'] ?? 0);
                        $missing = $this->describeMissingAiFields($aiData, $imagesSaved);
                        if ($missing !== '') {
                            $errors[] = "Row {$rowNum}: AI returned partial data for {$sku} - missing: {$missing}. " . $this->describeRemediation($missing, $aiServiceName);
                        }
                        $imagesDebug = $applyResult['images_debug'] ?? [];
                        if (!empty($imagesDebug)) {
                            $this->logImageDebug($sku, $imagesSaved, $imagesDebug);
                            if ($imagesSaved === 0) {
                                $errors[] = "Row {$rowNum}: image trail for {$sku}: " . $this->summarizeImagesDebug($imagesDebug);
                            }
                        }
                    }
                    if ($aiGenerate && $aiResult !== null) {
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

            if ($heartbeat !== null && (($index + 1) % 5 === 0)) {
                $heartbeat([
                    'processed' => $created + $updated + $failed,
                    'created' => $created, 'updated' => $updated, 'failed' => $failed,
                    'errors' => $errors,
                ]);
            }
        }

        if ($heartbeat !== null) {
            $heartbeat([
                'processed' => $created + $updated + $failed,
                'created' => $created, 'updated' => $updated, 'failed' => $failed,
                'errors' => $errors,
            ]);
        }

        return [
            'created' => $created, 'updated' => $updated, 'failed' => $failed,
            'errors' => $errors, 'ai_service' => $aiServiceName,
        ];
    }

    private function writeImportLog(\PDO $db, array $result, array $options): ?int
    {
        try {
            $type = $options['ai_generate'] ? 'ai_import' : 'import';
            $filename = $options['ai_generate'] ? "AI Import ({$result['ai_service']})" : 'CSV Import';
            $this->q($db, 
                "INSERT INTO product_import_logs (type, filename, status, total_products, created_products, updated_products, failed_products, errors, created_at, completed_at)
                 VALUES (?, ?, 'completed', ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $type, $filename,
                    $result['created'] + $result['updated'] + $result['failed'],
                    $result['created'], $result['updated'], $result['failed'],
                    !empty($result['errors']) ? json_encode(array_slice($result['errors'], 0, 100)) : null,
                ]
            );
            return (int) $db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Background-queue entry point. Called by Scheduler when a queued
     * import is picked up. Delegates to runImportLoop with a heartbeat.
     */
    public function processImportJob(array $job, ImportJobService $jobService): array
    {
        @set_time_limit(0);
        $jobId = (int) $job['id'];
        $payload = $job['payload'] ?? [];
        $data = $payload['data'] ?? [];
        $options = $this->extractImportOptions($payload);
        $db = Database::getInstance();
        try {
            $result = $this->runImportLoop($db, $data, $options, function ($progress) use ($jobService, $jobId) {
                $jobService->heartbeat($jobId, $progress);
            });
            $logId = $this->writeImportLog($db, $result, $options);
            $jobService->complete($jobId, [
                'processed' => $result['created'] + $result['updated'] + $result['failed'],
                'created' => $result['created'], 'updated' => $result['updated'], 'failed' => $result['failed'],
                'errors' => $result['errors'],
            ], $logId);
            return $result;
        } catch (\Throwable $e) {
            $jobService->fail($jobId, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Queue a bulk import to run in the background. Returns job_id so the
     * client can poll importJobStatus().
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
     * Dry-run: run AI on a single row and return the parsed result without
     * saving anything. Lets the user verify the AI output for one product
     * before kicking off a full bulk import.
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
            $aiModel = $this->sanitizeImportModel($_POST['ai_model'] ?? null);
            [$aiService, $aiServiceName] = $this->resolveAiService($aiModel);
            $aiResult = $aiService->generateCompleteProduct($sku, trim((string) ($row['short_description'] ?? '')), [
                'brand' => $row['brand'] ?? '',
                'category' => $row['category'] ?? '',
                'price' => $row['price'] ?? 0,
                'existingName' => $row['name'] ?? '',
                // bulk_import=true intentionally NOT passed - the dry-run
                // exists to preview what a real import row will produce,
                // and the real import path no longer sets it either.
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
     * Detail page for one import log entry. Shows the summary plus every
     * AI call from product_ai_imports.
     */
    public function importHistoryDetail(int $id): void
    {
        $db = Database::getInstance();
        $log = $this->q($db, "SELECT * FROM product_import_logs WHERE id = ? LIMIT 1", [$id])->fetch();
        if (!$log) {
            http_response_code(404);
            echo 'Import log not found';
            exit;
        }
        $aiRows = [];
        try {
            $aiRows = $this->q($db, 
                "SELECT pai.*, p.name AS product_name, p.status AS product_status
                 FROM product_ai_imports pai
                 LEFT JOIN products p ON p.id = pai.product_id
                 WHERE pai.created_at >= ? AND pai.created_at <= COALESCE(?, NOW())
                 ORDER BY pai.created_at ASC",
                [$log['created_at'], $log['completed_at'] ?? null]
            )->fetchAll() ?: [];
        } catch (\Throwable $e) {
            // product_ai_imports table not yet migrated
        }
        if (!empty($log['errors'])) {
            $log['errors'] = json_decode($log['errors'], true) ?: [];
        } else {
            $log['errors'] = [];
        }
        $this->layout('admin');
        $this->view('pages/products/import-history-detail', [
            'page_title' => 'Import #' . $log['id'],
            'active_page' => 'products',
            'log' => $log,
            'aiRows' => $aiRows,
        ]);
    }

    /**
     * Download a CSV / JSON template. Minimal AI-mode example first (just
     * sku + cost + vendor + category), then a fully-populated row for
     * manual imports.
     */
    public function importTemplate(): void
    {
        $format = $_GET['format'] ?? 'csv';
        if ($format === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="product_import_template.json"');
            echo json_encode([
                '_notes' => [
                    'ai_mode' => 'In AI mode only sku, cost_price, vendor and category are required - the rest is AI-generated.',
                    'price_calculation' => 'AI mode: sell price = cost_price * (1 + margin/100) * (1 + vat/100). Set margin and VAT on the import screen.',
                    'unknown_sku' => 'If AI cannot identify a SKU, the product is still created as status=draft for manual review.',
                ],
                'products' => [
                    ['sku' => 'BX8071514600K', 'cost_price' => 4200.00, 'vendor' => 'Pinnacle', 'category' => 'CPUs', 'stock' => 20],
                    [
                        'sku' => 'SKU002', 'name' => 'Cotton T-Shirt', 'price' => 299.99, 'compare_price' => 349.99,
                        'cost_price' => 150.00, 'vendor' => 'Acme Supplies', 'stock' => 25, 'category' => 'Clothing',
                        'description' => 'Premium cotton t-shirt.', 'short_description' => 'Soft, breathable cotton.',
                        'weight' => 0.3, 'status' => 'active', 'featured' => 1, 'image_url' => null,
                    ],
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="product_import_template.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, [
            'sku', 'cost_price', 'vendor', 'category',
            'name', 'price', 'compare_price', 'stock',
            'description', 'short_description', 'weight', 'status', 'featured', 'image_url',
        ]);
        fputcsv($out, ['BX8071514600K', '4200.00', 'Pinnacle', 'CPUs', '', '', '', '20', '', '', '', '', '', '']);
        fputcsv($out, ['SKU002', '150.00', 'Acme Supplies', 'Clothing', 'Cotton T-Shirt', '299.99', '349.99', '25', 'Premium cotton t-shirt.', 'Soft, breathable cotton.', '0.3', 'active', '1', '']);
        fputcsv($out, ['UNKNOWN-123', '500.00', 'Pinnacle', 'Accessories', '', '', '', '10', '', '', '', '', '', '']);
        fclose($out);
        exit;
    }

    /**
     * Export rows that failed in a previous import as a CSV. User can fix
     * and re-upload them.
     */
    public function exportFailedRows(int $id): void
    {
        $db = Database::getInstance();
        $log = $this->q($db, "SELECT * FROM product_import_logs WHERE id = ? LIMIT 1", [$id])->fetch();
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
}
