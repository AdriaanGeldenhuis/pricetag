<?php
/**
 * Product API Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Product;

class ProductApiController extends Controller
{
    protected Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    /**
     * Get list of products
     */
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = min((int) ($_GET['limit'] ?? 20), 100);
        $sort = $_GET['sort'] ?? 'newest';
        $categoryId = $_GET['category'] ?? null;

        $filters = [
            'category_id' => $categoryId,
            'is_active' => 1,
        ];

        if (isset($_GET['featured'])) {
            $filters['is_featured'] = 1;
        }

        if (isset($_GET['on_sale'])) {
            $filters['is_on_sale'] = 1;
        }

        $products = $this->productModel->getProducts($filters, $sort, $page, $limit);
        $total = $this->productModel->countProducts($filters);

        jsonResponse([
            'success' => true,
            'products' => array_map([$this, 'formatProduct'], $products),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);
    }

    /**
     * Get single product details (for quick view)
     */
    public function show(int $id): void
    {
        $product = Product::find($id);

        if (!$product || $product->status !== 'active') {
            jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
            return;
        }

        // Get additional data
        $images = $this->productModel->getProductImages($id);
        $variants = $this->productModel->getProductVariants($id);
        $category = $product->getPrimaryCategory();
        $primaryImage = $product->getPrimaryImage();

        jsonResponse([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'price' => (float) $product->price,
                'sale_price' => $product->compare_price && $product->compare_price > $product->price
                    ? (float) $product->price
                    : null,
                'compare_price' => $product->compare_price ? (float) $product->compare_price : null,
                'stock_quantity' => (int) $product->stock_quantity,
                'in_stock' => $product->isInStock(),
                'image' => $primaryImage,
                'images' => array_column($images, 'image_path'),
                'category' => $category['name'] ?? null,
                'category_id' => $category['id'] ?? null,
                'rating' => (float) ($product->rating_average ?? 0),
                'review_count' => (int) ($product->rating_count ?? 0),
                'is_on_sale' => (bool) $product->is_on_sale,
                'is_new' => (bool) $product->is_new,
                'is_featured' => (bool) $product->is_featured,
                'variants' => array_map(function($v) {
                    return [
                        'id' => $v['id'],
                        'name' => $v['name'] ?? null,
                        'sku' => $v['sku'],
                        'price' => (float) $v['price'],
                        'stock_quantity' => (int) $v['stock_quantity'],
                        'attributes' => json_decode($v['attributes'] ?? '{}', true),
                    ];
                }, $variants),
            ],
        ]);
    }

    /**
     * Format product for list response
     */
    protected function formatProduct($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => (float) $product->price,
            'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
            'image' => $product->featured_image,
            'category' => $product->category_name ?? null,
            'rating' => (float) ($product->rating_average ?? 0),
            'review_count' => (int) ($product->rating_count ?? 0),
            'in_stock' => $product->stock_quantity > 0,
            'is_on_sale' => (bool) $product->is_on_sale,
            'is_new' => (bool) $product->is_new,
        ];
    }
}
