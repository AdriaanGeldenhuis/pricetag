<?php
/**
 * Product Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
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
            'attributes' => $_GET['attr'] ?? [],
        ];

        $query = $_GET['q'] ?? '';
        $result = Product::search($query, $filters, $page, 25);

        // Get categories for filter
        $categories = Category::topLevel();

        // Get available attribute filters when a category is selected
        $availableFilters = ['attributes' => [], 'price_range' => ['min_price' => null, 'max_price' => null]];
        if (!empty($filters['category_id'])) {
            $selectedCategory = Category::find($filters['category_id']);
            if ($selectedCategory) {
                $availableFilters = $selectedCategory->getAvailableFilters();
            }
        }

        $this->layout('main');
        $this->view('pages/products/index', [
            'meta_title' => 'Products | ' . config('app.name'),
            'meta_description' => 'Browse our collection of products',
            'products' => $result['data'],
            'pagination' => $result,
            'categories' => $categories,
            'filters' => $filters,
            'availableFilters' => $availableFilters,
            'query' => $query,
        ]);
    }

    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);

        if (!$product) {
            // Old slug? Follow a recorded 301 from the redirects table so
            // existing inbound links keep working after a rename.
            $stmt = Database::getInstance()->prepare(
                "SELECT to_url, status_code FROM redirects
                 WHERE from_url = ? AND is_active = 1
                 LIMIT 1"
            );
            $stmt->execute(["/products/{$slug}"]);
            $row = $stmt->fetch();
            if ($row) {
                $this->redirect($row['to_url'], (int)($row['status_code'] ?? 301));
                return;
            }

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
        $specifications = $product->getSpecifications();
        $reviews = $product->getReviews();
        $relatedProducts = $product->getRelatedProducts(4);

        // Fallback: if no manually-curated related products exist, fill the
        // "Related" slot with other products from the primary category so
        // customers always have something to browse next.
        if (empty($relatedProducts) && $primaryCategory) {
            $category = Category::find($primaryCategory['id']);
            if ($category) {
                $page = $category->getProducts([], 1, 8);
                $relatedProducts = array_values(array_filter(
                    $page['data'] ?? [],
                    fn($p) => (int)($p->id ?? 0) !== (int)$product->id
                ));
                $relatedProducts = array_slice($relatedProducts, 0, 4);
            }
        }

        // Initial wishlist state so the heart button renders filled if the
        // logged-in user has already saved this product.
        $inWishlist = false;
        if (auth()) {
            $currentUser = user();
            if ($currentUser && !empty($currentUser['id'])) {
                $stmt = Database::getInstance()->prepare(
                    "SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1"
                );
                $stmt->execute([(int) $currentUser['id'], (int) $product->id]);
                $inWishlist = (bool) $stmt->fetchColumn();
            }
        }

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
            'canonical' => url('/products/' . $product->slug),
            'og_type' => 'product',
            'og_image' => $product->getPrimaryImage() ? url('storage/uploads/' . $product->getPrimaryImage()) : null,
            'schema' => $schema,
            'product' => $product,
            'images' => $images,
            'categories' => $categories,
            'primaryCategory' => $primaryCategory,
            'variants' => $variants,
            'attributes' => $attributes,
            'specifications' => $specifications,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
            'breadcrumbs' => $breadcrumbs,
            'inWishlist' => $inWishlist,
        ]);
    }
}
