<!-- Admin Order Detail -->
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="<?= url('/admin/orders') ?>" class="text-sm text-muted hover:text-primary">&larr; Back to Orders</a>
        <h1 class="admin-page-title mb-0">Order #<?= e($order['order_number']) ?></h1>
        <p class="text-muted"><?= date('d M Y \a\t H:i', strtotime($order['created_at'])) ?></p>
    </div>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Print
        </button>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Order Items -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Order Items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <span class="font-medium"><?= e($item['product_name'] ?? $item['name']) ?></span>
                                <span class="text-xs text-muted block">SKU: <?= e($item['sku']) ?></span>
                                <?php if ($item['variant_name']): ?>
                                <span class="text-xs text-muted"><?= e($item['variant_name']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= formatPrice($item['price']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-right font-medium"><?= formatPrice($item['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">Subtotal</td>
                            <td class="text-right"><?= formatPrice($order['subtotal']) ?></td>
                        </tr>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <tr>
                            <td colspan="3" class="text-right">
                                Discount
                                <?php if ($order['coupon_code']): ?>
                                <span class="badge badge-accent ml-1"><?= e($order['coupon_code']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right text-success">-<?= formatPrice($order['discount_amount']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="3" class="text-right">Shipping</td>
                            <td class="text-right"><?= $order['shipping_amount'] > 0 ? formatPrice($order['shipping_amount']) : 'Free' ?></td>
                        </tr>
                        <?php if ($order['tax_amount'] > 0): ?>
                        <tr>
                            <td colspan="3" class="text-right">Tax (VAT)</td>
                            <td class="text-right"><?= formatPrice($order['tax_amount']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="font-bold">
                            <td colspan="3" class="text-right">Total</td>
                            <td class="text-right"><?= formatPrice($order['total']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Customer & Addresses -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Billing Address -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Billing Address</h2>
                </div>
                <div class="card-body">
                    <p class="font-medium"><?= e($order['billing_first_name']) ?> <?= e($order['billing_last_name']) ?></p>
                    <?php if ($order['billing_company']): ?>
                    <p><?= e($order['billing_company']) ?></p>
                    <?php endif; ?>
                    <p><?= e($order['billing_address_1']) ?></p>
                    <?php if ($order['billing_address_2'] ?? null): ?>
                    <p><?= e($order['billing_address_2']) ?></p>
                    <?php endif; ?>
                    <p><?= e($order['billing_city']) ?>, <?= e($order['billing_province']) ?> <?= e($order['billing_postal_code']) ?></p>
                    <p><?= e($order['billing_country']) ?></p>
                    <p class="mt-2"><?= e($order['customer_email']) ?></p>
                    <?php if ($order['customer_phone'] ?? null): ?>
                    <p><?= e($order['customer_phone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Shipping Address</h2>
                </div>
                <div class="card-body">
                    <p class="font-medium"><?= e($order['shipping_first_name']) ?> <?= e($order['shipping_last_name']) ?></p>
                    <?php if ($order['shipping_company']): ?>
                    <p><?= e($order['shipping_company']) ?></p>
                    <?php endif; ?>
                    <p><?= e($order['shipping_address_1']) ?></p>
                    <?php if ($order['shipping_address_2'] ?? null): ?>
                    <p><?= e($order['shipping_address_2']) ?></p>
                    <?php endif; ?>
                    <p><?= e($order['shipping_city']) ?>, <?= e($order['shipping_province']) ?> <?= e($order['shipping_postal_code']) ?></p>
                    <p><?= e($order['shipping_country']) ?></p>
                    <?php if ($order['customer_phone'] ?? null): ?>
                    <p class="mt-2"><?= e($order['customer_phone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <?php if (!empty($payments)): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Payment History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= date('d M Y H:i', strtotime($payment['created_at'])) ?></td>
                            <td><?= ucfirst($payment['method'] ?? $payment['gateway'] ?? '-') ?></td>
                            <td class="text-sm"><?= e($payment['transaction_id'] ?? '-') ?></td>
                            <td class="font-medium"><?= formatPrice($payment['amount']) ?></td>
                            <td>
                                <?php
                                $pColors = ['pending' => 'warning', 'completed' => 'success', 'failed' => 'danger', 'refunded' => 'neutral'];
                                $pColor = $pColors[$payment['status']] ?? 'neutral';
                                ?>
                                <span class="badge badge-<?= $pColor ?>"><?= ucfirst($payment['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status History -->
        <?php if (!empty($statusHistory)): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Status History</h2>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <?php foreach ($statusHistory as $history): ?>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-2 h-2 rounded-full bg-primary mt-2"></div>
                        </div>
                        <div>
                            <p class="font-medium">
                                <?= ucfirst($history['status']) ?>
                            </p>
                            <?php if ($history['comment']): ?>
                            <p class="text-sm text-muted"><?= e($history['comment']) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-muted">
                                <?= date('d M Y H:i', strtotime($history['created_at'])) ?>
                                <?php if ($history['first_name']): ?>
                                by <?= e($history['first_name']) ?> <?= e($history['last_name']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Status Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Order Status</h2>
            </div>
            <div class="card-body">
                <?php
                $statusColors = [
                    'pending' => 'warning',
                    'processing' => 'primary',
                    'paid' => 'success',
                    'shipped' => 'primary',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    'refunded' => 'neutral',
                ];
                $color = $statusColors[$order['status']] ?? 'neutral';
                ?>
                <div class="text-center mb-4">
                    <span class="badge badge-<?= $color ?> badge-lg"><?= ucfirst($order['status']) ?></span>
                </div>

                <form action="<?= url('/admin/orders/' . $order['id'] . '/status') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="status" class="form-label">Update Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="refunded" <?= $order['status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes (optional)</label>
                        <textarea id="notes" name="notes" rows="2" class="form-input" placeholder="Add a note about this status change..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Update Status</button>
                </form>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Payment</h2>
            </div>
            <div class="card-body">
                <?php
                $paymentColors = [
                    'pending' => 'warning',
                    'paid' => 'success',
                    'failed' => 'danger',
                    'refunded' => 'neutral',
                ];
                $pColor = $paymentColors[$order['payment_status']] ?? 'neutral';
                ?>
                <div class="flex justify-between items-center mb-2">
                    <span>Status</span>
                    <span class="badge badge-<?= $pColor ?>"><?= ucfirst($order['payment_status']) ?></span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span>Method</span>
                    <span><?= ucfirst($order['payment_method'] ?? '-') ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Total</span>
                    <span class="font-bold"><?= formatPrice($order['total']) ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Customer</h2>
            </div>
            <div class="card-body">
                <?php if ($order['user_id']): ?>
                <p class="font-medium"><?= e($order['first_name']) ?> <?= e($order['last_name']) ?></p>
                <p class="text-sm text-muted"><?= e($order['email']) ?></p>
                <?php if ($order['user_phone']): ?>
                <p class="text-sm text-muted"><?= e($order['user_phone']) ?></p>
                <?php endif; ?>
                <a href="<?= url('/admin/customers/' . $order['user_id']) ?>" class="btn btn-outline btn-sm mt-3">
                    View Customer
                </a>
                <?php else: ?>
                <p class="text-muted">Guest checkout</p>
                <p class="font-medium mt-2"><?= e($order['billing_first_name']) ?> <?= e($order['billing_last_name']) ?></p>
                <p class="text-sm text-muted"><?= e($order['customer_email']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Notes -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Add Note</h2>
            </div>
            <div class="card-body">
                <form action="<?= url('/admin/orders/' . $order['id'] . '/note') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <textarea name="note" rows="3" class="form-input" placeholder="Add a note..." required></textarea>
                    </div>
                    <label class="flex items-center gap-2 mb-3">
                        <input type="checkbox" name="customer_visible" value="1" class="form-checkbox">
                        <span class="text-sm">Visible to customer</span>
                    </label>
                    <button type="submit" class="btn btn-outline w-full">Add Note</button>
                </form>
            </div>
        </div>
    </div>
</div>
