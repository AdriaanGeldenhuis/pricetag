<!-- Account - Order Detail -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <!-- Breadcrumb -->
                <div class="account-breadcrumb">
                    <a href="<?= url('/account') ?>">Account</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <a href="<?= url('/account/orders') ?>">Orders</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <span>#<?= e($order->order_number) ?></span>
                </div>

                <!-- Order Header -->
                <div class="order-detail-header">
                    <div>
                        <h1 class="account-page-title">Order #<?= e($order->order_number) ?></h1>
                        <div class="order-detail-meta">
                            <div class="order-detail-meta-item">
                                <span class="order-detail-meta-label">Placed On</span>
                                <span class="order-detail-meta-value"><?= date('d M Y, H:i', strtotime($order->created_at)) ?></span>
                            </div>
                            <div class="order-detail-meta-item">
                                <span class="order-detail-meta-label">Status</span>
                                <span class="order-status status-<?= e($order->status) ?>"><?= ucfirst($order->status) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="order-detail-actions">
                        <a href="<?= url('/account/orders/' . $order->id . '/invoice') ?>" class="btn btn-outline" target="_blank">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Download Invoice
                        </a>
                        <?php if ($order->status === 'delivered'): ?>
                        <a href="<?= url('/contact?subject=Return%20Request%20Order%20' . $order->order_number) ?>" class="btn btn-ghost">
                            Request Return
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-6">
                    <!-- Main Column -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Order Items -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="font-semibold">Order Items</h2>
                            </div>
                            <div class="card-body p-0">
                                <div class="order-items-list">
                                    <?php foreach ($items as $item): ?>
                                    <div class="order-item-row">
                                        <div class="order-item-image">
                                            <?php if ($item['image']): ?>
                                            <img src="<?= url('storage/uploads/' . e($item['image'])) ?>" alt="">
                                            <?php endif; ?>
                                        </div>
                                        <div class="order-item-details">
                                            <h4 class="order-item-name"><?= e($item['name']) ?></h4>
                                            <?php if (!empty($item['variant'])): ?>
                                            <p class="order-item-variant"><?= e($item['variant']) ?></p>
                                            <?php endif; ?>
                                            <p class="order-item-sku">SKU: <?= e($item['sku'] ?? 'N/A') ?></p>
                                        </div>
                                        <div class="order-item-quantity">
                                            <span class="text-muted">Qty:</span> <?= $item['quantity'] ?>
                                        </div>
                                        <div class="order-item-price">
                                            <?= formatPrice($item['price'] * $item['quantity']) ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Order Timeline -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="font-semibold">Order Timeline</h2>
                            </div>
                            <div class="card-body">
                                <div class="order-timeline">
                                    <?php
                                    $statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
                                    $currentIndex = array_search($order->status, $statusOrder);
                                    $statusLabels = [
                                        'pending' => ['Order Placed', 'Your order has been received'],
                                        'processing' => ['Processing', 'We are preparing your order'],
                                        'shipped' => ['Shipped', 'Your order is on its way'],
                                        'delivered' => ['Delivered', 'Order has been delivered'],
                                    ];
                                    ?>

                                    <?php if (!empty($statusHistory)): ?>
                                    <?php foreach ($statusHistory as $history): ?>
                                    <div class="order-timeline-item completed">
                                        <div class="order-timeline-dot"></div>
                                        <div class="order-timeline-date"><?= date('d M Y, H:i', strtotime($history['created_at'])) ?></div>
                                        <div class="order-timeline-title"><?= ucfirst($history['status']) ?></div>
                                        <?php if (!empty($history['note'])): ?>
                                        <p class="order-timeline-note"><?= e($history['note']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <?php foreach ($statusOrder as $index => $status): ?>
                                    <?php
                                    $isCompleted = $index < $currentIndex;
                                    $isActive = $index === $currentIndex;
                                    ?>
                                    <div class="order-timeline-item <?= $isCompleted ? 'completed' : ($isActive ? 'active' : '') ?>">
                                        <div class="order-timeline-dot"></div>
                                        <div class="order-timeline-title"><?= $statusLabels[$status][0] ?></div>
                                        <p class="order-timeline-note"><?= $statusLabels[$status][1] ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ($order->tracking_number): ?>
                                <div class="order-tracking mt-6 p-4 bg-primary-50 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-sm font-medium">Tracking Number</span>
                                            <p class="font-mono font-semibold"><?= e($order->tracking_number) ?></p>
                                        </div>
                                        <a href="<?= e($order->tracking_url ?? '#') ?>" target="_blank" class="btn btn-sm btn-primary">
                                            Track Package
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Order Summary -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="font-semibold">Order Summary</h2>
                            </div>
                            <div class="card-body">
                                <div class="order-summary">
                                    <div class="order-summary-row">
                                        <span>Subtotal</span>
                                        <span><?= formatPrice($order->subtotal) ?></span>
                                    </div>
                                    <?php if ($order->discount > 0): ?>
                                    <div class="order-summary-row text-success">
                                        <span>Discount</span>
                                        <span>-<?= formatPrice($order->discount) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="order-summary-row">
                                        <span>Shipping</span>
                                        <span><?= $order->shipping > 0 ? formatPrice($order->shipping) : 'Free' ?></span>
                                    </div>
                                    <?php if ($order->tax > 0): ?>
                                    <div class="order-summary-row">
                                        <span>VAT (15%)</span>
                                        <span><?= formatPrice($order->tax) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="order-summary-row order-summary-total">
                                        <span>Total</span>
                                        <span><?= formatPrice($order->total) ?></span>
                                    </div>
                                </div>

                                <div class="order-payment mt-4 pt-4 border-t">
                                    <span class="text-sm text-muted">Payment Method</span>
                                    <p class="font-medium">
                                        <?php if ($order->payment_method === 'card'): ?>
                                        Credit Card **** <?= e($order->card_last_four ?? '****') ?>
                                        <?php else: ?>
                                        <?= ucfirst($order->payment_method ?? 'Card') ?>
                                        <?php endif; ?>
                                    </p>
                                    <span class="text-xs text-success">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <?= ucfirst($order->payment_status) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="font-semibold">Shipping Address</h2>
                            </div>
                            <div class="card-body">
                                <p class="font-medium"><?= e($order->shipping_first_name . ' ' . $order->shipping_last_name) ?></p>
                                <p class="text-sm text-muted">
                                    <?= e($order->shipping_address_1) ?><br>
                                    <?php if ($order->shipping_address_2): ?>
                                    <?= e($order->shipping_address_2) ?><br>
                                    <?php endif; ?>
                                    <?= e($order->shipping_city) ?>, <?= e($order->shipping_province) ?> <?= e($order->shipping_postal_code) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="font-semibold">Billing Address</h2>
                            </div>
                            <div class="card-body">
                                <p class="font-medium"><?= e($order->billing_first_name . ' ' . $order->billing_last_name) ?></p>
                                <p class="text-sm text-muted">
                                    <?= e($order->billing_address_1) ?><br>
                                    <?php if ($order->billing_address_2): ?>
                                    <?= e($order->billing_address_2) ?><br>
                                    <?php endif; ?>
                                    <?= e($order->billing_city) ?>, <?= e($order->billing_province) ?> <?= e($order->billing_postal_code) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Need Help -->
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="font-semibold mb-2">Need Help?</h3>
                                <p class="text-sm text-muted mb-4">Have questions about your order?</p>
                                <a href="<?= url('/contact?order=' . $order->order_number) ?>" class="btn btn-outline btn-block">
                                    Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
/* Order Items List */
.order-items-list {
    display: flex;
    flex-direction: column;
}

.order-item-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--color-border);
}

.order-item-row:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 4rem;
    height: 4rem;
    border-radius: var(--radius-md);
    background: var(--color-background-alt);
    overflow: hidden;
    flex-shrink: 0;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-details {
    flex: 1;
    min-width: 0;
}

.order-item-name {
    font-weight: 600;
    margin: 0 0 0.25rem;
}

.order-item-variant {
    font-size: 0.875rem;
    color: var(--color-text-muted);
    margin: 0 0 0.25rem;
}

.order-item-sku {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin: 0;
}

.order-item-quantity {
    text-align: center;
    min-width: 60px;
}

.order-item-price {
    font-weight: 600;
    text-align: right;
    min-width: 80px;
}

/* Order Summary */
.order-summary {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.order-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
}

.order-summary-total {
    padding-top: 0.75rem;
    margin-top: 0.5rem;
    border-top: 1px solid var(--color-border);
    font-weight: 700;
    font-size: 1rem;
}

/* Order Detail Actions */
.order-detail-actions {
    display: flex;
    gap: 0.75rem;
}

@media (max-width: 640px) {
    .order-detail-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .order-detail-actions {
        width: 100%;
    }

    .order-detail-actions .btn {
        flex: 1;
    }

    .order-item-row {
        flex-wrap: wrap;
    }

    .order-item-quantity,
    .order-item-price {
        min-width: auto;
    }
}
</style>
