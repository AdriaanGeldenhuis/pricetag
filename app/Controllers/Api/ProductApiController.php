<?php
/**
 * Product API Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
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
     * Get similar/related products
     */
    public function similar(int $id): void
    {
        $product = Product::find($id);

        if (!$product || $product->status !== 'active') {
            jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
            return;
        }

        // Try explicit related products first
        $related = $product->getRelatedProducts(8);

        // Fall back to same-category products
        if (empty($related)) {
            $category = $product->getPrimaryCategory();
            if ($category) {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT p.* FROM products p
                    JOIN product_categories pc ON pc.product_id = p.id
                    WHERE pc.category_id = ? AND p.id != ? AND p.status = 'active'
                    ORDER BY p.sold_count DESC
                    LIMIT 8
                ");
                $stmt->execute([$category['id'], $id]);

                while ($data = $stmt->fetch()) {
                    $p = new Product($data);
                    $p->exists = true;
                    $related[] = $p;
                }
            }
        }

        $products = array_map(function ($p) {
            $image = $p->getPrimaryImage();
            $cat = $p->getPrimaryCategory();
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'compare_price' => $p->compare_price ? (float) $p->compare_price : null,
                'image' => $image,
                'category' => $cat['name'] ?? null,
                'in_stock' => $p->isInStock(),
            ];
        }, $related);

        jsonResponse(['success' => true, 'products' => $products]);
    }

    /**
     * Submit a product question
     */
    public function submitQuestion(int $id): void
    {
        $product = Product::find($id);

        if (!$product || $product->status !== 'active') {
            jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $question = trim($_POST['question'] ?? '');

        if (empty($name) || empty($email) || empty($question)) {
            jsonResponse(['success' => false, 'message' => 'All fields are required'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Please enter a valid email address'], 422);
            return;
        }

        if (strlen($question) < 10) {
            jsonResponse(['success' => false, 'message' => 'Question must be at least 10 characters'], 422);
            return;
        }

        $db = Database::getInstance();

        // Store as contact submission with product context
        $stmt = $db->prepare("
            INSERT INTO contact_submissions (name, email, subject, message, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");

        $subject = 'Product Question: ' . $product->name;
        $message = "Question about: {$product->name} (ID: {$product->id})\n\n{$question}";

        $stmt->execute([
            $name,
            $email,
            $subject,
            $message,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ]);

        jsonResponse(['success' => true, 'message' => 'Your question has been submitted! We\'ll get back to you soon.']);
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
