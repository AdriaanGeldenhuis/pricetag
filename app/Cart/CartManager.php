<?php
/**
 * Cart Manager
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Centralized cart management with session and database persistence.
 */

declare(strict_types=1);

namespace App\Cart;

use App\Core\Database;
use App\Services\Cache;
use PDO;

class CartManager
{
    private static ?self $instance = null;
    private PDO $db;
    private ?int $cartId = null;
    private ?int $userId = null;
    private string $sessionId;
    private array $items = [];
    private bool $loaded = false;

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->sessionId = session_id();
        $this->userId = $_SESSION['user_id'] ?? null;
        $this->load();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load cart from database
     */
    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $cart = null;

        // Try to find cart by user ID first (logged in users)
        if ($this->userId) {
            $stmt = $this->db->prepare("
                SELECT * FROM carts WHERE user_id = ? LIMIT 1
            ");
            $stmt->execute([$this->userId]);
            $cart = $stmt->fetch();
        }

        // Fall back to session ID (guests)
        if (!$cart && $this->sessionId) {
            $stmt = $this->db->prepare("
                SELECT * FROM carts WHERE session_id = ? AND user_id IS NULL LIMIT 1
            ");
            $stmt->execute([$this->sessionId]);
            $cart = $stmt->fetch();
        }

        if ($cart) {
            $this->cartId = (int) $cart['id'];
            $this->loadItems();
        }

        $this->loaded = true;
    }

    /**
     * Load cart items
     */
    private function loadItems(): void
    {
        if (!$this->cartId) {
            return;
        }

        $stmt = $this->db->prepare("
            SELECT ci.*, p.name, p.slug, p.price, p.sale_price, p.stock_quantity,
                   p.track_stock, p.status as product_status,
                   (SELECT image_url FROM product_images
                    WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$this->cartId]);
        $this->items = $stmt->fetchAll();
    }

    /**
     * Ensure cart exists in database
     */
    private function ensureCart(): void
    {
        if ($this->cartId) {
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO carts (user_id, session_id, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
        ");
        $stmt->execute([$this->userId, $this->sessionId]);
        $this->cartId = (int) $this->db->lastInsertId();
    }

    /**
     * Add item to cart
     */
    public function add(int $productId, int $quantity = 1, ?int $variantId = null): array
    {
        $this->ensureCart();

        // Check if product exists and is available
        $stmt = $this->db->prepare("
            SELECT id, name, price, sale_price, stock_quantity, track_stock, status
            FROM products WHERE id = ? AND status = 'active'
        ");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            return ['success' => false, 'error' => 'Product not found or unavailable'];
        }

        // Check stock
        if ($product['track_stock'] && $product['stock_quantity'] < $quantity) {
            return [
                'success' => false,
                'error' => 'Insufficient stock. Only ' . $product['stock_quantity'] . ' available.'
            ];
        }

        // Check if item already in cart
        $stmt = $this->db->prepare("
            SELECT id, quantity FROM cart_items
            WHERE cart_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))
        ");
        $stmt->execute([$this->cartId, $productId, $variantId, $variantId]);
        $existingItem = $stmt->fetch();

        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;

            if ($product['track_stock'] && $product['stock_quantity'] < $newQuantity) {
                return [
                    'success' => false,
                    'error' => 'Cannot add more. Only ' . $product['stock_quantity'] . ' available.'
                ];
            }

            $stmt = $this->db->prepare("
                UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?
            ");
            $stmt->execute([$newQuantity, $existingItem['id']]);
        } else {
            // Insert new item
            $stmt = $this->db->prepare("
                INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$this->cartId, $productId, $variantId, $quantity]);
        }

        // Update cart timestamp
        $stmt = $this->db->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$this->cartId]);

        // Reload items
        $this->loadItems();
        $this->updateSessionCount();

        return [
            'success' => true,
            'message' => 'Item added to cart',
            'cart' => $this->getSummary()
        ];
    }

    /**
     * Update item quantity
     */
    public function update(int $itemId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->remove($itemId);
        }

        // Get item with product info
        $stmt = $this->db->prepare("
            SELECT ci.*, p.stock_quantity, p.track_stock
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.id = ? AND ci.cart_id = ?
        ");
        $stmt->execute([$itemId, $this->cartId]);
        $item = $stmt->fetch();

        if (!$item) {
            return ['success' => false, 'error' => 'Item not found'];
        }

        // Check stock
        if ($item['track_stock'] && $item['stock_quantity'] < $quantity) {
            return [
                'success' => false,
                'error' => 'Only ' . $item['stock_quantity'] . ' available.'
            ];
        }

        $stmt = $this->db->prepare("
            UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$quantity, $itemId]);

        // Update cart timestamp
        $stmt = $this->db->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$this->cartId]);

        $this->loadItems();
        $this->updateSessionCount();

        return [
            'success' => true,
            'message' => 'Cart updated',
            'cart' => $this->getSummary()
        ];
    }

    /**
     * Remove item from cart
     */
    public function remove(int $itemId): array
    {
        $stmt = $this->db->prepare("
            DELETE FROM cart_items WHERE id = ? AND cart_id = ?
        ");
        $stmt->execute([$itemId, $this->cartId]);

        // Update cart timestamp
        $stmt = $this->db->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$this->cartId]);

        $this->loadItems();
        $this->updateSessionCount();

        return [
            'success' => true,
            'message' => 'Item removed',
            'cart' => $this->getSummary()
        ];
    }

    /**
     * Clear entire cart
     */
    public function clear(): array
    {
        if (!$this->cartId) {
            return ['success' => true, 'message' => 'Cart is empty'];
        }

        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$this->cartId]);

        $this->items = [];
        $this->updateSessionCount();

        return [
            'success' => true,
            'message' => 'Cart cleared',
            'cart' => $this->getSummary()
        ];
    }

    /**
     * Get cart items
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get cart summary
     */
    public function getSummary(): array
    {
        $subtotal = 0;
        $itemCount = 0;

        foreach ($this->items as $item) {
            $price = $item['sale_price'] ?? $item['price'];
            $subtotal += $price * $item['quantity'];
            $itemCount += $item['quantity'];
        }

        $shipping = $this->calculateShipping($subtotal);
        $tax = $this->calculateTax($subtotal);

        return [
            'items' => $this->items,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $subtotal + $shipping + $tax,
            'free_shipping_threshold' => (float) config('payment.shipping.free_threshold', 500),
            'qualifies_for_free_shipping' => $subtotal >= config('payment.shipping.free_threshold', 500),
        ];
    }

    /**
     * Calculate shipping
     */
    private function calculateShipping(float $subtotal): float
    {
        $freeThreshold = (float) config('payment.shipping.free_threshold', 500);

        if ($subtotal >= $freeThreshold) {
            return 0;
        }

        return (float) config('payment.shipping.default_rate', 75);
    }

    /**
     * Calculate tax (VAT included in South Africa)
     */
    private function calculateTax(float $subtotal): float
    {
        // VAT is included in prices in South Africa
        return 0;
    }

    /**
     * Get item count
     */
    public function count(): int
    {
        return array_sum(array_column($this->items, 'quantity'));
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Update session cart count
     */
    private function updateSessionCount(): void
    {
        $_SESSION['cart_count'] = $this->count();
    }

    /**
     * Merge guest cart with user cart after login
     */
    public function mergeGuestCart(int $userId): void
    {
        $this->userId = $userId;

        // Find guest cart
        $stmt = $this->db->prepare("
            SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL
        ");
        $stmt->execute([$this->sessionId]);
        $guestCart = $stmt->fetch();

        if (!$guestCart) {
            // No guest cart to merge, just load user cart
            $this->loaded = false;
            $this->cartId = null;
            $this->load();
            return;
        }

        // Find user cart
        $stmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userCart = $stmt->fetch();

        if ($userCart) {
            // Merge items from guest cart to user cart
            $stmt = $this->db->prepare("
                UPDATE cart_items SET cart_id = ? WHERE cart_id = ?
            ");
            $stmt->execute([$userCart['id'], $guestCart['id']]);

            // Delete guest cart
            $stmt = $this->db->prepare("DELETE FROM carts WHERE id = ?");
            $stmt->execute([$guestCart['id']]);

            $this->cartId = (int) $userCart['id'];
        } else {
            // Assign guest cart to user
            $stmt = $this->db->prepare("
                UPDATE carts SET user_id = ?, session_id = NULL, updated_at = NOW() WHERE id = ?
            ");
            $stmt->execute([$userId, $guestCart['id']]);
            $this->cartId = (int) $guestCart['id'];
        }

        $this->loadItems();
        $this->updateSessionCount();
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(string $code): array
    {
        if (!$this->cartId) {
            return ['success' => false, 'error' => 'Cart is empty'];
        }

        // Validate coupon
        $stmt = $this->db->prepare("
            SELECT * FROM coupons
            WHERE code = ? AND is_active = 1
            AND (starts_at IS NULL OR starts_at <= NOW())
            AND (expires_at IS NULL OR expires_at >= NOW())
            AND (usage_limit IS NULL OR used_count < usage_limit)
        ");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return ['success' => false, 'error' => 'Invalid or expired coupon code'];
        }

        // Check minimum order
        $summary = $this->getSummary();
        if ($coupon['minimum_order'] && $summary['subtotal'] < $coupon['minimum_order']) {
            return [
                'success' => false,
                'error' => 'Minimum order of R' . number_format($coupon['minimum_order'], 2) . ' required'
            ];
        }

        // Apply coupon to cart
        $stmt = $this->db->prepare("
            UPDATE carts SET coupon_code = ?, updated_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$code, $this->cartId]);

        return [
            'success' => true,
            'message' => 'Coupon applied successfully',
            'discount' => $this->calculateDiscount($coupon, $summary['subtotal'])
        ];
    }

    /**
     * Calculate discount amount
     */
    private function calculateDiscount(array $coupon, float $subtotal): float
    {
        if ($coupon['discount_type'] === 'percentage') {
            return $subtotal * ($coupon['discount_value'] / 100);
        }

        return min($coupon['discount_value'], $subtotal);
    }

    /**
     * Get cart ID
     */
    public function getCartId(): ?int
    {
        return $this->cartId;
    }
}
