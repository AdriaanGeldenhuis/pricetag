<?php
/**
 * Category Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): void
    {
        $categories = Category::topLevel();

        // Get child categories for each
        foreach ($categories as $category) {
            $category->children = $category->getChildren();
            $category->product_count = $category->getProductCount();
        }

        $this->layout('main');
        $this->view('pages/categories/index', [
            'meta_title' => 'Categories | ' . config('app.name'),
            'meta_description' => 'Browse all product categories',
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): void
    {
        $category = Category::findBySlug($slug);

        if (!$category) {
            http_response_code(404);
            $this->layout('main');
            $this->view('errors/404');
            return;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'on_sale' => !empty($_GET['on_sale']),
            'in_stock' => !empty($_GET['in_stock']),
            'sort' => $_GET['sort'] ?? 'newest',
            'attributes' => $_GET['attr'] ?? [],
        ];

        // Get products
        $result = $category->getProducts($filters, $page, 12);

        // Get available filters
        $availableFilters = $category->getAvailableFilters();

        // Get subcategories
        $subcategories = $category->getChildren();

        // Build breadcrumbs
        $breadcrumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Categories', 'url' => url('/categories')],
        ];
        foreach ($category->getBreadcrumbs() as $crumb) {
            $breadcrumbs[] = $crumb;
        }

        $this->layout('main');
        $this->view('pages/categories/show', [
            'meta_title' => $category->meta_title ?: $category->name . ' | ' . config('app.name'),
            'meta_description' => $category->meta_description ?: $category->description,
            'canonical' => url('/categories/' . $category->slug),
            'category' => $category,
            'products' => $result['data'],
            'pagination' => $result,
            'availableFilters' => $availableFilters,
            'subcategories' => $subcategories,
            'filters' => $filters,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
