<?php
/**
 * Product Service
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles all product business logic including CRUD operations,
 * bulk operations, quality scoring, and SKU/slug generation.
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Product;
use App\Models\Category;
use PDO;
use PDOException;
use RuntimeException;
use InvalidArgumentException;

class ProductService
{
    private static ?self $instance = null;
    private PDO $db;

    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // =========================================================================
    // CRUD Operations
    // =========================================================================

    /**
     * Create a new product
     *
     * @param array $data Product data
     * @return Product The created product
     * @throws RuntimeException If creation fails
     */
    public function createProduct(array $data): Product
    {
        $validation = $this->validateProductData($data);
        if (!$validation['valid']) {
            throw new InvalidArgumentException(
                'Product validation failed: ' . implode(', ', $validation['errors'])
            );
        }

        try {
            Database::beginTransaction();

            // Generate slug if not provided
            if (empty($data['slug']) && !empty($data['name'])) {
                $data['slug'] = $this->generateUniqueSlug($data['name']);
            }

            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSku('{category}-{number}', $data);
            }

            $product = Product::create($data);

            // Handle categories if provided
            if (!empty($data['categories'])) {
                $this->syncProductCategories($product->id, $data['categories'], $data['primary_category_id'] ?? null);
            }

            // Handle images if provided
            if (!empty($data['images'])) {
                $this->syncProductImages($product->id, $data['images']);
            }

            Database::commit();

            logMessage('info', 'Product created', [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
            ]);

            return $product;

        } catch (PDOException $e) {
            Database::rollBack();
            logMessage('error', 'Failed to create product', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw new RuntimeException('Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing product
     *
     * @param int $id Product ID
     * @param array $data Updated product data
     * @return Product The updated product
     * @throws RuntimeException If update fails or product not found
     */
    public function updateProduct(int $id, array $data): Product
    {
        $product = Product::find($id);
        if (!$product) {
            throw new RuntimeException("Product with ID {$id} not found");
        }

        $validation = $this->validateProductData($data, $id);
        if (!$validation['valid']) {
            throw new InvalidArgumentException(
                'Product validation failed: ' . implode(', ', $validation['errors'])
            );
        }

        $oldSlug = $product->slug;

        try {
            Database::beginTransaction();

            // Update slug if name changed and slug not explicitly provided
            if (!empty($data['name']) && empty($data['slug']) && $data['name'] !== $product->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
            }

            // Fill and save
            $product->fill($data);
            $product->save();

            // Record a 301 redirect from the old slug so existing inbound links
            // (Google, social shares, customer bookmarks) don't 404 after a rename.
            if ($oldSlug && $product->slug && $oldSlug !== $product->slug) {
                $this->recordSlugRedirect($oldSlug, $product->slug);
            }

            // Handle categories if provided
            if (isset($data['categories'])) {
                $this->syncProductCategories($product->id, $data['categories'], $data['primary_category_id'] ?? null);
            }

            // Handle images if provided
            if (isset($data['images'])) {
                $this->syncProductImages($product->id, $data['images']);
            }

            Database::commit();

            logMessage('info', 'Product updated', [
                'product_id' => $product->id,
                'sku' => $product->sku,
            ]);

            return $product;

        } catch (PDOException $e) {
            Database::rollBack();
            logMessage('error', 'Failed to update product', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Delete a product (soft or hard delete)
     *
     * @param int $id Product ID
     * @param bool $softDelete Whether to soft delete (default: true)
     * @return bool True if successful
     * @throws RuntimeException If deletion fails
     */
    public function deleteProduct(int $id, bool $softDelete = true): bool
    {
        $product = Product::find($id);
        if (!$product) {
            throw new RuntimeException("Product with ID {$id} not found");
        }

        try {
            Database::beginTransaction();

            if ($softDelete) {
                // Soft delete - set status to deleted
                $stmt = $this->db->prepare("UPDATE products SET status = 'deleted', deleted_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$id]);
            } else {
                // Hard delete - delete image files from disk before removing DB records
                $imageService = new ProductImageService();
                $imageService->deleteAllProductImages($id);

                // Remove remaining related data (categories, attributes, specs, variants, reviews)
                $this->deleteProductRelatedData($id);
                $result = $product->delete();
            }

            Database::commit();

            logMessage('info', 'Product deleted', [
                'product_id' => $id,
                'soft_delete' => $softDelete,
            ]);

            return $result;

        } catch (PDOException $e) {
            Database::rollBack();
            logMessage('error', 'Failed to delete product', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Failed to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate an existing product
     *
     * @param int $id Product ID to duplicate
     * @return Product The duplicated product
     * @throws RuntimeException If duplication fails
     */
    public function duplicateProduct(int $id): Product
    {
        $original = Product::find($id);
        if (!$original) {
            throw new RuntimeException("Product with ID {$id} not found");
        }

        try {
            Database::beginTransaction();

            // Get original data
            $data = $original->toArray();

            // Remove fields that shouldn't be copied
            unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
            unset($data['view_count'], $data['sold_count'], $data['rating_average'], $data['rating_count']);

            // Generate new unique identifiers
            $data['name'] = $original->name . ' (Copy)';
            $data['slug'] = $this->generateUniqueSlug($data['name']);
            $data['sku'] = $this->generateSku('{original}-COPY-{number}', ['original' => $original->sku]);
            $data['status'] = 'draft';

            // Create the duplicate
            $duplicate = Product::create($data);

            // Duplicate categories
            $stmt = $this->db->prepare("
                INSERT INTO product_categories (product_id, category_id, is_primary)
                SELECT ?, category_id, is_primary
                FROM product_categories
                WHERE product_id = ?
            ");
            $stmt->execute([$duplicate->id, $id]);

            // Duplicate images
            $stmt = $this->db->prepare("
                INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order)
                SELECT ?, path, alt_text, is_primary, sort_order
                FROM product_images
                WHERE product_id = ?
            ");
            $stmt->execute([$duplicate->id, $id]);

            // Duplicate attributes
            $stmt = $this->db->prepare("
                INSERT INTO product_attributes (product_id, attribute_id, attribute_value_id)
                SELECT ?, attribute_id, attribute_value_id
                FROM product_attributes
                WHERE product_id = ?
            ");
            $stmt->execute([$duplicate->id, $id]);

            // Duplicate specifications
            $stmt = $this->db->prepare("
                INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order)
                SELECT ?, spec_name, spec_value, sort_order
                FROM product_specifications
                WHERE product_id = ?
            ");
            $stmt->execute([$duplicate->id, $id]);

            Database::commit();

            logMessage('info', 'Product duplicated', [
                'original_id' => $id,
                'duplicate_id' => $duplicate->id,
            ]);

            return $duplicate;

        } catch (PDOException $e) {
            Database::rollBack();
            logMessage('error', 'Failed to duplicate product', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Failed to duplicate product: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted product
     *
     * @param int $id Product ID
     * @return bool True if successful
     * @throws RuntimeException If restoration fails
     */
    public function restoreProduct(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE products
                SET status = 'draft', deleted_at = NULL, updated_at = NOW()
                WHERE id = ? AND status = 'deleted'
            ");
            $result = $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException("Product with ID {$id} not found or not deleted");
            }

            logMessage('info', 'Product restored', ['product_id' => $id]);

            return $result;

        } catch (PDOException $e) {
            logMessage('error', 'Failed to restore product', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Failed to restore product: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Bulk Operations
    // =========================================================================

    /**
     * Bulk update product status
     *
     * @param array $ids Product IDs
     * @param string $status New status
     * @return int Number of affected rows
     * @throws InvalidArgumentException If status is invalid
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        $validStatuses = ['draft', 'active', 'inactive', 'deleted'];
        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException(
                "Invalid status '{$status}'. Valid statuses: " . implode(', ', $validStatuses)
            );
        }

        if (empty($ids)) {
            return 0;
        }

        try {
            Database::beginTransaction();

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$status], $ids);

            $sql = "UPDATE products SET status = ?, updated_at = NOW() WHERE id IN ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $affected = $stmt->rowCount();

            Database::commit();

            logMessage('info', 'Bulk status update', [
                'status' => $status,
                'product_count' => $affected,
            ]);

            return $affected;

        } catch (PDOException $e) {
            Database::rollBack();
            logMessage('error', 'Bulk status update failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Bulk status update failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update product prices
     *
     * @param array $ids Product IDs
     * @param float $price Price value
     * @param string $type Operation type: 'set', 'increase', 'decrease', 'percent'
     * @return int Number of affected rows
     * @throws InvalidArgumentException If type is invalid
     */
    public function bulkUpdatePrice(array $ids, float $price, string $type = 'set'): int
    {
        $validTypes = ['set', 'increase', 'decrease', 'percent'];
        if (!in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException(
                "Invalid price update type '{$type}'. Valid types: " . implode(', ', $validTypes)
            );
        }

        if (empty($ids)) {
            return 0;
        }

        try {
            Database::beginTransaction();

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $priceExpression = match ($type) {
                'set' => '?',
                'increase' => 'price + ?',
                'decrease' => 'GREATEST(0, price - ?)',
                'percent' => 'price * (1 + ? / 100)',
            };

            $sql = "UPDATE products SET price = {$priceExpression}, updated_at = NOW() WHERE id IN ({$placeholders})";
            $params = array_merge([$price], $ids);

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $affected = $stmt->rowCount();

            Database::commit();

            logMessage('info', 'Bulk price update', [
                'type' => $type,
                'value' => $price,
                'product_count' => $affected,
            ]);

            return $affected;

        } catch (PDOException $e) {
            Database::rollBack();
            logMessage('error', 'Bulk price update failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Bulk price update failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete products
     *
     * @param array $ids Product IDs
     * @param bool $softDelete Whether to soft delete (default: true)
     * @return int Number of affected rows
     */
    public function bulkDelete(array $ids, bool $softDelete = true): int
    {
        if (empty($ids)) {
            return 0;
        }

        try {
            Database::beginTransaction();

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            if ($softDelete) {
                $sql = "UPDATE products SET status = 'deleted', deleted_at = NOW(), updated_at = NOW() WHERE id IN ({$placeholders})";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($ids);
            } else {
                // Delete image files from disk + related data for each product
                $imageService = new ProductImageService();
                foreach ($ids as $id) {
                    $imageService->deleteAllProductImages($id);
                    $this->deleteProductRelatedData($id);
                }

                $sql = "DELETE FROM products WHERE id IN ({$placeholders})";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($ids);
            }

            $affected = $stmt->rowCount();

            Database::commit();

            logMessage('info', 'Bulk delete', [
                'soft_delete' => $softDelete,
                'product_count' => $affected,
            ]);

            return $affected;

        } catch (PDOException $e) {
            Database::rollBack();
            logMessage('error', 'Bulk delete failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Bulk delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign category to products
     *
     * @param array $ids Product IDs
     * @param int $categoryId Category ID to assign
     * @return int Number of affected products
     * @throws RuntimeException If category doesn't exist
     */
    public function bulkAssignCategory(array $ids, int $categoryId): int
    {
        if (empty($ids)) {
            return 0;
        }

        // Verify category exists
        $category = Category::find($categoryId);
        if (!$category) {
            throw new RuntimeException("Category with ID {$categoryId} not found");
        }

        try {
            Database::beginTransaction();

            $affected = 0;

            foreach ($ids as $productId) {
                // Check if relationship already exists
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM product_categories
                    WHERE product_id = ? AND category_id = ?
                ");
                $stmt->execute([$productId, $categoryId]);

                if ((int)$stmt->fetchColumn() === 0) {
                    // Add the category relationship
                    $stmt = $this->db->prepare("
                        INSERT INTO product_categories (product_id, category_id, is_primary)
                        VALUES (?, ?, 0)
                    ");
                    $stmt->execute([$productId, $categoryId]);
                    $affected++;
                }
            }

            // Update products' updated_at timestamp
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("UPDATE products SET updated_at = NOW() WHERE id IN ({$placeholders})");
            $stmt->execute($ids);

            Database::commit();

            logMessage('info', 'Bulk category assignment', [
                'category_id' => $categoryId,
                'product_count' => $affected,
            ]);

            return $affected;

        } catch (PDOException $e) {
            Database::rollBack();
            logMessage('error', 'Bulk category assignment failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Bulk category assignment failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Quality Score
    // =========================================================================

    /**
     * Calculate product quality score
     *
     * @param int $productId Product ID
     * @return array ['score' => int, 'missing' => array, 'recommendations' => array]
     */
    public function calculateQualityScore(int $productId): array
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new RuntimeException("Product with ID {$productId} not found");
        }

        $score = 0;
        $maxScore = 100;
        $missing = [];
        $recommendations = [];

        // Basic Information (30 points)
        if (!empty($product->name) && strlen($product->name) >= 10) {
            $score += 10;
        } else {
            $missing[] = 'Detailed product name (min 10 characters)';
        }

        if (!empty($product->description) && strlen($product->description) >= 100) {
            $score += 10;
        } elseif (!empty($product->description)) {
            $score += 5;
            $recommendations[] = 'Expand description to at least 100 characters';
        } else {
            $missing[] = 'Product description';
        }

        if (!empty($product->short_description) && strlen($product->short_description) >= 50) {
            $score += 10;
        } elseif (!empty($product->short_description)) {
            $score += 5;
            $recommendations[] = 'Expand short description to at least 50 characters';
        } else {
            $missing[] = 'Short description';
        }

        // Pricing (15 points)
        if (!empty($product->price) && $product->price > 0) {
            $score += 10;
        } else {
            $missing[] = 'Valid price';
        }

        if (!empty($product->compare_price) && $product->compare_price > $product->price) {
            $score += 5;
        } else {
            $recommendations[] = 'Add compare price to show savings';
        }

        // Images (20 points)
        $images = $product->getImages();
        $imageCount = count($images);

        if ($imageCount >= 3) {
            $score += 20;
        } elseif ($imageCount === 2) {
            $score += 15;
            $missing[] = '3rd product image';
        } elseif ($imageCount === 1) {
            $score += 10;
            $missing[] = '2nd product image';
            $missing[] = '3rd product image';
        } else {
            $missing[] = 'Product images (at least 1 required)';
        }

        // SEO (20 points)
        if (!empty($product->meta_title) && strlen($product->meta_title) >= 30) {
            $score += 7;
        } elseif (!empty($product->meta_title)) {
            $score += 3;
            $recommendations[] = 'Expand meta title to at least 30 characters';
        } else {
            $missing[] = 'Meta title';
        }

        if (!empty($product->meta_description) && strlen($product->meta_description) >= 120) {
            $score += 8;
        } elseif (!empty($product->meta_description)) {
            $score += 4;
            $recommendations[] = 'Expand meta description to at least 120 characters';
        } else {
            $missing[] = 'Meta description';
        }

        if (!empty($product->slug) && strlen($product->slug) >= 3) {
            $score += 5;
        } else {
            $missing[] = 'SEO-friendly slug';
        }

        // Categories (10 points)
        $categories = $product->getCategories();
        if (count($categories) >= 1) {
            $score += 10;
        } else {
            $missing[] = 'Product category';
        }

        // Inventory (5 points)
        if ($product->manage_stock) {
            if ($product->stock_quantity > 0) {
                $score += 5;
            } elseif ($product->backorders_allowed) {
                $score += 3;
                $recommendations[] = 'Add stock quantity for better inventory management';
            } else {
                $recommendations[] = 'Product is out of stock';
            }
        } else {
            $score += 5; // Stock not managed is acceptable
        }

        return [
            'score' => min($score, $maxScore),
            'max_score' => $maxScore,
            'grade' => $this->getQualityGrade($score),
            'missing' => $missing,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get quality grade based on score
     */
    private function getQualityGrade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    // =========================================================================
    // SKU Generation
    // =========================================================================

    /**
     * Generate a SKU based on pattern
     *
     * Patterns support:
     * - {brand} - Product brand
     * - {category} - Primary category code
     * - {number} - Sequential number
     * - {random} - Random alphanumeric
     * - {year} - Current year
     * - {original} - Original value (for copies)
     *
     * @param string $pattern SKU pattern (e.g., "{brand}-{category}-{number}")
     * @param array $data Context data
     * @return string Generated SKU
     */
    public function generateSku(string $pattern, array $data = []): string
    {
        $replacements = [];

        // Brand
        if (str_contains($pattern, '{brand}')) {
            $brand = $data['brand'] ?? 'GEN';
            $replacements['{brand}'] = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $brand), 0, 3));
        }

        // Category
        if (str_contains($pattern, '{category}')) {
            $categoryCode = 'CAT';
            if (!empty($data['primary_category_id'])) {
                $category = Category::find($data['primary_category_id']);
                if ($category) {
                    $categoryCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $category->name), 0, 3));
                }
            } elseif (!empty($data['categories'][0])) {
                $category = Category::find($data['categories'][0]);
                if ($category) {
                    $categoryCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $category->name), 0, 3));
                }
            }
            $replacements['{category}'] = $categoryCode;
        }

        // Sequential number
        if (str_contains($pattern, '{number}')) {
            $stmt = $this->db->query("SELECT MAX(id) FROM products");
            $maxId = (int)$stmt->fetchColumn();
            $replacements['{number}'] = str_pad((string)($maxId + 1), 5, '0', STR_PAD_LEFT);
        }

        // Random
        if (str_contains($pattern, '{random}')) {
            $replacements['{random}'] = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        }

        // Year
        if (str_contains($pattern, '{year}')) {
            $replacements['{year}'] = date('Y');
        }

        // Original (for copies)
        if (str_contains($pattern, '{original}')) {
            $replacements['{original}'] = $data['original'] ?? 'ORIG';
        }

        $sku = str_replace(array_keys($replacements), array_values($replacements), $pattern);

        // Ensure uniqueness
        $baseSku = $sku;
        $counter = 1;
        while (!$this->isSkuUnique($sku, $data['exclude_id'] ?? null)) {
            $sku = $baseSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Check if SKU is unique
     *
     * @param string $sku SKU to check
     * @param int|null $excludeId Product ID to exclude from check
     * @return bool True if unique
     */
    public function isSkuUnique(string $sku, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM products WHERE sku = ?";
        $params = [$sku];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn() === 0;
    }

    // =========================================================================
    // Slug Generation
    // =========================================================================

    /**
     * Generate a unique slug from a name
     *
     * @param string $name Product name
     * @param int|null $excludeId Product ID to exclude from uniqueness check
     * @return string Unique slug
     */
    public function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug); // Remove multiple consecutive hyphens
        $slug = trim($slug, '-');

        // Ensure minimum length
        if (strlen($slug) < 3) {
            $slug = 'product-' . $slug;
        }

        // Check uniqueness and append number if needed
        $baseSlug = $slug;
        $counter = 1;

        while (!$this->isSlugUnique($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug is unique
     *
     * @param string $slug Slug to check
     * @param int|null $excludeId Product ID to exclude from check
     * @return bool True if unique
     */
    private function isSlugUnique(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM products WHERE slug = ?";
        $params = [$slug];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn() === 0;
    }

    /**
     * Record a 301 redirect from an old product slug to the new one.
     *
     * Idempotent via the redirects.from_url UNIQUE key: if the same product
     * is renamed twice, the row is updated to point at the latest slug.
     */
    private function recordSlugRedirect(string $oldSlug, string $newSlug): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO redirects (from_url, to_url, status_code, is_active)
             VALUES (?, ?, 301, 1)
             ON DUPLICATE KEY UPDATE to_url = VALUES(to_url), is_active = 1"
        );
        $stmt->execute(["/products/{$oldSlug}", "/products/{$newSlug}"]);
    }

    // =========================================================================
    // Validation
    // =========================================================================

    /**
     * Validate product data
     *
     * @param array $data Product data to validate
     * @param int|null $productId Product ID for update validation
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateProductData(array $data, ?int $productId = null): array
    {
        $errors = [];

        // Name validation
        if (isset($data['name'])) {
            if (empty($data['name'])) {
                $errors['name'] = 'Product name is required';
            } elseif (strlen($data['name']) > 255) {
                $errors['name'] = 'Product name must be less than 255 characters';
            }
        } elseif ($productId === null) {
            // Name is required for new products
            $errors['name'] = 'Product name is required';
        }

        // SKU validation
        if (isset($data['sku'])) {
            if (strlen($data['sku']) > 100) {
                $errors['sku'] = 'SKU must be less than 100 characters';
            } elseif (!empty($data['sku']) && !$this->isSkuUnique($data['sku'], $productId)) {
                $errors['sku'] = 'SKU already exists';
            }
        }

        // Slug validation
        if (isset($data['slug']) && !empty($data['slug'])) {
            if (!preg_match('/^[a-z0-9\-]+$/', $data['slug'])) {
                $errors['slug'] = 'Slug can only contain lowercase letters, numbers, and hyphens';
            } elseif (!$this->isSlugUnique($data['slug'], $productId)) {
                $errors['slug'] = 'Slug already exists';
            }
        }

        // Price validation
        if (isset($data['price'])) {
            if (!is_numeric($data['price'])) {
                $errors['price'] = 'Price must be a number';
            } elseif ((float)$data['price'] < 0) {
                $errors['price'] = 'Price cannot be negative';
            }
        }

        // Compare price validation
        if (isset($data['compare_price']) && !empty($data['compare_price'])) {
            if (!is_numeric($data['compare_price'])) {
                $errors['compare_price'] = 'Compare price must be a number';
            } elseif ((float)$data['compare_price'] < 0) {
                $errors['compare_price'] = 'Compare price cannot be negative';
            }
        }

        // Cost price validation
        if (isset($data['cost_price']) && !empty($data['cost_price'])) {
            if (!is_numeric($data['cost_price'])) {
                $errors['cost_price'] = 'Cost price must be a number';
            } elseif ((float)$data['cost_price'] < 0) {
                $errors['cost_price'] = 'Cost price cannot be negative';
            }
        }

        // Stock quantity validation
        if (isset($data['stock_quantity'])) {
            if (!is_numeric($data['stock_quantity'])) {
                $errors['stock_quantity'] = 'Stock quantity must be a number';
            } elseif ((int)$data['stock_quantity'] < 0) {
                $errors['stock_quantity'] = 'Stock quantity cannot be negative';
            }
        }

        // Status validation
        if (isset($data['status'])) {
            $validStatuses = ['draft', 'active', 'inactive', 'deleted'];
            if (!in_array($data['status'], $validStatuses, true)) {
                $errors['status'] = 'Invalid status. Valid values: ' . implode(', ', $validStatuses);
            }
        }

        // Type validation
        if (isset($data['type'])) {
            $validTypes = ['simple', 'variable', 'grouped', 'virtual', 'downloadable'];
            if (!in_array($data['type'], $validTypes, true)) {
                $errors['type'] = 'Invalid type. Valid values: ' . implode(', ', $validTypes);
            }
        }

        // Weight/dimensions validation
        foreach (['weight', 'length', 'width', 'height'] as $dimension) {
            if (isset($data[$dimension]) && !empty($data[$dimension])) {
                if (!is_numeric($data[$dimension])) {
                    $errors[$dimension] = ucfirst($dimension) . ' must be a number';
                } elseif ((float)$data[$dimension] < 0) {
                    $errors[$dimension] = ucfirst($dimension) . ' cannot be negative';
                }
            }
        }

        // Categories validation
        if (isset($data['categories']) && !empty($data['categories'])) {
            if (!is_array($data['categories'])) {
                $errors['categories'] = 'Categories must be an array';
            } else {
                foreach ($data['categories'] as $categoryId) {
                    if (!Category::find($categoryId)) {
                        $errors['categories'] = "Category with ID {$categoryId} not found";
                        break;
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    // =========================================================================
    // Category Matching
    // =========================================================================

    /**
     * Match a suggested category name to an existing category using fuzzy matching.
     *
     * @param string $suggestedCategory AI-suggested category name
     * @return int|null Matched category ID, or null if no match
     */
    public function matchCategory(string $suggestedCategory): ?int
    {
        if (empty($suggestedCategory)) {
            return null;
        }

        $suggestedLower = strtolower(trim($suggestedCategory));

        // Get all categories
        $stmt = $this->db->query("SELECT id, name FROM categories WHERE is_active = 1");
        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($categories)) {
            return null;
        }

        // 1. Exact match (case-insensitive)
        foreach ($categories as $cat) {
            if (strtolower($cat['name']) === $suggestedLower) {
                return (int) $cat['id'];
            }
        }

        // 2. Contains match - category contains the suggestion or vice versa
        foreach ($categories as $cat) {
            $catLower = strtolower($cat['name']);
            if (str_contains($catLower, $suggestedLower) || str_contains($suggestedLower, $catLower)) {
                return (int) $cat['id'];
            }
        }

        // 3. Keyword match - split into words and match
        $suggestedWords = array_filter(explode(' ', $suggestedLower), fn($w) => strlen($w) > 2);
        $bestMatch = null;
        $bestScore = 0;

        foreach ($categories as $cat) {
            $catLower = strtolower($cat['name']);
            $score = 0;
            foreach ($suggestedWords as $word) {
                if (str_contains($catLower, $word)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = (int) $cat['id'];
            }
        }

        // Return best match if at least one keyword matched
        return $bestScore > 0 ? $bestMatch : null;
    }

    /**
     * Assign a product to a category (if not already assigned)
     *
     * @param int $productId Product ID
     * @param int $categoryId Category ID
     * @param bool $isPrimary Whether this is the primary category
     */
    public function assignCategory(int $productId, int $categoryId, bool $isPrimary = true): void
    {
        // Check if already assigned
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM product_categories WHERE product_id = ? AND category_id = ?");
        $stmt->execute([$productId, $categoryId]);

        if ((int)$stmt->fetchColumn() === 0) {
            $stmt = $this->db->prepare("INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)");
            $stmt->execute([$productId, $categoryId, $isPrimary ? 1 : 0]);
        }
    }

    /**
     * Save product specifications
     *
     * @param int $productId Product ID
     * @param array $specifications Array of ['name' => '', 'value' => ''] pairs
     */
    public function saveSpecifications(int $productId, array $specifications): void
    {
        // Don't overwrite existing specs if we already have them
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM product_specifications WHERE product_id = ?");
        $stmt->execute([$productId]);
        $existingCount = (int)$stmt->fetchColumn();

        if ($existingCount > 0) {
            return; // Keep existing specs
        }

        $stmt = $this->db->prepare("INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($specifications as $idx => $spec) {
            if (!empty($spec['name']) && !empty($spec['value'])) {
                $stmt->execute([$productId, $spec['name'], $spec['value'], $idx]);
            }
        }
    }

    /**
     * Save AI-generated product attributes.
     * Finds or creates attribute definitions and values, then links them to the product.
     * Follows the same pattern as handleBrandAttribute() but for any attribute.
     *
     * @param int $productId Product ID
     * @param array $attributes Associative array of attribute_name => attribute_value (e.g. ['Series' => 'RTX 5090', 'Memory Size' => '32GB'])
     */
    public function saveProductAttributes(int $productId, array $attributes): void
    {
        if (empty($attributes)) {
            return;
        }

        // Don't overwrite existing attributes if we already have them
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM product_attributes WHERE product_id = ?");
        $stmt->execute([$productId]);
        $existingCount = (int) $stmt->fetchColumn();

        if ($existingCount > 0) {
            return; // Keep existing attributes
        }

        foreach ($attributes as $attrName => $attrValue) {
            $attrName = trim((string) $attrName);
            $attrValue = trim((string) $attrValue);

            if (empty($attrName) || empty($attrValue)) {
                continue;
            }

            try {
                // Find or create the attribute definition
                $slug = slugify($attrName);
                $stmt = $this->db->prepare("SELECT id FROM attributes WHERE slug = ? LIMIT 1");
                $stmt->execute([$slug]);
                $attributeId = $stmt->fetchColumn();

                if (!$attributeId) {
                    $stmt = $this->db->prepare(
                        "INSERT INTO attributes (name, slug, type, is_filterable, is_visible) VALUES (?, ?, 'select', 1, 1)"
                    );
                    $stmt->execute([$attrName, $slug]);
                    $attributeId = (int) $this->db->lastInsertId();
                }

                // Find or create the attribute value
                $valueSlug = slugify($attrValue);
                $stmt = $this->db->prepare(
                    "SELECT id FROM attribute_values WHERE attribute_id = ? AND LOWER(value) = LOWER(?) LIMIT 1"
                );
                $stmt->execute([$attributeId, $attrValue]);
                $valueId = $stmt->fetchColumn();

                if (!$valueId) {
                    $stmt = $this->db->prepare(
                        "INSERT INTO attribute_values (attribute_id, value, slug) VALUES (?, ?, ?)"
                    );
                    $stmt->execute([$attributeId, $attrValue, $valueSlug]);
                    $valueId = (int) $this->db->lastInsertId();
                }

                // Link attribute value to product
                $stmt = $this->db->prepare(
                    "INSERT INTO product_attributes (product_id, attribute_id, attribute_value_id) VALUES (?, ?, ?)"
                );
                $stmt->execute([$productId, $attributeId, $valueId]);

            } catch (\Throwable $e) {
                logMessage('debug', "Could not save attribute '{$attrName}' for product {$productId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Sync product categories
     *
     * @param int $productId Product ID
     * @param array $categoryIds Category IDs
     * @param int|null $primaryCategoryId Primary category ID
     */
    private function syncProductCategories(int $productId, array $categoryIds, ?int $primaryCategoryId = null): void
    {
        // Delete existing categories
        $stmt = $this->db->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Set primary category if not specified
        if ($primaryCategoryId === null && !empty($categoryIds)) {
            $primaryCategoryId = $categoryIds[0];
        }

        // Insert new categories
        $stmt = $this->db->prepare("
            INSERT INTO product_categories (product_id, category_id, is_primary)
            VALUES (?, ?, ?)
        ");

        foreach ($categoryIds as $categoryId) {
            $isPrimary = ($categoryId === $primaryCategoryId) ? 1 : 0;
            $stmt->execute([$productId, $categoryId, $isPrimary]);
        }
    }

    /**
     * Sync product images
     *
     * @param int $productId Product ID
     * @param array $images Image data
     */
    private function syncProductImages(int $productId, array $images): void
    {
        // Delete existing images
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Insert new images
        $stmt = $this->db->prepare("
            INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($images as $index => $image) {
            $stmt->execute([
                $productId,
                $image['path'] ?? $image,
                $image['alt_text'] ?? null,
                ($index === 0) ? 1 : 0,
                $image['sort_order'] ?? $index,
            ]);
        }
    }

    /**
     * Delete all product-related data
     *
     * @param int $productId Product ID
     */
    private function deleteProductRelatedData(int $productId): void
    {
        $tables = [
            'product_categories',
            // product_images handled by ProductImageService::deleteAllProductImages() (deletes files + DB records)
            'product_attributes',
            'product_specifications',
            'product_variants',
            'product_related',
            'reviews',
        ];

        foreach ($tables as $table) {
            try {
                $stmt = $this->db->prepare("DELETE FROM {$table} WHERE product_id = ?");
                $stmt->execute([$productId]);
            } catch (PDOException $e) {
                // Table might not exist, continue
                logMessage('debug', "Could not delete from {$table}", ['error' => $e->getMessage()]);
            }
        }
    }
}
