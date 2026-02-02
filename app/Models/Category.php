<?php
/**
 * Category Model
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Category extends Model
{
    protected static string $table = 'categories';

    protected static array $fillable = [
        'parent_id', 'name', 'slug', 'description', 'icon', 'image',
        'meta_title', 'meta_description', 'meta_keywords',
        'sort_order', 'is_featured', 'is_active', 'show_in_menu'
    ];

    /**
     * Find by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->where('is_active', 1)->first();
    }

    /**
     * Get active categories
     */
    public static function active(): array
    {
        return self::where('is_active', 1)->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * Get top-level categories
     */
    public static function topLevel(): array
    {
        return self::where('is_active', 1)->whereNull('parent_id')->orderBy('sort_order')->get();
    }

    /**
     * Get featured categories
     */
    public static function featured(int $limit = 6): array
    {
        return self::where('is_active', 1)
            ->where('is_featured', 1)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Get categories with hierarchy
     */
    public static function getTree(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY parent_id, sort_order, name");
        $categories = $stmt->fetchAll();

        return self::buildTree($categories);
    }

    /**
     * Build category tree
     */
    private static function buildTree(array $categories, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] === $parentId) {
                $children = self::buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }

        return $branch;
    }

    /**
     * Get parent category
     */
    public function getParent(): ?self
    {
        if (!$this->parent_id) {
            return null;
        }

        return self::find($this->parent_id);
    }

    /**
     * Get child categories
     */
    public function getChildren(): array
    {
        return self::where('parent_id', $this->id)->where('is_active', 1)->orderBy('sort_order')->get();
    }

    /**
     * Get all ancestor IDs
     */
    public function getAncestorIds(): array
    {
        $ids = [];
        $parent = $this->getParent();

        while ($parent) {
            $ids[] = $parent->id;
            $parent = $parent->getParent();
        }

        return array_reverse($ids);
    }

    /**
     * Get all descendant IDs
     */
    public function getDescendantIds(): array
    {
        $db = Database::getInstance();
        $ids = [$this->id];

        $stmt = $db->prepare("SELECT id FROM categories WHERE parent_id = ? AND is_active = 1");

        $toCheck = [$this->id];
        while ($toCheck) {
            $parentId = array_shift($toCheck);
            $stmt->execute([$parentId]);
            $children = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($children as $childId) {
                $ids[] = $childId;
                $toCheck[] = $childId;
            }
        }

        return $ids;
    }

    /**
     * Get breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $category = $this;

        while ($category) {
            array_unshift($breadcrumbs, [
                'name' => $category->name,
                'slug' => $category->slug,
                'url' => url('/categories/' . $category->slug),
            ]);
            $category = $category->getParent();
        }

        return $breadcrumbs;
    }

    /**
     * Get product count
     */
    public function getProductCount(bool $includeChildren = true): int
    {
        $db = Database::getInstance();

        if ($includeChildren) {
            $categoryIds = $this->getDescendantIds();
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT p.id) FROM products p
                JOIN product_categories pc ON pc.product_id = p.id
                WHERE pc.category_id IN ($placeholders) AND p.status = 'active'
            ");
            $stmt->execute($categoryIds);
        } else {
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT p.id) FROM products p
                JOIN product_categories pc ON pc.product_id = p.id
                WHERE pc.category_id = ? AND p.status = 'active'
            ");
            $stmt->execute([$this->id]);
        }

        return (int) $stmt->fetchColumn();
    }

    /**
     * Get products in category
     */
    public function getProducts(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $categoryIds = $this->getDescendantIds();
        $filters['category_ids'] = $categoryIds;

        return Product::search('', $filters, $page, $perPage);
    }

    /**
     * Get available filters for category
     */
    public function getAvailableFilters(): array
    {
        $db = Database::getInstance();
        $categoryIds = $this->getDescendantIds();
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        // Price range
        $stmt = $db->prepare("
            SELECT MIN(p.price) as min_price, MAX(p.price) as max_price
            FROM products p
            JOIN product_categories pc ON pc.product_id = p.id
            WHERE pc.category_id IN ($placeholders) AND p.status = 'active'
        ");
        $stmt->execute($categoryIds);
        $priceRange = $stmt->fetch();

        // Attributes
        $stmt = $db->prepare("
            SELECT DISTINCT a.id, a.name, a.slug, a.type
            FROM attributes a
            JOIN product_attributes pa ON pa.attribute_id = a.id
            JOIN product_categories pc ON pc.product_id = pa.product_id
            WHERE pc.category_id IN ($placeholders) AND a.is_filterable = 1
            ORDER BY a.sort_order
        ");
        $stmt->execute($categoryIds);
        $attributes = $stmt->fetchAll();

        // Get values for each attribute
        foreach ($attributes as &$attr) {
            $stmt = $db->prepare("
                SELECT DISTINCT av.id, av.value, av.slug, av.color_code
                FROM attribute_values av
                JOIN product_attributes pa ON pa.attribute_value_id = av.id
                JOIN product_categories pc ON pc.product_id = pa.product_id
                WHERE pc.category_id IN ($placeholders) AND av.attribute_id = ?
                ORDER BY av.sort_order
            ");
            $stmt->execute([...$categoryIds, $attr['id']]);
            $attr['values'] = $stmt->fetchAll();
        }

        return [
            'price_range' => $priceRange,
            'attributes' => $attributes,
        ];
    }
}
