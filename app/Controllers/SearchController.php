<?php
/**
 * Search Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;

class SearchController extends Controller
{
    public function index(): void
    {
        $query = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'sort' => $_GET['sort'] ?? 'relevance',
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'in_stock' => !empty($_GET['in_stock']),
        ];

        if (empty($query)) {
            $this->redirect('/products');
            return;
        }

        // Log search term
        $this->logSearchTerm($query);

        // Search products
        $result = Product::search($query, $filters, $page, 12);

        $this->layout('main');
        $this->view('pages/products/index', [
            'meta_title' => "Search: $query | " . config('app.name'),
            'meta_robots' => 'noindex, follow',
            'products' => $result['data'],
            'pagination' => $result,
            'categories' => [],
            'filters' => $filters,
            'query' => $query,
        ]);
    }

    public function suggest(): void
    {
        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            $this->json(['results' => []]);
            return;
        }

        $db = Database::getInstance();

        // Search products
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.price, pi.path as image, c.name as category
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            LEFT JOIN product_categories pc ON pc.product_id = p.id AND pc.is_primary = 1
            LEFT JOIN categories c ON c.id = pc.category_id
            WHERE p.status = 'active'
            AND (p.name LIKE ? OR p.sku LIKE ?)
            ORDER BY
                CASE WHEN p.name LIKE ? THEN 0 ELSE 1 END,
                p.sold_count DESC
            LIMIT 8
        ");

        $term = $query . '%';
        $likeTerm = '%' . $query . '%';
        $stmt->execute([$likeTerm, $likeTerm, $term]);

        $results = $stmt->fetchAll();

        $this->json(['results' => $results]);
    }

    private function logSearchTerm(string $term): void
    {
        $db = Database::getInstance();

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

            $stmt = $db->prepare("INSERT INTO search_terms (term, results_count) VALUES (?, ?)");
            $stmt->execute([strtolower($term), $result['total']]);
        }
    }
}
