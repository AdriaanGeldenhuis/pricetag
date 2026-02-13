<!-- Admin Order Detail -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">
            Placed on <?= date('d F Y \a\t H:i', strtotime($order['created_at'])) ?>
        </p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/orders') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Orders
        </a>
        <a href="<?= url('/account/orders/' . $order['id'] . '/invoice') ?>" target="_blank" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Print Invoice
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Main Content -->
    <div>
        <!-- Order Items -->
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Order Items</h3>
                <span class="text-muted"><?= count($items) ?> item(s)</span>
            </div>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;"></th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= e($item['image'] ?? '/assets/images/placeholder.jpg') ?>"
                                     alt="<?= e($item['name']) ?>" class="admin-table-image">
                            </td>
                            <td>
                                <div class="font-semibold"><?= e($item['name']) ?></div>
                                <?php if ($item['product_slug']): ?>
                                <a href="<?= url('/products/' . $item['product_slug']) ?>" target="_blank"
                                   class="text-muted" style="font-size: 0.75rem;">View Product</a>
                                <?php endif; ?>
                            </td>
                            <td><?= formatPrice($item['price']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td class="font-semibold"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="admin-card-footer">
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; width: 200px;">
                        <span class="text-muted">Subtotal:</span>
                        <span><?= formatPrice($order['subtotal']) ?></span>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; width: 200px;">
                        <span class="text-muted">Discount:</span>
                        <span style="color: var(--admin-success);">-<?= formatPrice($order['discount']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; width: 200px;">
                        <span class="text-muted">Shipping:</span>
                        <span><?= $order['shipping'] > 0 ? formatPrice($order['shipping']) : 'Free' ?></span>
                    </div>
                    <?php if ($order['tax'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; width: 200px;">
                        <span class="text-muted">Tax:</span>
                        <span><?= formatPrice($order['tax']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; width: 200px; padding-top: 0.5rem; border-top: 1px solid var(--admin-border);">
                        <span class="font-semibold">Total:</span>
                        <span class="font-semibold" style="font-size: 1.125rem;"><?= formatPrice($order['total']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Timeline</h3>
            </div>
            <div class="admin-card-body">
                <?php if (empty($timeline)): ?>
                <p class="text-muted">No status updates yet.</p>
                <?php else: ?>
                <div style="position: relative; padding-left: 1.5rem;">
                    <?php foreach ($timeline as $i => $event): ?>
                    <div style="position: relative; padding-bottom: 1.5rem; <?= $i === count($timeline) - 1 ? '' : 'border-left: 2px solid var(--admin-border); margin-left: -1.5rem; padding-left: 1.5rem;' ?>">
                        <div style="position: absolute; left: -1.75rem; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--admin-primary);"></div>
                        <div class="font-semibold"><?= ucfirst($event['status']) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <?= date('d M Y H:i', strtotime($event['created_at'])) ?>
                        </div>
                        <?php if ($event['comment']): ?>
                        <div style="margin-top: 0.5rem; padding: 0.5rem; background: var(--admin-bg); border-radius: var(--admin-radius); font-size: 0.875rem;">
                            <?= e($event['comment']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notes -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Notes</h3>
            </div>
            <div class="admin-card-body">
                <!-- Add Note Form -->
                <form method="POST" action="<?= url('/admin/orders/' . $order['id'] . '/note') ?>" style="margin-bottom: 1.5rem;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="admin-form-group">
                        <textarea name="note" class="admin-form-textarea" rows="3"
                                  placeholder="Add a note about this order..."></textarea>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <label class="admin-checkbox-group">
                            <input type="checkbox" name="customer_visible" value="1" class="admin-checkbox">
                            <span>Visible to customer</span>
                        </label>
                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Add Note</button>
                    </div>
                </form>

                <!-- Notes List -->
                <?php if (!empty($notes)): ?>
                <div style="border-top: 1px solid var(--admin-border); padding-top: 1rem;">
                    <?php foreach ($notes as $note): ?>
                    <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--admin-border);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span class="font-semibold"><?= e($note['first_name'] . ' ' . $note['last_name']) ?></span>
                            <span class="text-muted" style="font-size: 0.75rem;">
                                <?= date('d M Y H:i', strtotime($note['created_at'])) ?>
                            </span>
                        </div>
                        <p style="margin: 0;"><?= e($note['note']) ?></p>
                        <?php if ($note['is_customer_visible']): ?>
                        <span style="display: inline-block; margin-top: 0.5rem; font-size: 0.75rem; color: var(--admin-success);">
                            Visible to customer
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">No notes yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Status & Actions -->
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Order Status</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <span class="admin-badge <?= e($order['status']) ?>" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                        <?= ucfirst($order['status']) ?>
                    </span>
                    <?php
                    $paymentBadge = match($order['payment_status']) {
                        'paid' => ['class' => 'delivered', 'label' => 'Paid'],
                        'pending' => ['class' => 'pending', 'label' => 'Payment Pending'],
                        'failed' => ['class' => 'cancelled', 'label' => 'Payment Failed'],
                        'refunded' => ['class' => 'shipped', 'label' => 'Refunded'],
                        default => ['class' => 'inactive', 'label' => ucfirst($order['payment_status'])],
                    };
                    ?>
                    <span class="admin-badge <?= $paymentBadge['class'] ?>" style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                        <?= $paymentBadge['label'] ?>
                    </span>
                </div>

                <form method="POST" action="<?= url('/admin/orders/' . $order['id'] . '/status') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="admin-form-group">
                        <label class="admin-form-label">Update Status</label>
                        <select name="status" class="admin-form-select">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="refunded" <?= $order['status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>

                    <div id="shippingFields" style="display: none;">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Tracking Number</label>
                            <input type="text" name="tracking_number" value="<?= e($order['tracking_number'] ?? '') ?>"
                                   class="admin-form-input" placeholder="e.g., PTT123456789ZA">
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Carrier</label>
                            <select name="tracking_carrier" class="admin-form-select">
                                <option value="">Select Carrier</option>
                                <option value="post_office" <?= ($order['tracking_carrier'] ?? '') === 'post_office' ? 'selected' : '' ?>>SA Post Office</option>
                                <option value="courier_guy" <?= ($order['tracking_carrier'] ?? '') === 'courier_guy' ? 'selected' : '' ?>>The Courier Guy</option>
                                <option value="ram" <?= ($order['tracking_carrier'] ?? '') === 'ram' ? 'selected' : '' ?>>RAM Hand to Hand</option>
                                <option value="dawn_wing" <?= ($order['tracking_carrier'] ?? '') === 'dawn_wing' ? 'selected' : '' ?>>DawnWing</option>
                                <option value="fastway" <?= ($order['tracking_carrier'] ?? '') === 'fastway' ? 'selected' : '' ?>>Fastway</option>
                                <option value="other" <?= ($order['tracking_carrier'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Note (optional)</label>
                        <textarea name="note" class="admin-form-textarea" rows="2"
                                  placeholder="Reason for status change..."></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-checkbox-group">
                            <input type="checkbox" name="notify_customer" value="1" class="admin-checkbox" checked>
                            <span>Notify customer via email</span>
                        </label>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%;">
                        Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Customer -->
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Customer</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <span class="admin-user-avatar">
                        <?= strtoupper(substr($order['billing_first_name'], 0, 1) . substr($order['billing_last_name'], 0, 1)) ?>
                    </span>
                    <div>
                        <div class="font-semibold"><?= e($order['billing_first_name'] . ' ' . $order['billing_last_name']) ?></div>
                        <?php if ($customer): ?>
                        <a href="<?= url('/admin/customers/' . $customer['id']) ?>" class="text-muted" style="font-size: 0.75rem;">
                            View Profile
                        </a>
                        <?php else: ?>
                        <span class="text-muted" style="font-size: 0.75rem;">Guest</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($customer): ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; padding: 0.75rem; background: var(--admin-bg); border-radius: var(--admin-radius); margin-bottom: 1rem;">
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem;">Total Orders</div>
                        <div class="font-semibold"><?= $customer['order_count'] ?></div>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size: 0.75rem;">Total Spent</div>
                        <div class="font-semibold"><?= formatPrice($customer['total_spent'] ?? 0) ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <div style="font-size: 0.875rem;">
                    <div style="margin-bottom: 0.5rem;">
                        <span class="text-muted">Email:</span>
                        <a href="mailto:<?= e($order['customer_email']) ?>"><?= e($order['customer_email']) ?></a>
                    </div>
                    <?php if ($order['customer_phone'] ?? null): ?>
                    <div>
                        <span class="text-muted">Phone:</span>
                        <a href="tel:<?= e($order['customer_phone']) ?>"><?= e($order['customer_phone']) ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Shipping Address</h3>
            </div>
            <div class="admin-card-body" style="font-size: 0.875rem;">
                <p style="margin: 0;">
                    <?= e($order['shipping_first_name'] . ' ' . $order['shipping_last_name']) ?><br>
                    <?= e($order['shipping_address_1']) ?><br>
                    <?php if ($order['shipping_address_2']): ?>
                    <?= e($order['shipping_address_2']) ?><br>
                    <?php endif; ?>
                    <?= e($order['shipping_city']) ?>, <?= e($order['shipping_province']) ?><br>
                    <?= e($order['shipping_postal_code']) ?>
                </p>
            </div>
        </div>

        <!-- Billing Address -->
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Billing Address</h3>
            </div>
            <div class="admin-card-body" style="font-size: 0.875rem;">
                <p style="margin: 0;">
                    <?= e($order['billing_first_name'] . ' ' . $order['billing_last_name']) ?><br>
                    <?= e($order['billing_address_1']) ?><br>
                    <?php if ($order['billing_address_2']): ?>
                    <?= e($order['billing_address_2']) ?><br>
                    <?php endif; ?>
                    <?= e($order['billing_city']) ?>, <?= e($order['billing_province']) ?><br>
                    <?= e($order['billing_postal_code']) ?>
                </p>
            </div>
        </div>

        <!-- Payment -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Payment Details</h3>
            </div>
            <div class="admin-card-body" style="font-size: 0.875rem;">
                <div style="display: grid; gap: 0.5rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span class="text-muted">Method:</span>
                        <span><?= ucfirst($order['payment_method'] ?? 'N/A') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span class="text-muted">Status:</span>
                        <span class="admin-badge <?= $paymentBadge['class'] ?>"><?= $paymentBadge['label'] ?></span>
                    </div>
                    <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                    <div style="padding-top: 0.5rem; border-top: 1px solid var(--admin-border); margin-top: 0.5rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span class="text-muted">Transaction ID:</span>
                            <span style="font-family: monospace; font-size: 0.75rem;"><?= e($payment['transaction_id'] ?? '-') ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span class="text-muted">Amount:</span>
                            <span><?= formatPrice($payment['amount']) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span class="text-muted">Date:</span>
                            <span><?= date('d M Y H:i', strtotime($payment['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const shippingFields = document.getElementById('shippingFields');
    shippingFields.style.display = this.value === 'shipped' ? 'block' : 'none';
});

// Show on load if already shipped
if (document.querySelector('select[name="status"]').value === 'shipped') {
    document.getElementById('shippingFields').style.display = 'block';
}
</script>
