<?php
/**
 * Search Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
use App\Models\Category;

class SearchController extends Controller
{
    public function index(): void
    {
        $query = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;

        $filters = [
            'sort' => $_GET['sort'] ?? 'relevance',
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'in_stock' => !empty($_GET['in_stock']),
            'on_sale' => !empty($_GET['on_sale']),
            'category_ids' => isset($_GET['categories']) ? array_filter(array_map('intval', (array)$_GET['categories'])) : [],
            'rating' => isset($_GET['rating']) ? (int)$_GET['rating'] : null,
            'attributes' => $_GET['attr'] ?? [],
        ];

        if (empty($query)) {
            $this->redirect('/products');
            return;
        }

        // Log search term
        $this->logSearchTerm($query);

        // Search products
        $result = Product::search($query, $filters, $page, $perPage);

        // Get filter options
        $filterOptions = $this->getFilterOptions($query);

        // Get popular searches for suggestions
        $popularSearches = $this->getPopularSearches(6);

        // Get related categories
        $relatedCategories = $this->getRelatedCategories($query);

        // Handle AJAX requests for "Load More"
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'html' => $this->renderPartial('components/product-grid', ['products' => $result['data']]),
                'hasMore' => $page < $result['last_page'],
                'total' => $result['total'],
            ]);
            return;
        }

        $this->layout('main');
        $this->view('pages/search/results', [
            'meta_title' => "Search: $query | " . config('app.name'),
            'meta_robots' => 'noindex, follow',
            'products' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'query' => $query,
            'popularSearches' => $popularSearches,
            'relatedCategories' => $relatedCategories,
        ]);
    }

    public function suggest(): void
    {
        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            $this->json(['results' => [], 'categories' => [], 'suggestions' => []]);
            return;
        }

        $db = Database::getInstance();

        // Search products
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.compare_price, pi.path as image, c.name as category
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            LEFT JOIN product_categories pc ON pc.product_id = p.id AND pc.is_primary = 1
            LEFT JOIN categories c ON c.id = pc.category_id
            WHERE p.status = 'active'
            AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)
            ORDER BY
                CASE WHEN p.name LIKE ? THEN 0 ELSE 1 END,
                p.sold_count DESC
            LIMIT 6
        ");

        $term = $query . '%';
        $likeTerm = '%' . $query . '%';
        $stmt->execute([$likeTerm, $likeTerm, $likeTerm, $term]);
        $products = $stmt->fetchAll();

        // Search categories
        $stmt = $db->prepare("
            SELECT id, name, slug, image
            FROM categories
            WHERE status = 'active'
            AND name LIKE ?
            ORDER BY name
            LIMIT 4
        ");
        $stmt->execute([$likeTerm]);
        $categories = $stmt->fetchAll();

        // Get search suggestions from popular terms
        $stmt = $db->prepare("
            SELECT term
            FROM search_terms
            WHERE term LIKE ?
            AND results_count > 0
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->execute([$term]);
        $suggestions = array_column($stmt->fetchAll(), 'term');

        $this->json([
            'results' => $products,
            'categories' => $categories,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * API endpoint for AJAX filtering
     */
    public function filter(): void
    {
        $query = trim($_POST['q'] ?? $_GET['q'] ?? '');
        $page = max(1, (int) ($_POST['page'] ?? $_GET['page'] ?? 1));
        $perPage = 12;

        $filters = [
            'sort' => $_POST['sort'] ?? $_GET['sort'] ?? 'relevance',
            'min_price' => $_POST['min_price'] ?? $_GET['min_price'] ?? null,
            'max_price' => $_POST['max_price'] ?? $_GET['max_price'] ?? null,
            'in_stock' => !empty($_POST['in_stock'] ?? $_GET['in_stock']),
            'on_sale' => !empty($_POST['on_sale'] ?? $_GET['on_sale']),
            'category_ids' => isset($_POST['categories']) ? array_filter(array_map('intval', (array)$_POST['categories'])) : [],
            'rating' => isset($_POST['rating']) ? (int)$_POST['rating'] : null,
            'attributes' => $_POST['attr'] ?? $_GET['attr'] ?? [],
        ];

        $result = Product::search($query, $filters, $page, $perPage);

        $this->json([
            'success' => true,
            'products' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'total' => $result['total'],
                'per_page' => $result['per_page'],
            ],
            'hasMore' => $page < $result['last_page'],
        ]);
    }

    /**
     * Get filter options for the search query
     */
    private function getFilterOptions(string $query): array
    {
        $db = Database::getInstance();
        $likeTerm = '%' . $query . '%';

        // Get price range for matching products
        $stmt = $db->prepare("
            SELECT
                MIN(COALESCE(p.compare_price, p.price)) as min_price,
                MAX(p.price) as max_price
            FROM products p
            WHERE p.status = 'active'
            AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)
        ");
        $stmt->execute([$likeTerm, $likeTerm, $likeTerm]);
        $priceRange = $stmt->fetch();

        // Get categories with product counts
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.slug, COUNT(DISTINCT p.id) as product_count
            FROM categories c
            JOIN product_categories pc ON pc.category_id = c.id
            JOIN products p ON p.id = pc.product_id AND p.status = 'active'
            WHERE c.status = 'active'
            AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)
            GROUP BY c.id
            ORDER BY product_count DESC
            LIMIT 15
        ");
        $stmt->execute([$likeTerm, $likeTerm, $likeTerm]);
        $categories = $stmt->fetchAll();

        // Get available attributes for filtering
        $stmt = $db->prepare("
            SELECT a.id, a.name, a.slug, av.id as value_id, av.value, COUNT(DISTINCT pav.product_id) as count
            FROM attributes a
            JOIN attribute_values av ON av.attribute_id = a.id
            JOIN product_attribute_values pav ON pav.attribute_value_id = av.id
            JOIN products p ON p.id = pav.product_id AND p.status = 'active'
            WHERE a.is_filterable = 1
            AND (p.name LIKE ? OR p.description LIKE ?)
            GROUP BY av.id
            HAVING count > 0
            ORDER BY a.sort_order, av.sort_order
        ");
        $stmt->execute([$likeTerm, $likeTerm]);
        $attributeRows = $stmt->fetchAll();

        // Group attributes
        $attributes = [];
        foreach ($attributeRows as $row) {
            if (!isset($attributes[$row['id']])) {
                $attributes[$row['id']] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'values' => [],
                ];
            }
            $attributes[$row['id']]['values'][] = [
                'id' => $row['value_id'],
                'value' => $row['value'],
                'count' => $row['count'],
            ];
        }

        return [
            'price_range' => [
                'min' => floor($priceRange['min_price'] ?? 0),
                'max' => ceil($priceRange['max_price'] ?? 10000),
            ],
            'categories' => $categories,
            'attributes' => array_values($attributes),
        ];
    }

    /**
     * Get popular search terms
     */
    private function getPopularSearches(int $limit = 6): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT term, count, results_count
            FROM search_terms
            WHERE results_count > 0
            ORDER BY count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get categories related to search query
     */
    private function getRelatedCategories(string $query): array
    {
        $db = Database::getInstance();
        $likeTerm = '%' . $query . '%';

        $stmt = $db->prepare("
            SELECT c.id, c.name, c.slug, c.image, COUNT(DISTINCT p.id) as product_count
            FROM categories c
            JOIN product_categories pc ON pc.category_id = c.id
            JOIN products p ON p.id = pc.product_id AND p.status = 'active'
            WHERE c.status = 'active'
            AND (c.name LIKE ? OR p.name LIKE ?)
            GROUP BY c.id
            ORDER BY product_count DESC
            LIMIT 6
        ");
        $stmt->execute([$likeTerm, $likeTerm]);
        return $stmt->fetchAll();
    }

    private function logSearchTerm(string $term): void
    {
        $db = Database::getInstance();

        try {
            // Check if term exists
            $stmt = $db->prepare("SELECT id FROM search_terms WHERE term = ?");
            $stmt->execute([strtolower($term)]);
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $db->prepare("UPDATE search_terms SET count = count + 1, last_searched_at = NOW() WHERE id = ?");
                $stmt->execute([$existing['id']]);
            } else {
                // Get results count
                $result = Product::search($term, [], 1, 1);

                $stmt = $db->prepare("INSERT INTO search_terms (term, results_count, count, last_searched_at) VALUES (?, ?, 1, NOW())");
                $stmt->execute([strtolower($term), $result['total']]);
            }
        } catch (\Throwable $e) {
            // Silent fail - logging shouldn't break search
        }
    }

    /**
     * Check if request is AJAX
     */
    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Render partial view and return HTML
     */
    private function renderPartial(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        include APP_PATH . '/Views/' . $view . '.php';
        return ob_get_clean();
    }
}
