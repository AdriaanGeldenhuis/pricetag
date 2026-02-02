<?php
/**
 * Cart Model
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Models;

use App\Core\Database;

class Cart
{
    private ?int $cartId = null;
    private array $items = [];
    private ?string $couponCode = null;

    public function __construct()
    {
        $this->load();
    }

    /**
     * Load cart from database or session
     */
    private function load(): void
    {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();

        // Try to find existing cart
        if ($userId) {
            $stmt = $db->prepare("SELECT * FROM carts WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM carts WHERE session_id = ? AND user_id IS NULL LIMIT 1");
            $stmt->execute([$sessionId]);
        }

        $cart = $stmt->fetch();

        if ($cart) {
            $this->cartId = $cart['id'];
            $this->couponCode = $cart['coupon_code'];
            $this->loadItems();
        }
    }

    /**
     * Load cart items
     */
    private function loadItems(): void
    {
        if (!$this->cartId) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT ci.*, p.name, p.slug, p.price as current_price, p.stock_quantity, p.manage_stock,
                   pi.path as image
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$this->cartId]);
        $this->items = $stmt->fetchAll();
    }

    /**
     * Get or create cart
     */
    private function getOrCreateCart(): int
    {
        if ($this->cartId) {
            return $this->cartId;
        }

        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();

        $stmt = $db->prepare("INSERT INTO carts (user_id, session_id, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))");
        $stmt->execute([$userId, $userId ? null : $sessionId]);
        $this->cartId = (int) $db->lastInsertId();

        return $this->cartId;
    }

    /**
     * Add item to cart
     */
    public function add(int $productId, int $quantity = 1, ?int $variantId = null, array $options = []): bool
    {
        $db = Database::getInstance();

        // Get product
        $product = Product::find($productId);
        if (!$product || $product->status !== 'active') {
            return false;
        }

        // Check stock
        if ($product->manage_stock && $product->stock_quantity < $quantity) {
            return false;
        }

        $price = $product->price;

        // Get variant price if applicable
        if ($variantId) {
            $stmt = $db->prepare("SELECT price, stock_quantity FROM product_variants WHERE id = ? AND product_id = ? AND is_active = 1");
            $stmt->execute([$variantId, $productId]);
            $variant = $stmt->fetch();

            if (!$variant) {
                return false;
            }

            $price = $variant['price'];

            if ($variant['stock_quantity'] < $quantity) {
                return false;
            }
        }

        $cartId = $this->getOrCreateCart();

        // Check if item already exists
        $stmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))");
        $stmt->execute([$cartId, $productId, $variantId, $variantId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            $newQty = $existing['quantity'] + $quantity;
            $stmt = $db->prepare("UPDATE cart_items SET quantity = ?, price = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newQty, $price, $existing['id']]);
        } else {
            // Insert new item
            $stmt = $db->prepare("INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, price, options) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cartId, $productId, $variantId, $quantity, $price, json_encode($options)]);
        }

        $this->loadItems();
        $this->updateSessionCount();

        return true;
    }

    /**
     * Update item quantity
     */
    public function update(int $itemId, int $quantity): bool
    {
        if (!$this->cartId) {
            return false;
        }

        $db = Database::getInstance();

        if ($quantity <= 0) {
            return $this->remove($itemId);
        }

        // Check stock
        $stmt = $db->prepare("
            SELECT ci.product_id, ci.variant_id, p.stock_quantity, p.manage_stock
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            WHERE ci.id = ? AND ci.cart_id = ?
        ");
        $stmt->execute([$itemId, $this->cartId]);
        $item = $stmt->fetch();

        if (!$item) {
            return false;
        }

        if ($item['manage_stock'] && $item['stock_quantity'] < $quantity) {
            return false;
        }

        $stmt = $db->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ? AND cart_id = ?");
        $stmt->execute([$quantity, $itemId, $this->cartId]);

        $this->loadItems();
        $this->updateSessionCount();

        return true;
    }

    /**
     * Remove item from cart
     */
    public function remove(int $itemId): bool
    {
        if (!$this->cartId) {
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
        $stmt->execute([$itemId, $this->cartId]);

        $this->loadItems();
        $this->updateSessionCount();

        return true;
    }

    /**
     * Clear cart
     */
    public function clear(): void
    {
        if (!$this->cartId) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$this->cartId]);

        $this->items = [];
        $this->updateSessionCount();
    }

    /**
     * Get cart items
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get item count
     */
    public function getCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Get subtotal
     */
    public function getSubtotal(): float
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    /**
     * Apply coupon
     */
    public function applyCoupon(string $code): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM coupons
            WHERE code = ? AND is_active = 1
            AND (starts_at IS NULL OR starts_at <= NOW())
            AND (expires_at IS NULL OR expires_at > NOW())
            AND (usage_limit IS NULL OR used_count < usage_limit)
        ");
        $stmt->execute([strtoupper($code)]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid or expired coupon code'];
        }

        $subtotal = $this->getSubtotal();

        // Check minimum amount
        if ($coupon['minimum_amount'] && $subtotal < $coupon['minimum_amount']) {
            return ['success' => false, 'message' => 'Minimum order amount not reached'];
        }

        // Check user usage limit
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId && $coupon['usage_per_user']) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND user_id = ?");
            $stmt->execute([$coupon['id'], $userId]);
            if ($stmt->fetchColumn() >= $coupon['usage_per_user']) {
                return ['success' => false, 'message' => 'You have already used this coupon'];
            }
        }

        // Apply coupon
        if ($this->cartId) {
            $stmt = $db->prepare("UPDATE carts SET coupon_code = ? WHERE id = ?");
            $stmt->execute([$code, $this->cartId]);
            $this->couponCode = $code;
        }

        return ['success' => true, 'coupon' => $coupon];
    }

    /**
     * Get discount amount
     */
    public function getDiscount(): float
    {
        if (!$this->couponCode) {
            return 0;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
        $stmt->execute([strtoupper($this->couponCode)]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return 0;
        }

        $subtotal = $this->getSubtotal();

        switch ($coupon['type']) {
            case 'percentage':
                $discount = $subtotal * ($coupon['value'] / 100);
                break;
            case 'fixed':
                $discount = $coupon['value'];
                break;
            default:
                $discount = 0;
        }

        // Apply maximum discount
        if ($coupon['maximum_discount'] && $discount > $coupon['maximum_discount']) {
            $discount = $coupon['maximum_discount'];
        }

        return min($discount, $subtotal);
    }

    /**
     * Get shipping cost
     */
    public function getShipping(): float
    {
        $subtotal = $this->getSubtotal();
        $freeThreshold = config('payment.shipping.free_threshold', 500);

        if ($subtotal >= $freeThreshold) {
            return 0;
        }

        return config('payment.shipping.default_rate', 75);
    }

    /**
     * Get total
     */
    public function getTotal(): float
    {
        return $this->getSubtotal() - $this->getDiscount() + $this->getShipping();
    }

    /**
     * Update session count
     */
    private function updateSessionCount(): void
    {
        $_SESSION['cart_count'] = $this->getCount();
    }

    /**
     * Merge guest cart with user cart
     */
    public function mergeWithUser(int $userId): void
    {
        $db = Database::getInstance();
        $sessionId = session_id();

        // Get guest cart
        $stmt = $db->prepare("SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$sessionId]);
        $guestCart = $stmt->fetch();

        // Get user cart
        $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userCart = $stmt->fetch();

        if ($guestCart && $userCart) {
            // Merge items
            $stmt = $db->prepare("UPDATE cart_items SET cart_id = ? WHERE cart_id = ?");
            $stmt->execute([$userCart['id'], $guestCart['id']]);

            // Delete guest cart
            $stmt = $db->prepare("DELETE FROM carts WHERE id = ?");
            $stmt->execute([$guestCart['id']]);

            $this->cartId = $userCart['id'];
        } elseif ($guestCart) {
            // Assign guest cart to user
            $stmt = $db->prepare("UPDATE carts SET user_id = ?, session_id = NULL WHERE id = ?");
            $stmt->execute([$userId, $guestCart['id']]);
            $this->cartId = $guestCart['id'];
        }

        $this->loadItems();
        $this->updateSessionCount();
    }
}
