<!-- Checkout Success Page -->
<div class="checkout-success-page">
    <div class="container">
        <!-- Success Animation -->
        <div class="success-header">
            <div class="success-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h1 class="success-title">Thank You for Your Order!</h1>
            <p class="success-message">
                Your order <strong>#<?= e($order->order_number) ?></strong> has been confirmed.
            </p>
            <p class="success-email">
                A confirmation email has been sent to <strong><?= e($order->email) ?></strong>
            </p>
        </div>

        <div class="success-grid">
            <!-- Order Details -->
            <div class="success-main">
                <!-- Order Items -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold">Order Summary</h2>
                    </div>
                    <div class="card-body">
                        <div class="order-items">
                            <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <?php if ($item['image']): ?>
                                    <img src="<?= url('storage/uploads/' . e($item['image'])) ?>" alt="">
                                    <?php endif; ?>
                                </div>
                                <div class="order-item-details">
                                    <p class="order-item-name"><?= e($item['name']) ?></p>
                                    <?php if (!empty($item['variant'])): ?>
                                    <p class="order-item-variant"><?= e($item['variant']) ?></p>
                                    <?php endif; ?>
                                    <p class="order-item-qty">Qty: <?= $item['quantity'] ?></p>
                                </div>
                                <p class="order-item-price"><?= formatPrice($item['price'] * $item['quantity']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-totals">
                            <div class="order-total-row">
                                <span>Subtotal</span>
                                <span><?= formatPrice($order->subtotal) ?></span>
                            </div>
                            <?php if ($order->discount > 0): ?>
                            <div class="order-total-row order-discount">
                                <span>Discount</span>
                                <span>-<?= formatPrice($order->discount) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="order-total-row">
                                <span>Shipping</span>
                                <span><?= $order->shipping > 0 ? formatPrice($order->shipping) : 'Free' ?></span>
                            </div>
                            <?php if ($order->tax > 0): ?>
                            <div class="order-total-row">
                                <span>VAT (15%)</span>
                                <span><?= formatPrice($order->tax) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="order-total-row order-total-final">
                                <span>Total Paid</span>
                                <span><?= formatPrice($order->total) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold">Delivery Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="delivery-info-grid">
                            <div class="delivery-info-section">
                                <h3>Shipping Address</h3>
                                <p>
                                    <?= e($order->shipping_first_name . ' ' . $order->shipping_last_name) ?><br>
                                    <?= e($order->shipping_address_1) ?><br>
                                    <?php if ($order->shipping_address_2): ?>
                                    <?= e($order->shipping_address_2) ?><br>
                                    <?php endif; ?>
                                    <?= e($order->shipping_city) ?>, <?= e($order->shipping_province) ?> <?= e($order->shipping_postal_code) ?>
                                </p>
                            </div>
                            <div class="delivery-info-section">
                                <h3>Shipping Method</h3>
                                <p>
                                    <?= e($order->shipping_method === 'express' ? 'Express Delivery' : 'Standard Delivery') ?><br>
                                    <span class="text-muted">
                                        <?= $order->shipping_method === 'express' ? '1-2 business days' : '3-5 business days' ?>
                                    </span>
                                </p>
                            </div>
                            <div class="delivery-info-section">
                                <h3>Contact</h3>
                                <p>
                                    <?= e($order->email) ?><br>
                                    <?php if ($order->phone): ?>
                                    <?= e($order->phone) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="delivery-info-section">
                                <h3>Payment Method</h3>
                                <p>
                                    <?php if ($order->payment_method === 'card'): ?>
                                    Credit/Debit Card<br>
                                    <span class="text-muted">**** <?= e($order->card_last_four ?? '****') ?></span>
                                    <?php else: ?>
                                    Instant EFT
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($order->notes): ?>
                        <div class="delivery-notes">
                            <h3>Delivery Notes</h3>
                            <p><?= e($order->notes) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- What's Next -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold">What's Next?</h2>
                    </div>
                    <div class="card-body">
                        <div class="next-steps">
                            <div class="next-step completed">
                                <div class="next-step-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                                <div class="next-step-content">
                                    <h4>Order Confirmed</h4>
                                    <p>We've received your order and payment</p>
                                </div>
                            </div>
                            <div class="next-step <?= $order->status === 'processing' ? 'active' : '' ?>">
                                <div class="next-step-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    </svg>
                                </div>
                                <div class="next-step-content">
                                    <h4>Processing</h4>
                                    <p>We're preparing your items for shipment</p>
                                </div>
                            </div>
                            <div class="next-step">
                                <div class="next-step-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="1" y="3" width="15" height="13"></rect>
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                    </svg>
                                </div>
                                <div class="next-step-content">
                                    <h4>Shipped</h4>
                                    <p>Your order is on its way to you</p>
                                </div>
                            </div>
                            <div class="next-step">
                                <div class="next-step-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                    </svg>
                                </div>
                                <div class="next-step-content">
                                    <h4>Delivered</h4>
                                    <p>Package arrives at your doorstep</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="success-sidebar">
                <!-- Order Info Card -->
                <div class="card success-order-card">
                    <div class="card-body">
                        <div class="success-order-number">
                            <span class="label">Order Number</span>
                            <span class="value">#<?= e($order->order_number) ?></span>
                        </div>
                        <div class="success-order-date">
                            <span class="label">Order Date</span>
                            <span class="value"><?= date('d M Y, H:i', strtotime($order->created_at)) ?></span>
                        </div>
                        <div class="success-order-status">
                            <span class="label">Status</span>
                            <span class="order-status-badge status-<?= e($order->status) ?>">
                                <?= ucfirst($order->status) ?>
                            </span>
                        </div>

                        <div class="success-actions">
                            <?php if (auth()): ?>
                            <a href="<?= url('/account/orders/' . $order->id) ?>" class="btn btn-primary btn-block">
                                View Order Details
                            </a>
                            <a href="<?= url('/account/orders/' . $order->id . '/invoice') ?>" class="btn btn-outline btn-block">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Download Invoice
                            </a>
                            <?php endif; ?>
                            <a href="<?= url('/products') ?>" class="btn btn-ghost btn-block">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Need Help Card -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="font-semibold mb-3">Need Help?</h3>
                        <p class="text-sm text-muted mb-4">
                            If you have any questions about your order, we're here to help.
                        </p>
                        <div class="help-options">
                            <a href="<?= url('/contact') ?>" class="help-option">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span>Contact Support</span>
                            </a>
                            <a href="<?= url('/page/faq') ?>" class="help-option">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                                <span>FAQ</span>
                            </a>
                            <a href="<?= url('/page/shipping') ?>" class="help-option">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                                <span>Shipping Info</span>
                            </a>
                            <a href="<?= url('/page/returns') ?>" class="help-option">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="1 4 1 10 7 10"></polyline>
                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                </svg>
                                <span>Returns & Refunds</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Share Order (Social Proof) -->
                <?php if (!auth()): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="font-semibold mb-2">Create an Account</h3>
                        <p class="text-sm text-muted mb-4">
                            Track your order, save your addresses, and checkout faster next time.
                        </p>
                        <a href="<?= url('/register?order=' . $order->order_number) ?>" class="btn btn-outline btn-block">
                            Create Account
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Checkout Success Page Styles */
.checkout-success-page {
    background: var(--color-background-alt);
    min-height: 100vh;
    padding: 3rem 0;
}

/* Success Header */
.success-header {
    text-align: center;
    margin-bottom: 3rem;
}

.success-icon {
    width: 5rem;
    height: 5rem;
    margin: 0 auto 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-success-100);
    border-radius: 50%;
    color: var(--color-success);
    animation: successPulse 0.6s ease-out;
}

.success-icon svg {
    width: 2.5rem;
    height: 2.5rem;
    stroke-dasharray: 50;
    animation: successCheck 0.5s ease-out 0.3s both;
}

@keyframes successPulse {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes successCheck {
    0% { stroke-dashoffset: 50; }
    100% { stroke-dashoffset: 0; }
}

.success-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--color-text);
}

.success-message {
    font-size: 1.125rem;
    color: var(--color-text-muted);
    margin-bottom: 0.25rem;
}

.success-email {
    font-size: 0.875rem;
    color: var(--color-text-muted);
}

/* Success Grid */
.success-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 1024px) {
    .success-grid {
        grid-template-columns: 1fr 360px;
    }
}

.success-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.success-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Order Items */
.order-items {
    display: grid;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--color-border);
    margin-bottom: 1rem;
}

.order-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
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
    font-weight: 500;
    margin: 0 0 0.25rem;
}

.order-item-variant {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin: 0 0 0.25rem;
}

.order-item-qty {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin: 0;
}

.order-item-price {
    font-weight: 600;
    margin: 0;
}

/* Order Totals */
.order-totals {
    display: grid;
    gap: 0.5rem;
}

.order-total-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
}

.order-discount {
    color: var(--color-success);
}

.order-total-final {
    padding-top: 0.75rem;
    margin-top: 0.5rem;
    border-top: 1px solid var(--color-border);
    font-size: 1.125rem;
    font-weight: 700;
}

/* Delivery Info */
.delivery-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

@media (max-width: 640px) {
    .delivery-info-grid {
        grid-template-columns: 1fr;
    }
}

.delivery-info-section h3 {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--color-text-muted);
    margin-bottom: 0.5rem;
}

.delivery-info-section p {
    font-size: 0.875rem;
    margin: 0;
    line-height: 1.5;
}

.delivery-notes {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--color-border);
}

.delivery-notes h3 {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--color-text-muted);
    margin-bottom: 0.5rem;
}

.delivery-notes p {
    font-size: 0.875rem;
    margin: 0;
    padding: 0.75rem;
    background: var(--color-background-alt);
    border-radius: var(--radius-md);
}

/* Next Steps */
.next-steps {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.next-step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    position: relative;
    opacity: 0.5;
}

.next-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 1.25rem;
    top: 3.5rem;
    bottom: 0;
    width: 2px;
    background: var(--color-border);
}

.next-step.completed,
.next-step.active {
    opacity: 1;
}

.next-step.completed::after {
    background: var(--color-success);
}

.next-step-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-neutral-100);
    border-radius: 50%;
    flex-shrink: 0;
    z-index: 1;
}

.next-step-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}

.next-step.completed .next-step-icon {
    background: var(--color-success);
    color: white;
}

.next-step.active .next-step-icon {
    background: var(--color-primary);
    color: white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 var(--color-primary-300); }
    50% { box-shadow: 0 0 0 8px transparent; }
}

.next-step-content h4 {
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0 0 0.25rem;
}

.next-step-content p {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin: 0;
}

/* Success Order Card */
.success-order-card {
    background: var(--color-background);
}

.success-order-number,
.success-order-date,
.success-order-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--color-border);
}

.success-order-status {
    border-bottom: none;
    margin-bottom: 1rem;
}

.success-order-number .label,
.success-order-date .label,
.success-order-status .label {
    font-size: 0.875rem;
    color: var(--color-text-muted);
}

.success-order-number .value,
.success-order-date .value {
    font-weight: 600;
}

.order-status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.order-status-badge.status-pending {
    background: var(--color-warning-100);
    color: var(--color-warning-700);
}

.order-status-badge.status-processing {
    background: var(--color-primary-100);
    color: var(--color-primary-700);
}

.order-status-badge.status-shipped {
    background: var(--color-info-100);
    color: var(--color-info-700);
}

.order-status-badge.status-delivered,
.order-status-badge.status-completed {
    background: var(--color-success-100);
    color: var(--color-success-700);
}

.success-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Help Options */
.help-options {
    display: grid;
    gap: 0.5rem;
}

.help-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: var(--radius-md);
    color: var(--color-text);
    transition: background 0.2s;
}

.help-option:hover {
    background: var(--color-background-alt);
}

.help-option svg {
    color: var(--color-text-muted);
}

.help-option span {
    font-size: 0.875rem;
}

/* Print Styles */
@media print {
    .checkout-success-page {
        background: white;
        padding: 0;
    }

    .success-sidebar,
    .next-steps,
    .success-actions,
    .help-options {
        display: none;
    }

    .success-grid {
        display: block;
    }

    .card {
        box-shadow: none;
        border: 1px solid #ddd;
        break-inside: avoid;
    }
}
</style>

<script>
// Track conversion event (for analytics)
document.addEventListener('DOMContentLoaded', function() {
    // Google Analytics / GTM
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', {
            transaction_id: '<?= e($order->order_number) ?>',
            value: <?= $order->total ?>,
            currency: 'ZAR',
            items: <?= json_encode(array_map(function($item) {
                return [
                    'item_id' => $item['product_id'],
                    'item_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity']
                ];
            }, $items)) ?>
        });
    }

    // Facebook Pixel
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', {
            value: <?= $order->total ?>,
            currency: 'ZAR',
            content_ids: <?= json_encode(array_column($items, 'product_id')) ?>,
            content_type: 'product'
        });
    }

    // Clear any checkout-related data from localStorage
    try {
        localStorage.removeItem('checkout_step');
        localStorage.removeItem('checkout_form_data');
    } catch (e) {}
});
</script>
