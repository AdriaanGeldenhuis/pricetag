<?php
/**
 * Order Model
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Order extends Model
{
    protected static string $table = 'orders';

    protected static array $fillable = [
        'user_id', 'order_number', 'invoice_number', 'status', 'payment_status',
        'subtotal', 'discount_amount', 'shipping_amount', 'tax_amount', 'total',
        'coupon_code', 'customer_email', 'customer_phone',
        'billing_first_name', 'billing_last_name', 'billing_company',
        'billing_address_1', 'billing_address_2', 'billing_city', 'billing_province',
        'billing_postal_code', 'billing_country',
        'shipping_first_name', 'shipping_last_name', 'shipping_company',
        'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_province',
        'shipping_postal_code', 'shipping_country',
        'shipping_method', 'tracking_number', 'shipped_at', 'delivered_at',
        'customer_notes', 'admin_notes', 'ip_address', 'user_agent'
    ];

    /**
     * Create order from cart
     */
    public static function createFromCart(Cart $cart, array $data): ?self
    {
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // Generate order number
            $orderNumber = generateOrderNumber();
            $invoiceNumber = generateInvoiceNumber();

            // Calculate totals
            $subtotal = $cart->getSubtotal();
            $discount = $cart->getDiscount();
            $shipping = $cart->getShipping();
            $taxRate = config('payment.tax.rate', 15);
            $taxInclusive = config('payment.tax.inclusive', true);

            if ($taxInclusive) {
                $taxAmount = $subtotal * ($taxRate / (100 + $taxRate));
            } else {
                $taxAmount = $subtotal * ($taxRate / 100);
            }

            $total = $subtotal - $discount + $shipping;

            // Create order
            $order = new self([
                'user_id' => $_SESSION['user_id'] ?? null,
                'order_number' => $orderNumber,
                'invoice_number' => $invoiceNumber,
                'status' => 'pending',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shipping,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'coupon_code' => $data['coupon_code'] ?? null,
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'] ?? null,
                'billing_first_name' => $data['billing_first_name'],
                'billing_last_name' => $data['billing_last_name'],
                'billing_company' => $data['billing_company'] ?? null,
                'billing_address_1' => $data['billing_address_1'],
                'billing_address_2' => $data['billing_address_2'] ?? null,
                'billing_city' => $data['billing_city'],
                'billing_province' => $data['billing_province'],
                'billing_postal_code' => $data['billing_postal_code'],
                'billing_country' => $data['billing_country'] ?? 'ZA',
                'shipping_first_name' => $data['shipping_first_name'] ?? $data['billing_first_name'],
                'shipping_last_name' => $data['shipping_last_name'] ?? $data['billing_last_name'],
                'shipping_company' => $data['shipping_company'] ?? $data['billing_company'] ?? null,
                'shipping_address_1' => $data['shipping_address_1'] ?? $data['billing_address_1'],
                'shipping_address_2' => $data['shipping_address_2'] ?? $data['billing_address_2'] ?? null,
                'shipping_city' => $data['shipping_city'] ?? $data['billing_city'],
                'shipping_province' => $data['shipping_province'] ?? $data['billing_province'],
                'shipping_postal_code' => $data['shipping_postal_code'] ?? $data['billing_postal_code'],
                'shipping_country' => $data['shipping_country'] ?? $data['billing_country'] ?? 'ZA',
                'shipping_method' => $data['shipping_method'] ?? 'standard',
                'customer_notes' => $data['notes'] ?? null,
                'ip_address' => clientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            $order->save();

            // Create order items
            foreach ($cart->getItems() as $item) {
                $stmt = $db->prepare("
                    INSERT INTO order_items (order_id, product_id, variant_id, sku, name, variant_name, quantity, price, total, options)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $order->id,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['sku'] ?? $item['product_id'],
                    $item['name'],
                    $item['variant_name'] ?? null,
                    $item['quantity'],
                    $item['price'],
                    $item['price'] * $item['quantity'],
                    $item['options'] ?? null,
                ]);

                // Update stock
                if ($item['manage_stock']) {
                    $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['product_id']]);
                }
            }

            // Add status history
            $stmt = $db->prepare("INSERT INTO order_status_history (order_id, status, comment) VALUES (?, 'pending', 'Order created')");
            $stmt->execute([$order->id]);

            // Record coupon usage
            if ($discount > 0 && $data['coupon_code']) {
                $stmt = $db->prepare("SELECT id FROM coupons WHERE code = ?");
                $stmt->execute([strtoupper($data['coupon_code'])]);
                $coupon = $stmt->fetch();

                if ($coupon) {
                    $stmt = $db->prepare("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$coupon['id'], $_SESSION['user_id'] ?? null, $order->id, $discount]);

                    $stmt = $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
                    $stmt->execute([$coupon['id']]);
                }
            }

            $db->commit();

            return $order;
        } catch (\Exception $e) {
            $db->rollBack();
            logMessage('error', 'Order creation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find by order number
     */
    public static function findByOrderNumber(string $orderNumber): ?self
    {
        return self::where('order_number', $orderNumber)->first();
    }

    /**
     * Get items
     */
    public function getItems(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Get payment
     */
    public function getPayment(): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$this->id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create payment record
     */
    public function createPayment(string $gateway, float $amount): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO payments (order_id, gateway, amount, currency, status)
            VALUES (?, ?, ?, 'ZAR', 'pending')
        ");
        $stmt->execute([$this->id, $gateway, $amount]);
        return (int) $db->lastInsertId();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $status, ?string $transactionId = null, ?array $metadata = null): void
    {
        $db = Database::getInstance();

        $updates = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        $params = [$status];

        if ($transactionId) {
            $updates['transaction_id'] = $transactionId;
            $params[] = $transactionId;
        }

        if ($metadata) {
            $updates['metadata'] = json_encode($metadata);
            $params[] = json_encode($metadata);
        }

        if ($status === 'completed') {
            $updates['paid_at'] = date('Y-m-d H:i:s');
            $params[] = date('Y-m-d H:i:s');
        }

        $sets = [];
        foreach (array_keys($updates) as $key) {
            $sets[] = "$key = ?";
        }

        $params[] = $this->id;

        $stmt = $db->prepare("UPDATE payments SET " . implode(', ', $sets) . " WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute($params);

        // Update order payment status
        $this->payment_status = $status === 'completed' ? 'paid' : $status;
        if ($status === 'completed') {
            $this->status = 'processing';
        }
        $this->save();

        // Add status history
        $this->addStatusHistory($this->status, "Payment $status");
    }

    /**
     * Update status
     */
    public function updateStatus(string $status, ?string $comment = null, bool $notifyCustomer = false): void
    {
        $this->status = $status;

        if ($status === 'shipped') {
            $this->shipped_at = date('Y-m-d H:i:s');
        } elseif ($status === 'delivered') {
            $this->delivered_at = date('Y-m-d H:i:s');
        }

        $this->save();
        $this->addStatusHistory($status, $comment, $notifyCustomer);
    }

    /**
     * Add status history
     */
    public function addStatusHistory(string $status, ?string $comment = null, bool $notifyCustomer = false): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO order_status_history (order_id, status, comment, notify_customer, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$this->id, $status, $comment, $notifyCustomer ? 1 : 0, $_SESSION['user_id'] ?? null]);
    }

    /**
     * Get status history
     */
    public function getStatusHistory(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT osh.*, u.first_name, u.last_name
            FROM order_status_history osh
            LEFT JOIN users u ON u.id = osh.created_by
            WHERE osh.order_id = ?
            ORDER BY osh.created_at DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Get user
     */
    public function getUser(): ?User
    {
        if (!$this->user_id) {
            return null;
        }
        return User::find($this->user_id);
    }

    /**
     * Get formatted billing address
     */
    public function getFormattedBillingAddress(): string
    {
        $lines = [
            $this->billing_first_name . ' ' . $this->billing_last_name,
        ];

        if ($this->billing_company) {
            $lines[] = $this->billing_company;
        }

        $lines[] = $this->billing_address_1;

        if ($this->billing_address_2) {
            $lines[] = $this->billing_address_2;
        }

        $lines[] = $this->billing_city . ', ' . $this->billing_province . ' ' . $this->billing_postal_code;
        $lines[] = $this->billing_country;

        return implode("\n", $lines);
    }

    /**
     * Get formatted shipping address
     */
    public function getFormattedShippingAddress(): string
    {
        $lines = [
            $this->shipping_first_name . ' ' . $this->shipping_last_name,
        ];

        if ($this->shipping_company) {
            $lines[] = $this->shipping_company;
        }

        $lines[] = $this->shipping_address_1;

        if ($this->shipping_address_2) {
            $lines[] = $this->shipping_address_2;
        }

        $lines[] = $this->shipping_city . ', ' . $this->shipping_province . ' ' . $this->shipping_postal_code;
        $lines[] = $this->shipping_country;

        return implode("\n", $lines);
    }

    /**
     * Cancel order and restore stock
     */
    public function cancel(?string $reason = null): bool
    {
        if (in_array($this->status, ['delivered', 'cancelled'], true)) {
            return false;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // Restore stock
            $items = $this->getItems();
            foreach ($items as $item) {
                if ($item['product_id']) {
                    $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['product_id']]);
                }
            }

            $this->updateStatus('cancelled', $reason);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}
