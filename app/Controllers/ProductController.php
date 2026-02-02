<?php
/**
 * Product Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'category_id' => $_GET['category'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'on_sale' => !empty($_GET['on_sale']),
            'in_stock' => !empty($_GET['in_stock']),
            'sort' => $_GET['sort'] ?? 'newest',
        ];

        $query = $_GET['q'] ?? '';
        $result = Product::search($query, $filters, $page, 12);

        // Get categories for filter
        $categories = Category::topLevel();

        $this->layout('main');
        $this->view('pages/products/index', [
            'meta_title' => 'Products | ' . config('app.name'),
            'meta_description' => 'Browse our collection of products',
            'products' => $result['data'],
            'pagination' => $result,
            'categories' => $categories,
            'filters' => $filters,
            'query' => $query,
        ]);
    }

    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);

        if (!$product) {
            http_response_code(404);
            $this->layout('main');
            $this->view('errors/404');
            return;
        }

        // Increment view count
        $product->incrementViews();

        // Get product data
        $images = $product->getImages();
        $categories = $product->getCategories();
        $primaryCategory = $product->getPrimaryCategory();
        $variants = $product->getVariants();
        $attributes = $product->getAttributes();
        $reviews = $product->getReviews();
        $relatedProducts = $product->getRelatedProducts(4);

        // Build breadcrumbs
        $breadcrumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Products', 'url' => url('/products')],
        ];

        if ($primaryCategory) {
            $category = Category::find($primaryCategory['id']);
            if ($category) {
                foreach ($category->getBreadcrumbs() as $crumb) {
                    $breadcrumbs[] = $crumb;
                }
            }
        }

        $breadcrumbs[] = ['name' => $product->name, 'url' => null];

        // Schema data
        $schema = [
            '@context' => 'https://schema.org',
            ...$product->getSchemaData(),
        ];

        $this->layout('main');
        $this->view('pages/products/show', [
            'meta_title' => $product->meta_title ?: $product->name . ' | ' . config('app.name'),
            'meta_description' => $product->meta_description ?: $product->short_description,
            'og_type' => 'product',
            'og_image' => $product->getPrimaryImage() ? url('storage/uploads/' . $product->getPrimaryImage()) : null,
            'schema' => $schema,
            'product' => $product,
            'images' => $images,
            'categories' => $categories,
            'primaryCategory' => $primaryCategory,
            'variants' => $variants,
            'attributes' => $attributes,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
