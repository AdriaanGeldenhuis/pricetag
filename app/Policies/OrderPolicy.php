<?php
/**
 * Order Policy
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Authorization rules for order-related actions.
 */

declare(strict_types=1);

namespace App\Policies;

class OrderPolicy extends Policy
{
    /**
     * Can user view any orders (list)?
     */
    public function viewAny(): bool
    {
        // Admins can view all orders
        if ($this->isAdmin()) {
            return $this->allow();
        }

        // Authenticated users can view their own orders (filtered in query)
        return $this->isAuthenticated();
    }

    /**
     * Can user view a specific order?
     */
    public function view(array $order): bool
    {
        // Admins can view any order
        if ($this->isAdmin()) {
            return $this->allow();
        }

        // Users can only view their own orders
        return $this->owns((int) ($order['user_id'] ?? 0));
    }

    /**
     * Can user create orders?
     */
    public function create(): bool
    {
        // Anyone can create orders (guest checkout allowed)
        return $this->allow();
    }

    /**
     * Can user update an order?
     */
    public function update(array $order): bool
    {
        // Only admins can update orders
        return $this->isAdmin();
    }

    /**
     * Can user cancel an order?
     */
    public function cancel(array $order): bool
    {
        // Admins can cancel any order
        if ($this->isAdmin()) {
            return $this->allow();
        }

        // Users can cancel their own orders if still pending
        if ($this->owns((int) ($order['user_id'] ?? 0))) {
            return in_array($order['status'] ?? '', ['pending', 'processing'], true);
        }

        return $this->deny();
    }

    /**
     * Can user delete an order?
     */
    public function delete(array $order): bool
    {
        // Only super admins can delete orders
        return $this->isSuperAdmin();
    }

    /**
     * Can user request refund?
     */
    public function requestRefund(array $order): bool
    {
        // Admins can process refunds
        if ($this->isAdmin()) {
            return $this->allow();
        }

        // Users can request refund on their completed orders
        if ($this->owns((int) ($order['user_id'] ?? 0))) {
            return ($order['status'] ?? '') === 'completed';
        }

        return $this->deny();
    }

    /**
     * Can user download invoice?
     */
    public function downloadInvoice(array $order): bool
    {
        // Admins can download any invoice
        if ($this->isAdmin()) {
            return $this->allow();
        }

        // Users can download invoices for their own orders
        return $this->owns((int) ($order['user_id'] ?? 0));
    }
}
