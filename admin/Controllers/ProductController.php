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

        // Ensure unique slug with proper collision handling
        $db = Database::getInstance();
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) {
                break; // Slug is unique
            }
            $counter++;
            $slug = $baseSlug . '-' . $counter;

            // Safety limit to prevent infinite loop
            if ($counter > 1000) {
                $slug = $baseSlug . '-' . uniqid();
                break;
            }
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
            'weight' => !empty($_POST['weight']) ? (float) $_POST['weight'] : null,
            'length' => !empty($_POST['length']) ? (float) $_POST['length'] : null,
            'width' => !empty($_POST['width']) ? (float) $_POST['width'] : null,
            'height' => !empty($_POST['height']) ? (float) $_POST['height'] : null,
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

        // Handle image uploads (multiple)
        if (!empty($_FILES['images']['tmp_name'][0])) {
            $this->handleImageUploads($product->id, $_FILES['images']);
        }

        // Handle variants for variable products
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
        $product->weight = !empty($_POST['weight']) ? (float) $_POST['weight'] : null;
        $product->length = !empty($_POST['length']) ? (float) $_POST['length'] : null;
        $product->width = !empty($_POST['width']) ? (float) $_POST['width'] : null;
        $product->height = !empty($_POST['height']) ? (float) $_POST['height'] : null;
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
     * Bulk action for products
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
        $validActions = ['active', 'draft', 'inactive', 'delete'];
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
            if ($action === 'delete') {
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
        $categories = Category::getTree();

        $this->layout('admin');
        $this->view('pages/products/import', [
            'page_title' => 'Import / Export Products',
            'active_page' => 'products',
            'categories' => $categories,
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
        $stmt = $db->query("SELECT id, name FROM vendors");
        foreach ($stmt->fetchAll() as $v) {
            $vendorLookup[strtolower($v['name'])] = $v['id'];
        }

        // Get category lookup
        $categoryLookup = [];
        $stmt = $db->query("SELECT id, name FROM categories");
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
     * Process AJAX import (for drag & drop column mapping)
     */
    public function importProcess(): void
    {
        // Ensure JSON response
        header('Content-Type: application/json');

        try {
            if (!$this->validateCsrf()) {
                echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
                exit;
            }

            $data = json_decode($_POST['data'] ?? '[]', true);
            if (empty($data)) {
                echo json_encode(['success' => false, 'error' => 'No data provided']);
                exit;
            }

            $updateExisting = ($_POST['update_existing'] ?? '1') === '1';
            $createNew = ($_POST['create_new'] ?? '1') === '1';
            $skipErrors = ($_POST['skip_errors'] ?? '0') === '1';
            $aiGenerate = ($_POST['ai_generate'] ?? '0') === '1';
            $aiFields = json_decode($_POST['ai_fields'] ?? '[]', true);

            $db = Database::getInstance();
        $created = 0;
        $updated = 0;
        $errors = [];

        // Get vendor lookup
        $stmt = $db->query("SELECT id, name FROM vendors");
        $vendorLookup = [];
        while ($row = $stmt->fetch()) {
            $vendorLookup[strtolower($row['name'])] = $row['id'];
        }

        // Get category lookup
        $stmt = $db->query("SELECT id, name FROM categories");
        $categoryLookup = [];
        while ($row = $stmt->fetch()) {
            $categoryLookup[strtolower($row['name'])] = $row['id'];
        }

        foreach ($data as $idx => $row) {
            try {
                $sku = trim($row['sku'] ?? '');
                $name = trim($row['name'] ?? '');
                $price = (float) ($row['price'] ?? 0);

                if (empty($sku)) {
                    if (!$skipErrors) {
                        $errors[] = "Row " . ($idx + 1) . ": Missing SKU";
                    }
                    continue;
                }

                // Check if product exists
                $stmt = $db->prepare("SELECT id FROM products WHERE sku = ?");
                $stmt->execute([$sku]);
                $existingId = $stmt->fetchColumn();

                if ($existingId && !$updateExisting) {
                    continue; // Skip existing
                }

                if (!$existingId && !$createNew) {
                    continue; // Skip new
                }

                // Generate slug
                $slug = slugify($name ?: $sku);
                $baseSlug = $slug;
                $counter = 1;
                while (true) {
                    $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
                    $stmt->execute([$slug, $existingId ?: 0]);
                    if (!$stmt->fetch()) break;
                    $slug = $baseSlug . '-' . (++$counter);
                    if ($counter > 100) {
                        $slug = $baseSlug . '-' . uniqid();
                        break;
                    }
                }

                // Get vendor ID
                $vendorId = null;
                if (!empty($row['vendor'])) {
                    $vendorId = $vendorLookup[strtolower(trim($row['vendor']))] ?? null;
                }

                // Prepare product data
                $productData = [
                    'sku' => $sku,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $row['description'] ?? '',
                    'short_description' => $row['short_description'] ?? '',
                    'price' => $price,
                    'compare_price' => !empty($row['compare_price']) ? (float) $row['compare_price'] : null,
                    'cost_price' => !empty($row['cost_price']) ? (float) $row['cost_price'] : null,
                    'stock_quantity' => (int) ($row['stock'] ?? 0),
                    'weight' => !empty($row['weight']) ? (float) $row['weight'] : null,
                    'vendor_id' => $vendorId,
                    'status' => $row['status'] ?? 'active',
                ];

                // Store brand for attribute handling
                $brandValue = $row['brand'] ?? null;

                // AI Generate if enabled - use OpenAI for proper product identification
                if ($aiGenerate) {
                    $openai = new OpenAIService();
                    $aiResult = $openai->generateFromSku($sku, [
                        'brand' => $brandValue,
                        'category' => $row['category'] ?? '',
                        'price' => $price
                    ]);

                    // Apply AI generated values for empty fields
                    if (empty($productData['name']) || in_array('name', $aiFields)) {
                        $productData['name'] = $aiResult['name'] ?? $sku;
                    }
                    if (empty($productData['description']) || in_array('description', $aiFields)) {
                        $productData['description'] = $aiResult['description'] ?? '';
                    }
                    if (empty($productData['short_description']) || in_array('short_description', $aiFields)) {
                        $productData['short_description'] = $aiResult['short_description'] ?? substr($productData['description'], 0, 150);
                    }
                    // Auto-set brand if detected by AI
                    if (empty($brandValue) && !empty($aiResult['brand'])) {
                        $brandValue = $aiResult['brand'];
                    }

                    // Update slug based on new name
                    $productData['slug'] = slugify($productData['name']);
                }

                if ($existingId) {
                    // Update
                    $setClauses = [];
                    $params = [];
                    foreach ($productData as $col => $val) {
                        if ($col !== 'sku') {
                            $setClauses[] = "`$col` = ?";
                            $params[] = $val;
                        }
                    }
                    $params[] = $existingId;
                    $stmt = $db->prepare("UPDATE products SET " . implode(', ', $setClauses) . " WHERE id = ?");
                    $stmt->execute($params);
                    $updated++;
                } else {
                    // Insert
                    $columns = array_keys($productData);
                    $placeholders = array_fill(0, count($columns), '?');
                    $stmt = $db->prepare("INSERT INTO products (" . implode(', ', array_map(fn($c) => "`$c`", $columns)) . ") VALUES (" . implode(', ', $placeholders) . ")");
                    $stmt->execute(array_values($productData));
                    $productId = $db->lastInsertId();
                    $created++;

                    // Handle category
                    if (!empty($row['category'])) {
                        $catId = $categoryLookup[strtolower(trim($row['category']))] ?? null;
                        if ($catId) {
                            $stmt = $db->prepare("INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, 1)");
                            $stmt->execute([$productId, $catId]);
                        }
                    }

                    // Handle brand as attribute
                    if (!empty($brandValue)) {
                        $this->handleBrandAttribute($db, $productId, trim($brandValue));
                    }
                }

                // Also update brand for existing products
                if ($existingId && !empty($brandValue)) {
                    $this->handleBrandAttribute($db, $existingId, trim($brandValue));
                }
            } catch (\Throwable $e) {
                $errors[] = "Row " . ($idx + 1) . ": " . $e->getMessage();
                if (!$skipErrors) {
                    break;
                }
            }
        }

            echo json_encode([
                'success' => true,
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
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
     * Handle brand attribute - create if not exists and associate with product
     */
    private function handleBrandAttribute($db, int $productId, string $brandValue): void
    {
        // Find or create the "Brand" attribute
        $stmt = $db->prepare("SELECT id FROM attributes WHERE LOWER(name) = 'brand' LIMIT 1");
        $stmt->execute();
        $attributeId = $stmt->fetchColumn();

        if (!$attributeId) {
            // Create Brand attribute
            $stmt = $db->prepare("INSERT INTO attributes (name, slug, type, is_filterable, is_visible) VALUES ('Brand', 'brand', 'select', 1, 1)");
            $stmt->execute();
            $attributeId = $db->lastInsertId();
        }

        // Find or create the attribute value
        $stmt = $db->prepare("SELECT id FROM attribute_values WHERE attribute_id = ? AND LOWER(value) = LOWER(?) LIMIT 1");
        $stmt->execute([$attributeId, $brandValue]);
        $valueId = $stmt->fetchColumn();

        if (!$valueId) {
            // Create the attribute value
            $slug = slugify($brandValue);
            $stmt = $db->prepare("INSERT INTO attribute_values (attribute_id, value, slug) VALUES (?, ?, ?)");
            $stmt->execute([$attributeId, $brandValue, $slug]);
            $valueId = $db->lastInsertId();
        }

        // Remove existing brand association for this product
        $stmt = $db->prepare("DELETE FROM product_attributes WHERE product_id = ? AND attribute_id = ?");
        $stmt->execute([$productId, $attributeId]);

        // Associate attribute value with product
        $stmt = $db->prepare("INSERT INTO product_attributes (product_id, attribute_id, attribute_value_id) VALUES (?, ?, ?)");
        $stmt->execute([$productId, $attributeId, $valueId]);
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
}
