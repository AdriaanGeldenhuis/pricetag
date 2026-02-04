<?php
/**
 * Product Model
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Product extends Model
{
    protected static string $table = 'products';

    protected static array $fillable = [
        'sku', 'name', 'slug', 'description', 'short_description', 'type', 'status',
        'price', 'compare_price', 'cost_price',
        'manage_stock', 'stock_quantity', 'low_stock_threshold', 'backorders_allowed', 'lead_time_days',
        'weight', 'length', 'width', 'height',
        'vendor_id', 'vendor_sku',
        'meta_title', 'meta_description', 'meta_keywords',
        'is_featured', 'is_new', 'is_on_sale', 'is_taxable'
    ];

    /**
     * Find by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->where('status', 'active')->first();
    }

    /**
     * Get active products
     */
    public static function active(): array
    {
        return self::where('status', 'active')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Get featured products
     */
    public static function featured(int $limit = 8): array
    {
        return self::where('status', 'active')
            ->where('is_featured', 1)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get new arrivals
     */
    public static function newArrivals(int $limit = 8): array
    {
        return self::where('status', 'active')
            ->where('is_new', 1)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get on sale products
     */
    public static function onSale(int $limit = 8): array
    {
        return self::where('status', 'active')
            ->where('is_on_sale', 1)
            ->whereNotNull('compare_price')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending products (by sales + views)
     */
    public static function trending(int $limit = 8): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM products
            WHERE status = 'active'
            ORDER BY (sold_count * 2 + view_count) DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);

        $products = [];
        while ($data = $stmt->fetch()) {
            $product = new self();
            $product->setOriginal($data);
            $product->exists = true;
            $products[] = $product;
        }

        return $products;
    }

    /**
     * Search products
     */
    public static function search(string $query, array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $db = Database::getInstance();

        $where = ["status = 'active'"];
        $params = [];

        // Full text search
        if (!empty($query)) {
            $where[] = "MATCH(name, description, sku) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $query . '*';
        }

        // Category filter - support both single and multiple category IDs
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['category_ids']), '?'));
            $where[] = "id IN (SELECT product_id FROM product_categories WHERE category_id IN ($placeholders))";
            $params = array_merge($params, $filters['category_ids']);
        } elseif (!empty($filters['category_id'])) {
            $where[] = "id IN (SELECT product_id FROM product_categories WHERE category_id = ?)";
            $params[] = $filters['category_id'];
        }

        // Price filter
        if (!empty($filters['min_price'])) {
            $where[] = "price >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = "price <= ?";
            $params[] = $filters['max_price'];
        }

        // On sale filter
        if (!empty($filters['on_sale'])) {
            $where[] = "is_on_sale = 1";
        }

        // In stock filter
        if (!empty($filters['in_stock'])) {
            $where[] = "(manage_stock = 0 OR stock_quantity > 0)";
        }

        // Build query
        $whereClause = implode(' AND ', $where);

        // Count total
        $countStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE $whereClause");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Sort
        $orderBy = match ($filters['sort'] ?? 'newest') {
            'price_asc' => 'price ASC',
            'price_desc' => 'price DESC',
            'popular' => 'sold_count DESC',
            'rating' => 'rating_average DESC',
            default => 'created_at DESC'
        };

        // Get products
        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("SELECT * FROM products WHERE $whereClause ORDER BY $orderBy LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);

        $products = [];
        while ($data = $stmt->fetch()) {
            $product = new self();
            $product->setOriginal($data);
            $product->exists = true;
            $products[] = $product;
        }

        return [
            'data' => $products,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Get primary image
     */
    public function getPrimaryImage(): ?string
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT path FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
        $stmt->execute([$this->id]);
        $result = $stmt->fetch();

        return $result ? $result['path'] : null;
    }

    /**
     * Get all images
     */
    public function getImages(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, is_primary DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Get categories
     */
    public function getCategories(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT c.* FROM categories c
            JOIN product_categories pc ON pc.category_id = c.id
            WHERE pc.product_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Get primary category
     */
    public function getPrimaryCategory(): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT c.* FROM categories c
            JOIN product_categories pc ON pc.category_id = c.id
            WHERE pc.product_id = ? AND pc.is_primary = 1
            LIMIT 1
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get variants
     */
    public function getVariants(): array
    {
        if ($this->type !== 'variable') {
            return [];
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY price");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Get attributes
     */
    public function getAttributes(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT a.name as attribute_name, a.slug as attribute_slug,
                   av.value, av.slug as value_slug, av.color_code
            FROM product_attributes pa
            JOIN attributes a ON a.id = pa.attribute_id
            LEFT JOIN attribute_values av ON av.id = pa.attribute_value_id
            WHERE pa.product_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Get related products
     */
    public function getRelatedProducts(int $limit = 4): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.* FROM products p
            JOIN product_related pr ON pr.related_id = p.id
            WHERE pr.product_id = ? AND p.status = 'active'
            LIMIT ?
        ");
        $stmt->execute([$this->id, $limit]);

        $products = [];
        while ($data = $stmt->fetch()) {
            $product = new self($data);
            $product->exists = true;
            $products[] = $product;
        }

        return $products;
    }

    /**
     * Get reviews
     */
    public function getReviews(int $limit = 10): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT r.*, u.first_name, u.last_name FROM reviews r
            LEFT JOIN users u ON u.id = r.user_id
            WHERE r.product_id = ? AND r.is_approved = 1
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$this->id, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get specifications
     */
    public function getSpecifications(): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT spec_name, spec_value FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$this->id]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Check if in stock
     */
    public function isInStock(): bool
    {
        if (!$this->manage_stock) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    /**
     * Get stock status text
     */
    public function getStockStatus(): string
    {
        if (!$this->manage_stock) {
            return 'In Stock';
        }

        if ($this->stock_quantity <= 0) {
            return $this->backorders_allowed ? 'Backorder' : 'Out of Stock';
        }

        if ($this->stock_quantity <= $this->low_stock_threshold) {
            return "Only {$this->stock_quantity} left";
        }

        return 'In Stock';
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): ?int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?");
        $stmt->execute([$this->id]);
    }

    /**
     * Get schema.org data
     */
    public function getSchemaData(): array
    {
        return [
            '@type' => 'Product',
            'name' => $this->name,
            'description' => $this->description ?? $this->short_description,
            'sku' => $this->sku,
            'image' => $this->getPrimaryImage() ? url('storage/uploads/' . $this->getPrimaryImage()) : null,
            'offers' => [
                '@type' => 'Offer',
                'url' => url('/products/' . $this->slug),
                'priceCurrency' => 'ZAR',
                'price' => number_format($this->price, 2, '.', ''),
                'availability' => $this->isInStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
            'aggregateRating' => $this->rating_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => $this->rating_average,
                'reviewCount' => $this->rating_count,
            ] : null,
        ];
    }

    /**
     * Get products with filters (for API)
     */
    public function getProducts(array $filters = [], string $sort = 'newest', int $page = 1, int $limit = 20): array
    {
        $db = Database::getInstance();

        $where = ["p.status = 'active'"];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "p.id IN (SELECT product_id FROM product_categories WHERE category_id = ?)";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['is_featured'])) {
            $where[] = "p.is_featured = 1";
        }

        if (!empty($filters['is_on_sale'])) {
            $where[] = "p.is_on_sale = 1";
        }

        if (!empty($filters['is_new'])) {
            $where[] = "p.is_new = 1";
        }

        $whereClause = implode(' AND ', $where);

        $orderBy = match ($sort) {
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'popular' => 'p.sold_count DESC',
            'rating' => 'p.rating_average DESC',
            'name_asc' => 'p.name ASC',
            'name_desc' => 'p.name DESC',
            default => 'p.created_at DESC'
        };

        $offset = ($page - 1) * $limit;
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN product_categories pc ON pc.product_id = p.id AND pc.is_primary = 1
            LEFT JOIN categories c ON c.id = pc.category_id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);

        $products = [];
        while ($data = $stmt->fetch()) {
            $product = new self($data);
            $product->exists = true;
            $product->category_name = $data['category_name'] ?? null;
            $products[] = $product;
        }

        return $products;
    }

    /**
     * Count products with filters (for API pagination)
     */
    public function countProducts(array $filters = []): int
    {
        $db = Database::getInstance();

        $where = ["status = 'active'"];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "id IN (SELECT product_id FROM product_categories WHERE category_id = ?)";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['is_featured'])) {
            $where[] = "is_featured = 1";
        }

        if (!empty($filters['is_on_sale'])) {
            $where[] = "is_on_sale = 1";
        }

        if (!empty($filters['is_new'])) {
            $where[] = "is_new = 1";
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE {$whereClause}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Get product images (for API)
     */
    public function getProductImages(int $productId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, path as image_path, is_primary, sort_order FROM product_images WHERE product_id = ? ORDER BY sort_order, is_primary DESC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get product variants (for API)
     */
    public function getProductVariants(int $productId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, sku, price, stock_quantity, attributes, name FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY price");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
}
