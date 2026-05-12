<?php
/**
 * Product Policy
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Authorization rules for product-related actions.
 */

declare(strict_types=1);

namespace App\Policies;

class ProductPolicy extends Policy
{
    /**
     * Can user view any products (list)?
     */
    public function viewAny(): bool
    {
        // Anyone can view product listings
        return $this->allow();
    }

    /**
     * Can user view a specific product?
     */
    public function view(array $product): bool
    {
        // Active products are visible to everyone
        if (($product['status'] ?? '') === 'active') {
            return $this->allow();
        }

        // Admins can view inactive products
        return $this->isAdmin();
    }

    /**
     * Can user create products?
     */
    public function create(): bool
    {
        // Only admins can create products
        return $this->isAdmin();
    }

    /**
     * Can user update a product?
     */
    public function update(array $product): bool
    {
        // Only admins can update products
        return $this->isAdmin();
    }

    /**
     * Can user delete a product?
     */
    public function delete(array $product): bool
    {
        // Only super admins can delete products
        return $this->isSuperAdmin();
    }

    /**
     * Can user manage inventory?
     */
    public function manageInventory(array $product): bool
    {
        return $this->isAdmin();
    }

    /**
     * Can user add product to cart?
     */
    public function addToCart(array $product): bool
    {
        // Must be active and in stock
        if (($product['status'] ?? '') !== 'active') {
            return $this->deny();
        }

        // Check stock if tracking is enabled
        if (($product['manage_stock'] ?? false) && ($product['stock_quantity'] ?? 0) <= 0) {
            return $this->deny();
        }

        return $this->allow();
    }

    /**
     * Can user review product?
     */
    public function review(array $product): bool
    {
        // Must be authenticated to review
        if (!$this->isAuthenticated()) {
            return $this->deny();
        }

        // Product must be active
        return ($product['status'] ?? '') === 'active';
    }
}
