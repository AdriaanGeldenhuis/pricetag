<?php
/**
 * Search Engine
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Product search with typo tolerance, synonyms, and filtering capabilities.
 */

declare(strict_types=1);

namespace App\Search;

use App\Core\Database;
use App\Services\Cache;
use PDO;

class SearchEngine
{
    private PDO $db;
    private array $synonyms = [];
    private array $filters = [];
    private string $query = '';
    private int $limit = 24;
    private int $offset = 0;
    private string $sortBy = 'relevance';
    private string $sortOrder = 'DESC';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadSynonyms();
    }

    /**
     * Load search synonyms from database
     */
    private function loadSynonyms(): void
    {
        $cached = cacheGet('search_synonyms');
        if ($cached !== null) {
            $this->synonyms = $cached;
            return;
        }

        $stmt = $this->db->query("SELECT term, synonyms FROM search_synonyms");
        while ($row = $stmt->fetch()) {
            $this->synonyms[strtolower($row['term'])] = array_map('trim', explode(',', $row['synonyms']));
        }

        cachePut('search_synonyms', $this->synonyms, 3600);
    }

    /**
     * Set search query
     */
    public function query(string $query): self
    {
        $this->query = trim($query);
        return $this;
    }

    /**
     * Add filter
     */
    public function filter(string $type, mixed $value): self
    {
        $this->filters[$type] = $value;
        return $this;
    }

    /**
     * Set category filter
     */
    public function category(int|array $categoryIds): self
    {
        $this->filters['category'] = is_array($categoryIds) ? $categoryIds : [$categoryIds];
        return $this;
    }

    /**
     * Set price range filter
     */
    public function priceRange(float $min, float $max): self
    {
        $this->filters['price_min'] = $min;
        $this->filters['price_max'] = $max;
        return $this;
    }

    /**
     * Set availability filter
     */
    public function inStock(bool $inStock = true): self
    {
        $this->filters['in_stock'] = $inStock;
        return $this;
    }

    /**
     * Set rating filter
     */
    public function minRating(float $rating): self
    {
        $this->filters['min_rating'] = $rating;
        return $this;
    }

    /**
     * Set sorting
     */
    public function sortBy(string $field, string $order = 'ASC'): self
    {
        $allowedFields = ['relevance', 'price', 'name', 'created_at', 'rating', 'popularity'];
        if (in_array($field, $allowedFields, true)) {
            $this->sortBy = $field;
            $this->sortOrder = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        }
        return $this;
    }

    /**
     * Set pagination
     */
    public function paginate(int $page, int $perPage = 24): self
    {
        $this->limit = min($perPage, 100);
        $this->offset = max(0, ($page - 1) * $this->limit);
        return $this;
    }

    /**
     * Execute search
     */
    public function search(): array
    {
        // Expand query with synonyms
        $searchTerms = $this->expandQuery($this->query);

        // Build search query
        $sql = $this->buildSearchQuery($searchTerms);
        $countSql = $this->buildCountQuery($searchTerms);

        // Get total count
        $countStmt = $this->db->prepare($countSql['sql']);
        $countStmt->execute($countSql['params']);
        $total = (int) $countStmt->fetchColumn();

        // Get results
        $stmt = $this->db->prepare($sql['sql']);
        $stmt->execute($sql['params']);
        $results = $stmt->fetchAll();

        // Log search
        $this->logSearch($this->query, $total);

        return [
            'results' => $results,
            'total' => $total,
            'page' => ($this->offset / $this->limit) + 1,
            'per_page' => $this->limit,
            'total_pages' => (int) ceil($total / $this->limit),
            'query' => $this->query,
            'filters' => $this->filters,
        ];
    }

    /**
     * Get autocomplete suggestions
     */
    public function autocomplete(string $query, int $limit = 8): array
    {
        $query = trim($query);
        if (strlen($query) < 2) {
            return [];
        }

        // Get product suggestions
        $stmt = $this->db->prepare("
            SELECT DISTINCT name as suggestion, 'product' as type
            FROM products
            WHERE status = 'active' AND name LIKE ?
            LIMIT ?
        ");
        $stmt->execute(['%' . $query . '%', $limit]);
        $products = $stmt->fetchAll();

        // Get category suggestions
        $stmt = $this->db->prepare("
            SELECT DISTINCT name as suggestion, 'category' as type
            FROM categories
            WHERE status = 'active' AND name LIKE ?
            LIMIT ?
        ");
        $stmt->execute(['%' . $query . '%', 3]);
        $categories = $stmt->fetchAll();

        // Get recent search suggestions
        $stmt = $this->db->prepare("
            SELECT DISTINCT term as suggestion, 'recent' as type
            FROM search_terms
            WHERE term LIKE ? AND search_count > 5
            ORDER BY search_count DESC
            LIMIT ?
        ");
        $stmt->execute([$query . '%', 3]);
        $recent = $stmt->fetchAll();

        return [
            'products' => $products,
            'categories' => $categories,
            'recent' => $recent,
        ];
    }

    /**
     * Search by SKU or vendor code
     */
    public function searchBySku(string $sku): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM products p
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE p.status = 'active' AND (p.sku = ? OR p.vendor_sku = ?)
            LIMIT 1
        ");
        $stmt->execute([$sku, $sku]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Expand query with synonyms and typo tolerance
     */
    private function expandQuery(string $query): array
    {
        $words = preg_split('/\s+/', strtolower($query));
        $expanded = $words;

        foreach ($words as $word) {
            // Add synonyms
            if (isset($this->synonyms[$word])) {
                $expanded = array_merge($expanded, $this->synonyms[$word]);
            }

            // Add typo variations (Levenshtein distance 1)
            $expanded = array_merge($expanded, $this->generateTypoVariations($word));
        }

        return array_unique($expanded);
    }

    /**
     * Generate typo variations for a word
     */
    private function generateTypoVariations(string $word): array
    {
        if (strlen($word) <= 3) {
            return [];
        }

        $variations = [];

        // Common typos: missing letters, swapped letters
        // This is a simplified version - could use more sophisticated algorithms
        for ($i = 0; $i < strlen($word); $i++) {
            // Character swap with next
            if ($i < strlen($word) - 1) {
                $variation = substr($word, 0, $i) .
                             $word[$i + 1] . $word[$i] .
                             substr($word, $i + 2);
                $variations[] = $variation;
            }
        }

        return $variations;
    }

    /**
     * Build search SQL query
     */
    private function buildSearchQuery(array $searchTerms): array
    {
        $params = [];
        $conditions = ["p.status = 'active'"];

        // Full-text search conditions
        if (!empty($searchTerms)) {
            $searchConditions = [];
            foreach ($searchTerms as $i => $term) {
                $paramName = "term$i";
                $params[$paramName] = '%' . $term . '%';
                $searchConditions[] = "(p.name LIKE :$paramName OR p.description LIKE :$paramName OR p.sku LIKE :$paramName)";
            }
            $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
        }

        // Apply filters
        $filterConditions = $this->buildFilterConditions($params);
        $conditions = array_merge($conditions, $filterConditions);

        // Build ORDER BY
        $orderBy = $this->buildOrderBy();

        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM products p
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE " . implode(' AND ', $conditions) . "
            GROUP BY p.id
            ORDER BY $orderBy
            LIMIT {$this->limit} OFFSET {$this->offset}
        ";

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Build count query
     */
    private function buildCountQuery(array $searchTerms): array
    {
        $params = [];
        $conditions = ["p.status = 'active'"];

        if (!empty($searchTerms)) {
            $searchConditions = [];
            foreach ($searchTerms as $i => $term) {
                $paramName = "term$i";
                $params[$paramName] = '%' . $term . '%';
                $searchConditions[] = "(p.name LIKE :$paramName OR p.description LIKE :$paramName OR p.sku LIKE :$paramName)";
            }
            $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
        }

        $filterConditions = $this->buildFilterConditions($params);
        $conditions = array_merge($conditions, $filterConditions);

        $sql = "
            SELECT COUNT(DISTINCT p.id)
            FROM products p
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            WHERE " . implode(' AND ', $conditions);

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Build filter conditions
     */
    private function buildFilterConditions(array &$params): array
    {
        $conditions = [];

        // Category filter
        if (!empty($this->filters['category'])) {
            $categoryIds = $this->filters['category'];
            $placeholders = [];
            foreach ($categoryIds as $i => $id) {
                $paramName = "cat$i";
                $params[$paramName] = $id;
                $placeholders[] = ":$paramName";
            }
            $conditions[] = 'pc.category_id IN (' . implode(', ', $placeholders) . ')';
        }

        // Price range
        if (isset($this->filters['price_min'])) {
            $params['price_min'] = $this->filters['price_min'];
            $conditions[] = 'COALESCE(p.sale_price, p.price) >= :price_min';
        }
        if (isset($this->filters['price_max'])) {
            $params['price_max'] = $this->filters['price_max'];
            $conditions[] = 'COALESCE(p.sale_price, p.price) <= :price_max';
        }

        // Stock availability
        if (!empty($this->filters['in_stock'])) {
            $conditions[] = '(p.track_stock = 0 OR p.stock_quantity > 0)';
        }

        // Rating
        if (isset($this->filters['min_rating'])) {
            $params['min_rating'] = $this->filters['min_rating'];
            $conditions[] = 'p.average_rating >= :min_rating';
        }

        return $conditions;
    }

    /**
     * Build ORDER BY clause
     */
    private function buildOrderBy(): string
    {
        switch ($this->sortBy) {
            case 'price':
                return "COALESCE(p.sale_price, p.price) {$this->sortOrder}";
            case 'name':
                return "p.name {$this->sortOrder}";
            case 'created_at':
                return "p.created_at {$this->sortOrder}";
            case 'rating':
                return "p.average_rating {$this->sortOrder}";
            case 'popularity':
                return "p.view_count {$this->sortOrder}";
            case 'relevance':
            default:
                return "p.is_featured DESC, p.created_at DESC";
        }
    }

    /**
     * Log search term
     */
    private function logSearch(string $term, int $resultCount): void
    {
        if (empty($term)) {
            return;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO search_terms (term, search_count, last_searched_at)
                VALUES (?, 1, NOW())
                ON DUPLICATE KEY UPDATE
                    search_count = search_count + 1,
                    last_searched_at = NOW()
            ");
            $stmt->execute([strtolower($term)]);
        } catch (\Throwable $e) {
            // Silent fail - logging shouldn't break search
        }
    }

    /**
     * Get popular search terms
     */
    public function getPopularSearches(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT term, search_count
            FROM search_terms
            ORDER BY search_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get filter options for search results
     */
    public function getFilterOptions(): array
    {
        return [
            'categories' => $this->getAvailableCategories(),
            'price_range' => $this->getPriceRange(),
            'ratings' => [5, 4, 3, 2, 1],
        ];
    }

    /**
     * Get categories with product counts
     */
    private function getAvailableCategories(): array
    {
        $stmt = $this->db->query("
            SELECT c.id, c.name, c.slug, COUNT(pc.product_id) as product_count
            FROM categories c
            JOIN product_categories pc ON c.id = pc.category_id
            JOIN products p ON pc.product_id = p.id AND p.status = 'active'
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY c.name
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get price range for active products
     */
    private function getPriceRange(): array
    {
        $stmt = $this->db->query("
            SELECT
                MIN(COALESCE(sale_price, price)) as min_price,
                MAX(COALESCE(sale_price, price)) as max_price
            FROM products
            WHERE status = 'active'
        ");
        return $stmt->fetch();
    }
}
