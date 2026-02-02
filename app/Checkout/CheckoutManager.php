<?php
/**
 * Checkout Manager
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles the checkout process including validation, order creation, and payment.
 */

declare(strict_types=1);

namespace App\Checkout;

use App\Core\Database;
use App\Cart\CartManager;
use App\Services\YocoPayment;
use PDO;

class CheckoutManager
{
    private PDO $db;
    private CartManager $cart;
    private array $errors = [];
    private array $checkoutData = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->cart = CartManager::getInstance();
    }

    /**
     * Validate checkout data
     */
    public function validate(array $data): bool
    {
        $this->errors = [];
        $this->checkoutData = $data;

        // Cart validation
        if ($this->cart->isEmpty()) {
            $this->errors['cart'] = 'Your cart is empty';
            return false;
        }

        // Billing validation
        $this->validateAddress($data, 'billing');

        // Shipping validation (if different from billing)
        if (empty($data['same_as_billing'])) {
            $this->validateAddress($data, 'shipping');
        }

        // Email validation
        if (empty($data['email'])) {
            $this->errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email address';
        }

        // Phone validation
        if (empty($data['phone'])) {
            $this->errors['phone'] = 'Phone number is required';
        }

        // Stock validation
        $this->validateStock();

        return empty($this->errors);
    }

    /**
     * Validate address fields
     */
    private function validateAddress(array $data, string $type): void
    {
        $fields = [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'address_line_1' => 'Address',
            'city' => 'City',
            'province' => 'Province',
            'postal_code' => 'Postal code',
        ];

        foreach ($fields as $field => $label) {
            $key = "{$type}_{$field}";
            if (empty($data[$key])) {
                $this->errors[$key] = "$label is required";
            }
        }

        // Postal code format (South African)
        $postalKey = "{$type}_postal_code";
        if (!empty($data[$postalKey]) && !preg_match('/^\d{4}$/', $data[$postalKey])) {
            $this->errors[$postalKey] = 'Invalid postal code (must be 4 digits)';
        }
    }

    /**
     * Validate stock availability
     */
    private function validateStock(): void
    {
        $items = $this->cart->getItems();

        foreach ($items as $item) {
            if ($item['track_stock'] && $item['stock_quantity'] < $item['quantity']) {
                $this->errors['stock'][] = sprintf(
                    '%s: Only %d available (you have %d in cart)',
                    $item['name'],
                    $item['stock_quantity'],
                    $item['quantity']
                );
            }

            if ($item['product_status'] !== 'active') {
                $this->errors['stock'][] = sprintf('%s is no longer available', $item['name']);
            }
        }
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create order from checkout
     */
    public function createOrder(array $data): ?int
    {
        if (!$this->validate($data)) {
            return null;
        }

        $cartSummary = $this->cart->getSummary();

        Database::beginTransaction();

        try {
            // Create order
            $orderNumber = generateOrderNumber();
            $userId = $_SESSION['user_id'] ?? null;

            // Shipping address (use billing if same)
            if (!empty($data['same_as_billing'])) {
                $data['shipping_first_name'] = $data['billing_first_name'];
                $data['shipping_last_name'] = $data['billing_last_name'];
                $data['shipping_company'] = $data['billing_company'] ?? null;
                $data['shipping_address_line_1'] = $data['billing_address_line_1'];
                $data['shipping_address_line_2'] = $data['billing_address_line_2'] ?? null;
                $data['shipping_city'] = $data['billing_city'];
                $data['shipping_province'] = $data['billing_province'];
                $data['shipping_postal_code'] = $data['billing_postal_code'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    user_id, order_number, email, phone, status,
                    billing_first_name, billing_last_name, billing_company,
                    billing_address_line_1, billing_address_line_2,
                    billing_city, billing_province, billing_postal_code, billing_country,
                    shipping_first_name, shipping_last_name, shipping_company,
                    shipping_address_line_1, shipping_address_line_2,
                    shipping_city, shipping_province, shipping_postal_code, shipping_country,
                    subtotal, shipping_amount, tax_amount, discount_amount, total,
                    coupon_code, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, 'pending',
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, 'ZA',
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, 'ZA',
                    ?, ?, ?, ?, ?,
                    ?, ?, NOW(), NOW()
                )
            ");

            $stmt->execute([
                $userId,
                $orderNumber,
                $data['email'],
                $data['phone'],
                $data['billing_first_name'],
                $data['billing_last_name'],
                $data['billing_company'] ?? null,
                $data['billing_address_line_1'],
                $data['billing_address_line_2'] ?? null,
                $data['billing_city'],
                $data['billing_province'],
                $data['billing_postal_code'],
                $data['shipping_first_name'],
                $data['shipping_last_name'],
                $data['shipping_company'] ?? null,
                $data['shipping_address_line_1'],
                $data['shipping_address_line_2'] ?? null,
                $data['shipping_city'],
                $data['shipping_province'],
                $data['shipping_postal_code'],
                $cartSummary['subtotal'],
                $cartSummary['shipping'],
                $cartSummary['tax'],
                0, // discount_amount (calculated from coupon if applied)
                $cartSummary['total'],
                null, // coupon_code
                $data['notes'] ?? null,
            ]);

            $orderId = (int) $this->db->lastInsertId();

            // Create order items
            foreach ($cartSummary['items'] as $item) {
                $price = $item['sale_price'] ?? $item['price'];

                $stmt = $this->db->prepare("
                    INSERT INTO order_items (
                        order_id, product_id, variant_id, name, sku,
                        price, quantity, total, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['name'],
                    $item['sku'] ?? null,
                    $price,
                    $item['quantity'],
                    $price * $item['quantity'],
                ]);

                // Reduce stock
                if ($item['track_stock']) {
                    $stmt = $this->db->prepare("
                        UPDATE products
                        SET stock_quantity = stock_quantity - ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$item['quantity'], $item['product_id']]);
                }
            }

            // Add status history
            $stmt = $this->db->prepare("
                INSERT INTO order_status_history (order_id, status, notes, created_at)
                VALUES (?, 'pending', 'Order created', NOW())
            ");
            $stmt->execute([$orderId]);

            // Store order in session for payment processing
            $_SESSION['pending_order_id'] = $orderId;

            Database::commit();

            return $orderId;

        } catch (\Throwable $e) {
            Database::rollBack();
            logMessage('error', 'Order creation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            $this->errors['order'] = 'Failed to create order. Please try again.';
            return null;
        }
    }

    /**
     * Process payment for order
     */
    public function processPayment(int $orderId, string $paymentToken): array
    {
        // Get order
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? AND status = 'pending'");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return ['success' => false, 'error' => 'Order not found or already processed'];
        }

        // Process payment with Yoco
        $yoco = new YocoPayment();
        $result = $yoco->charge([
            'token' => $paymentToken,
            'amount' => (int) ($order['total'] * 100), // Convert to cents
            'currency' => 'ZAR',
            'metadata' => [
                'order_id' => $orderId,
                'order_number' => $order['order_number'],
            ],
        ]);

        if (!$result['success']) {
            // Log failed payment
            $this->logPayment($orderId, 'failed', $result);
            return $result;
        }

        // Update order status
        Database::beginTransaction();

        try {
            // Update order
            $stmt = $this->db->prepare("
                UPDATE orders SET status = 'paid', payment_status = 'paid', updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$orderId]);

            // Log payment
            $this->logPayment($orderId, 'success', $result);

            // Add status history
            $stmt = $this->db->prepare("
                INSERT INTO order_status_history (order_id, status, notes, created_at)
                VALUES (?, 'paid', 'Payment received', NOW())
            ");
            $stmt->execute([$orderId]);

            // Clear cart
            $this->cart->clear();
            unset($_SESSION['pending_order_id']);

            // Generate invoice number
            $invoiceNumber = generateInvoiceNumber();
            $stmt = $this->db->prepare("UPDATE orders SET invoice_number = ? WHERE id = ?");
            $stmt->execute([$invoiceNumber, $orderId]);

            Database::commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $order['order_number'],
                'message' => 'Payment successful',
            ];

        } catch (\Throwable $e) {
            Database::rollBack();
            logMessage('error', 'Payment processing failed after charge', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'Payment recorded but order update failed'];
        }
    }

    /**
     * Log payment attempt
     */
    private function logPayment(int $orderId, string $status, array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO payments (
                order_id, gateway, gateway_reference, amount, currency,
                status, response_data, created_at
            ) VALUES (?, 'yoco', ?, ?, 'ZAR', ?, ?, NOW())
        ");

        $stmt->execute([
            $orderId,
            $data['id'] ?? null,
            $data['amount'] ?? 0,
            $status,
            json_encode($data),
        ]);
    }

    /**
     * Get shipping methods
     */
    public function getShippingMethods(float $subtotal): array
    {
        $freeThreshold = (float) config('payment.shipping.free_threshold', 500);
        $standardRate = (float) config('payment.shipping.default_rate', 75);
        $expressRate = (float) config('payment.shipping.express_rate', 150);

        $methods = [
            [
                'id' => 'standard',
                'name' => 'Standard Delivery',
                'description' => '3-5 business days',
                'price' => $subtotal >= $freeThreshold ? 0 : $standardRate,
                'free' => $subtotal >= $freeThreshold,
            ],
            [
                'id' => 'express',
                'name' => 'Express Delivery',
                'description' => '1-2 business days',
                'price' => $expressRate,
                'free' => false,
            ],
        ];

        return $methods;
    }

    /**
     * Get provinces list
     */
    public function getProvinces(): array
    {
        return [
            'EC' => 'Eastern Cape',
            'FS' => 'Free State',
            'GP' => 'Gauteng',
            'KZN' => 'KwaZulu-Natal',
            'LP' => 'Limpopo',
            'MP' => 'Mpumalanga',
            'NC' => 'Northern Cape',
            'NW' => 'North West',
            'WC' => 'Western Cape',
        ];
    }
}
