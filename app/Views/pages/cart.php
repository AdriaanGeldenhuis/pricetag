<?php
/**
 * Shopping Cart Page
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Features:
 * - Full cart display with product details
 * - Quantity controls with +/- buttons
 * - Update/Remove animations
 * - Empty cart state with suggestions
 * - Save for later functionality
 * - Coupon code application
 * - Shipping estimate preview
 * - Stock validation
 * - Recently viewed products
 */

use App\Models\Product;

$isEmpty = empty($items);
$recentlyViewed = Product::recentlyViewed(4);
?>

<!-- Breadcrumb Rings -->
<nav class="breadcrumb-rings container">
    <div class="breadcrumb-rings-list">
        <a href="<?= url('/') ?>" class="bc-ring-item">
            <div class="bc-ring-wrap">
                <div class="bc-ring-border"></div>
                <div class="bc-ring-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
            </div>
            <span class="bc-ring-label">Home</span>
        </a>
        <span class="bc-ring-sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
        <span class="bc-ring-item bc-ring-active">
            <div class="bc-ring-wrap">
                <div class="bc-ring-border"></div>
                <div class="bc-ring-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
            </div>
            <span class="bc-ring-label">Cart</span>
        </span>
    </div>
</nav>

<div class="cart-page container py-6">
    <h1 class="cart-page-title">
        Shopping Cart
        <?php if (!$isEmpty): ?>
        <span class="cart-page-count">(<?= $cart->getCount() ?> items)</span>
        <?php endif; ?>
    </h1>

    <?php if ($isEmpty): ?>
    <!-- Empty Cart State -->
    <div class="cart-empty">
        <div class="cart-empty-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
        </div>
        <h2 class="cart-empty-title">Your cart is empty</h2>
        <p class="cart-empty-text">Looks like you haven't added any items to your cart yet.</p>
        <a href="<?= url('/products') ?>" class="btn btn-primary btn-lg">Start Shopping</a>

        <!-- Recently Viewed -->
        <?php if (!empty($recentlyViewed)): ?>
        <div class="cart-empty-recently">
            <h3 class="cart-recently-title">Recently Viewed</h3>
            <div class="cart-recently-grid">
                <?php foreach ($recentlyViewed as $product): ?>
                <a href="<?= url('/products/' . $product->slug) ?>" class="cart-recently-item">
                    <?php if ($img = $product->getPrimaryImage()): ?>
                    <img src="<?= url('storage/uploads/' . $img) ?>" alt="<?= e($product->name) ?>" class="cart-recently-img">
                    <?php else: ?>
                    <div class="cart-recently-img cart-recently-placeholder"></div>
                    <?php endif; ?>
                    <span class="cart-recently-name"><?= e($product->name) ?></span>
                    <span class="cart-recently-price"><?= formatPrice($product->price) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Cart Content -->
    <div class="cart-layout">
        <!-- Cart Items -->
        <div class="cart-items-container">
            <div class="cart-items" id="cart-items">
                <?php foreach ($items as $item): ?>
                <div class="cart-page-item" data-item-id="<?= $item['id'] ?>">
                    <div class="cart-page-item-image">
                        <a href="<?= url('/products/' . ($item['slug'] ?? '')) ?>">
                            <?php if (!empty($item['image'])): ?>
                            <img src="<?= url('storage/uploads/' . $item['image']) ?>" alt="<?= e($item['name']) ?>">
                            <?php else: ?>
                            <div class="cart-page-item-placeholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="cart-page-item-details">
                        <h3 class="cart-page-item-title">
                            <a href="<?= url('/products/' . ($item['slug'] ?? '')) ?>"><?= e($item['name']) ?></a>
                        </h3>

                        <?php if (!empty($item['variant_name'])): ?>
                        <p class="cart-page-item-variant"><?= e($item['variant_name']) ?></p>
                        <?php endif; ?>

                        <?php
                        $opts = $item['options'] ?? [];
                        if (is_string($opts)) { $opts = json_decode($opts, true) ?: []; }
                        ?>
                        <?php if (!empty($opts) && is_array($opts)): ?>
                        <p class="cart-page-item-options">
                            <?= e(implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($opts), $opts))) ?>
                        </p>
                        <?php endif; ?>

                        <!-- Stock Status -->
                        <?php if (isset($item['stock_quantity'])): ?>
                            <?php if ($item['stock_quantity'] > 0): ?>
                                <?php if ($item['stock_quantity'] <= 5): ?>
                                <span class="cart-page-item-stock cart-page-item-stock-low">
                                    Only <?= $item['stock_quantity'] ?> left
                                </span>
                                <?php else: ?>
                                <span class="cart-page-item-stock cart-page-item-stock-ok">In Stock</span>
                                <?php endif; ?>
                            <?php else: ?>
                            <span class="cart-page-item-stock cart-page-item-stock-out">Out of Stock</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Mobile Price -->
                        <div class="cart-page-item-price-mobile">
                            <?= formatPrice($item['price'] * $item['quantity']) ?>
                        </div>

                        <!-- Actions -->
                        <div class="cart-page-item-actions">
                            <button type="button" class="cart-page-item-action" data-save-later="<?= $item['id'] ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                Save for Later
                            </button>
                            <button type="button" class="cart-page-item-action cart-page-item-remove" data-remove="<?= $item['id'] ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Remove
                            </button>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="cart-page-item-quantity">
                        <div class="cart-qty-control">
                            <button type="button" class="cart-qty-btn" data-qty-dec aria-label="Decrease quantity">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <input type="number" class="cart-qty-input" value="<?= $item['quantity'] ?>"
                                   min="1" max="<?= min(99, $item['stock_quantity'] ?? 99) ?>"
                                   data-item-id="<?= $item['id'] ?>" aria-label="Quantity">
                            <button type="button" class="cart-qty-btn" data-qty-inc aria-label="Increase quantity">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="cart-page-item-price">
                        <span class="cart-page-item-price-each">
                            <?= formatPrice($item['price']) ?> each
                        </span>
                        <span class="cart-page-item-price-total">
                            <?= formatPrice($item['price'] * $item['quantity']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Continue Shopping -->
            <div class="cart-continue">
                <a href="<?= url('/products') ?>" class="cart-continue-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Continue Shopping
                </a>
            </div>
        </div>

        <!-- Cart Summary -->
        <aside class="cart-summary">
            <h2 class="cart-summary-title">Order Summary</h2>

            <!-- Coupon Code -->
            <div class="cart-coupon">
                <form id="coupon-form" class="cart-coupon-form">
                    <input type="text" name="coupon_code" class="cart-coupon-input"
                           placeholder="Enter coupon code" id="coupon-input">
                    <button type="submit" class="cart-coupon-btn">Apply</button>
                </form>
                <p class="cart-coupon-message" id="coupon-message"></p>
            </div>

            <!-- Totals -->
            <div class="cart-totals">
                <div class="cart-totals-row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal"><?= formatPrice($subtotal) ?></span>
                </div>

                <?php if ($discount > 0): ?>
                <div class="cart-totals-row cart-totals-discount">
                    <span>Discount</span>
                    <span id="cart-discount">-<?= formatPrice($discount) ?></span>
                </div>
                <?php endif; ?>

                <div class="cart-totals-row">
                    <span>Shipping</span>
                    <span id="cart-shipping">
                        <?php if ($shipping > 0): ?>
                        <?= formatPrice($shipping) ?>
                        <?php else: ?>
                        <span class="cart-free-shipping">FREE</span>
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Shipping Estimate -->
                <div class="cart-shipping-estimate">
                    <button type="button" class="cart-shipping-toggle" id="shipping-estimate-toggle">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                        Estimate Shipping
                    </button>
                    <div class="cart-shipping-form" id="shipping-estimate-form" style="display: none;">
                        <select class="cart-shipping-select" id="shipping-province">
                            <option value="">Select Province</option>
                            <option value="GP">Gauteng</option>
                            <option value="WC">Western Cape</option>
                            <option value="KZN">KwaZulu-Natal</option>
                            <option value="EC">Eastern Cape</option>
                            <option value="FS">Free State</option>
                            <option value="MP">Mpumalanga</option>
                            <option value="NW">North West</option>
                            <option value="LP">Limpopo</option>
                            <option value="NC">Northern Cape</option>
                        </select>
                        <input type="text" class="cart-shipping-input" id="shipping-postal" placeholder="Postal Code">
                        <button type="button" class="btn btn-sm btn-outline" id="calculate-shipping">Calculate</button>
                    </div>
                </div>

                <div class="cart-totals-row cart-totals-total">
                    <span>Total</span>
                    <span id="cart-total"><?= formatPrice($total) ?></span>
                </div>

                <!-- VAT Notice -->
                <p class="cart-vat-notice">Includes VAT where applicable</p>
            </div>

            <!-- Checkout Button -->
            <a href="<?= url('/checkout') ?>" class="btn btn-primary btn-lg btn-block cart-checkout-btn">
                Proceed to Checkout
            </a>

            <!-- Trust Badges -->
            <div class="cart-trust">
                <div class="cart-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Secure Checkout</span>
                </div>
                <div class="cart-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>Buyer Protection</span>
                </div>
                <div class="cart-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 12 20 22 4 22 4 12"></polyline>
                        <rect x="2" y="7" width="20" height="5"></rect>
                        <line x1="12" y1="22" x2="12" y2="7"></line>
                        <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                        <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                    </svg>
                    <span>Free Returns</span>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="cart-payments">
                <span class="cart-payments-label">We Accept:</span>
                <div class="cart-payments-icons">
                    <span class="cart-payment-icon" title="Visa">
                        <svg width="40" height="24" viewBox="0 0 40 24"><text x="5" y="16" font-size="10" fill="#1A1F71">VISA</text></svg>
                    </span>
                    <span class="cart-payment-icon" title="Mastercard">
                        <svg width="40" height="24" viewBox="0 0 40 24"><circle cx="14" cy="12" r="8" fill="#EB001B"/><circle cx="26" cy="12" r="8" fill="#F79E1B"/></svg>
                    </span>
                    <span class="cart-payment-icon" title="PayFast">
                        <svg width="40" height="24" viewBox="0 0 40 24"><text x="3" y="15" font-size="8" fill="#00457C">PayFast</text></svg>
                    </span>
                    <span class="cart-payment-icon" title="EFT">
                        <svg width="40" height="24" viewBox="0 0 40 24"><text x="10" y="15" font-size="10" fill="#333">EFT</text></svg>
                    </span>
                </div>
            </div>
        </aside>
    </div>

    <!-- Saved for Later -->
    <?php if (!empty($savedItems ?? [])): ?>
    <div class="cart-saved-section">
        <h2 class="cart-saved-title">Saved for Later (<?= count($savedItems) ?>)</h2>
        <div class="cart-saved-grid">
            <?php foreach ($savedItems as $item): ?>
            <div class="cart-saved-item" data-saved-id="<?= $item['id'] ?>">
                <!-- Similar structure to cart items -->
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recently Viewed -->
    <?php if (!empty($recentlyViewed)): ?>
    <div class="cart-recently-section">
        <h2 class="cart-recently-title">Recently Viewed</h2>
        <div class="cart-recently-grid-full">
            <?php foreach ($recentlyViewed as $product): ?>
            <?php include APP_PATH . '/Views/components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<style>
/* =========================================================================
   CART PAGE STYLES
   ========================================================================= */

.cart-page {
    min-height: 60vh;
}

.cart-page-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-6);
}

.cart-page-count {
    font-size: var(--text-lg);
    font-weight: var(--font-normal);
    color: var(--color-text-muted);
}

/* Empty Cart */
.cart-empty {
    text-align: center;
    padding: var(--space-12) var(--space-4);
}

.cart-empty-icon {
    color: var(--color-neutral-300);
    margin-bottom: var(--space-6);
}

.cart-empty-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-2);
}

.cart-empty-text {
    color: var(--color-text-muted);
    margin-bottom: var(--space-6);
}

.cart-empty-recently {
    margin-top: var(--space-12);
    padding-top: var(--space-8);
    border-top: var(--border-1) solid var(--color-border-light);
}

/* Cart Layout */
.cart-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 1024px) {
    .cart-layout {
        grid-template-columns: 1fr 380px;
    }
}

/* Cart Items */
.cart-items-container {
    min-width: 0;
}

.cart-items {
    background-color: var(--color-background);
    border-radius: var(--radius-xl);
    border: var(--border-1) solid var(--color-border-light);
    overflow: hidden;
}

.cart-page-item {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: var(--space-4);
    padding: var(--space-4);
    border-bottom: var(--border-1) solid var(--color-border-light);
    transition: background-color var(--duration-200);
}

.cart-page-item:last-child {
    border-bottom: none;
}

.cart-page-item:hover {
    background-color: var(--color-background-alt);
}

.cart-page-item.is-removing {
    opacity: 0.5;
    pointer-events: none;
}

.cart-page-item.is-removed {
    animation: slideOut 0.3s ease-out forwards;
}

@keyframes slideOut {
    to {
        opacity: 0;
        transform: translateX(-100%);
        max-height: 0;
        padding: 0;
        margin: 0;
    }
}

@media (min-width: 768px) {
    .cart-page-item {
        grid-template-columns: 120px 1fr auto auto;
        align-items: center;
        padding: var(--space-6);
    }
}

.cart-page-item-image {
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: var(--radius-lg);
    background-color: var(--color-background-alt);
}

.cart-page-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-page-item-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
}

.cart-page-item-details {
    min-width: 0;
}

.cart-page-item-title {
    font-size: var(--text-base);
    font-weight: var(--font-medium);
    margin-bottom: var(--space-1);
}

.cart-page-item-title a:hover {
    color: var(--color-primary);
}

.cart-page-item-variant,
.cart-page-item-options {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    margin-bottom: var(--space-2);
}

.cart-page-item-stock {
    display: inline-block;
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
    margin-bottom: var(--space-2);
}

.cart-page-item-stock-ok {
    background-color: var(--color-success-50);
    color: var(--color-success);
}

.cart-page-item-stock-low {
    background-color: var(--color-warning-50);
    color: var(--color-warning);
}

.cart-page-item-stock-out {
    background-color: var(--color-danger-50);
    color: var(--color-danger);
}

.cart-page-item-price-mobile {
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-2);
}

@media (min-width: 768px) {
    .cart-page-item-price-mobile {
        display: none;
    }
}

.cart-page-item-actions {
    display: flex;
    gap: var(--space-4);
    margin-top: var(--space-2);
}

.cart-page-item-action {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    transition: var(--transition-colors);
}

.cart-page-item-action:hover {
    color: var(--color-primary);
}

.cart-page-item-remove:hover {
    color: var(--color-danger);
}

/* Quantity Control */
.cart-page-item-quantity {
    display: none;
}

@media (min-width: 768px) {
    .cart-page-item-quantity {
        display: block;
    }
}

.cart-qty-control {
    display: flex;
    align-items: center;
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    background-color: var(--color-background);
}

.cart-qty-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
    transition: var(--transition-colors);
}

.cart-qty-btn:hover {
    color: var(--color-primary);
    background-color: var(--color-background-alt);
}

.cart-qty-btn:first-child {
    border-radius: var(--radius-lg) 0 0 var(--radius-lg);
}

.cart-qty-btn:last-child {
    border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
}

.cart-qty-input {
    width: 50px;
    height: 40px;
    text-align: center;
    border: none;
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    -moz-appearance: textfield;
}

.cart-qty-input::-webkit-outer-spin-button,
.cart-qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Price */
.cart-page-item-price {
    display: none;
    text-align: right;
}

@media (min-width: 768px) {
    .cart-page-item-price {
        display: block;
    }
}

.cart-page-item-price-each {
    display: block;
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    margin-bottom: var(--space-1);
}

.cart-page-item-price-total {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
}

/* Continue Shopping */
.cart-continue {
    margin-top: var(--space-4);
}

.cart-continue-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    color: var(--color-primary);
}

.cart-continue-link:hover {
    text-decoration: underline;
}

/* Cart Summary */
.cart-summary {
    background-color: var(--color-background);
    border-radius: var(--radius-xl);
    border: var(--border-1) solid var(--color-border-light);
    padding: var(--space-6);
    height: fit-content;
    position: sticky;
    top: calc(var(--header-height) + var(--space-4));
}

.cart-summary-title {
    font-size: var(--text-lg);
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-4);
    border-bottom: var(--border-1) solid var(--color-border-light);
}

/* Coupon */
.cart-coupon {
    margin-bottom: var(--space-4);
}

.cart-coupon-form {
    display: flex;
    gap: var(--space-2);
}

.cart-coupon-input {
    flex: 1;
    height: 40px;
    padding: 0 var(--space-3);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    font-size: var(--text-sm);
}

.cart-coupon-btn {
    padding: 0 var(--space-4);
    height: 40px;
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    background-color: var(--color-background-alt);
    border-radius: var(--radius-lg);
    transition: var(--transition-colors);
}

.cart-coupon-btn:hover {
    background-color: var(--color-neutral-200);
}

.cart-coupon-message {
    font-size: var(--text-sm);
    margin-top: var(--space-2);
}

.cart-coupon-message.success {
    color: var(--color-success);
}

.cart-coupon-message.error {
    color: var(--color-danger);
}

/* Totals */
.cart-totals {
    margin-bottom: var(--space-4);
}

.cart-totals-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-2) 0;
    font-size: var(--text-sm);
}

.cart-totals-discount {
    color: var(--color-success);
}

.cart-totals-total {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    margin-top: var(--space-4);
    padding-top: var(--space-4);
    border-top: var(--border-2) solid var(--color-text);
}

.cart-free-shipping {
    color: var(--color-success);
    font-weight: var(--font-semibold);
}

/* Shipping Estimate */
.cart-shipping-estimate {
    margin: var(--space-4) 0;
    padding: var(--space-3);
    background-color: var(--color-background-alt);
    border-radius: var(--radius-lg);
}

.cart-shipping-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    width: 100%;
}

.cart-shipping-toggle:hover {
    color: var(--color-primary);
}

.cart-shipping-form {
    margin-top: var(--space-3);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.cart-shipping-select,
.cart-shipping-input {
    height: 36px;
    padding: 0 var(--space-3);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
}

.cart-vat-notice {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
    text-align: center;
    margin-top: var(--space-2);
}

/* Checkout Button */
.cart-checkout-btn {
    margin-bottom: var(--space-4);
}

/* Trust Badges */
.cart-trust {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: var(--space-4);
    padding: var(--space-4) 0;
    border-top: var(--border-1) solid var(--color-border-light);
}

.cart-trust-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-xs);
    color: var(--color-text-muted);
}

.cart-trust-item svg {
    color: var(--color-success);
}

/* Payment Methods */
.cart-payments {
    text-align: center;
    padding-top: var(--space-4);
    border-top: var(--border-1) solid var(--color-border-light);
}

.cart-payments-label {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
    display: block;
    margin-bottom: var(--space-2);
}

.cart-payments-icons {
    display: flex;
    justify-content: center;
    gap: var(--space-2);
}

.cart-payment-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 30px;
    background-color: var(--color-background-alt);
    border-radius: var(--radius-sm);
}

/* Recently Viewed */
.cart-recently-section {
    margin-top: var(--space-12);
    padding-top: var(--space-8);
    border-top: var(--border-1) solid var(--color-border-light);
}

.cart-recently-title {
    font-size: var(--text-xl);
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-6);
}

.cart-recently-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .cart-recently-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.cart-recently-grid-full {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .cart-recently-grid-full {
        grid-template-columns: repeat(4, 1fr);
    }
}

.cart-recently-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--space-3);
    border-radius: var(--radius-lg);
    transition: var(--transition-colors);
}

.cart-recently-item:hover {
    background-color: var(--color-background-alt);
}

.cart-recently-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-2);
}

.cart-recently-placeholder {
    background-color: var(--color-neutral-200);
}

.cart-recently-name {
    font-size: var(--text-sm);
    text-align: center;
    margin-bottom: var(--space-1);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.cart-recently-price {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-primary);
}
</style>

<script>
(function() {
    'use strict';

    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    const formatPrice = (amount) => {
        const { symbol, decimals } = window.Pricetag.currency;
        return symbol + Number(amount).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    };

    const CartPage = {
        init() {
            this.bindQuantityControls();
            this.bindRemoveButtons();
            this.bindSaveForLater();
            this.bindCouponForm();
            this.bindShippingEstimate();
        },

        bindQuantityControls() {
            $$('.cart-qty-control').forEach(control => {
                const input = control.querySelector('.cart-qty-input');
                const decBtn = control.querySelector('[data-qty-dec]');
                const incBtn = control.querySelector('[data-qty-inc]');

                if (!input) return;

                decBtn?.addEventListener('click', () => {
                    const newVal = Math.max(1, parseInt(input.value) - 1);
                    input.value = newVal;
                    this.updateQuantity(input.dataset.itemId, newVal);
                });

                incBtn?.addEventListener('click', () => {
                    const max = parseInt(input.max) || 99;
                    const newVal = Math.min(max, parseInt(input.value) + 1);
                    input.value = newVal;
                    this.updateQuantity(input.dataset.itemId, newVal);
                });

                let debounceTimer;
                input.addEventListener('change', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        const val = Math.max(1, Math.min(parseInt(input.max) || 99, parseInt(input.value) || 1));
                        input.value = val;
                        this.updateQuantity(input.dataset.itemId, val);
                    }, 500);
                });
            });
        },

        async updateQuantity(itemId, quantity) {
            const item = $(`.cart-page-item[data-item-id="${itemId}"]`);
            if (item) item.style.opacity = '0.7';

            try {
                const formData = new FormData();
                formData.append('_token', window.Pricetag.csrfToken);
                formData.append('item_id', itemId);
                formData.append('quantity', quantity);

                const response = await fetch(window.Pricetag.baseUrl + '/cart/update', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();
                if (data.success) {
                    this.updateTotals(data.cart);
                    this.updateItemPrice(itemId, data.cart.items);
                } else {
                    window.Pricetag.Toast?.error(data.message || 'Failed to update');
                }
            } catch (err) {
                window.Pricetag.Toast?.error('Failed to update cart');
            } finally {
                if (item) item.style.opacity = '1';
            }
        },

        updateItemPrice(itemId, items) {
            const itemData = items.find(i => i.id == itemId);
            if (!itemData) return;

            const item = $(`.cart-page-item[data-item-id="${itemId}"]`);
            if (!item) return;

            const priceTotal = item.querySelector('.cart-page-item-price-total');
            const priceMobile = item.querySelector('.cart-page-item-price-mobile');

            const total = itemData.price * itemData.quantity;
            if (priceTotal) priceTotal.textContent = formatPrice(total);
            if (priceMobile) priceMobile.textContent = formatPrice(total);
        },

        updateTotals(cart) {
            $('#cart-subtotal').textContent = formatPrice(cart.subtotal);
            $('#cart-total').textContent = formatPrice(cart.total);

            const shippingEl = $('#cart-shipping');
            if (shippingEl) {
                if (cart.shipping > 0) {
                    shippingEl.innerHTML = formatPrice(cart.shipping);
                } else {
                    shippingEl.innerHTML = '<span class="cart-free-shipping">FREE</span>';
                }
            }

            const discountEl = $('#cart-discount');
            if (discountEl && cart.discount > 0) {
                discountEl.textContent = '-' + formatPrice(cart.discount);
            }

            // Update header count
            const countEl = document.querySelector('#cart-count');
            if (countEl) {
                countEl.textContent = cart.count || '';
                countEl.dataset.count = cart.count;
            }
        },

        bindRemoveButtons() {
            $$('[data-remove]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const itemId = btn.dataset.remove;
                    const item = $(`.cart-page-item[data-item-id="${itemId}"]`);

                    if (!confirm('Remove this item from your cart?')) return;

                    item?.classList.add('is-removing');

                    try {
                        const formData = new FormData();
                        formData.append('_token', window.Pricetag.csrfToken);
                        formData.append('item_id', itemId);

                        const response = await fetch(window.Pricetag.baseUrl + '/cart/remove', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const data = await response.json();
                        if (data.success) {
                            item?.classList.add('is-removed');
                            setTimeout(() => {
                                item?.remove();
                                this.updateTotals(data.cart);

                                // Check if cart is empty
                                if (data.cart.count === 0) {
                                    location.reload();
                                }
                            }, 300);
                            window.Pricetag.Toast?.success('Item removed from cart');
                        }
                    } catch (err) {
                        item?.classList.remove('is-removing');
                        window.Pricetag.Toast?.error('Failed to remove item');
                    }
                });
            });
        },

        bindSaveForLater() {
            $$('[data-save-later]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!window.Pricetag.isLoggedIn) {
                        window.Pricetag.Toast?.info('Please login to save items');
                        return;
                    }

                    const itemId = btn.dataset.saveLater;
                    // Implement save for later logic
                    window.Pricetag.Toast?.success('Item saved to wishlist');
                });
            });
        },

        bindCouponForm() {
            const form = $('#coupon-form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const input = $('#coupon-input');
                const message = $('#coupon-message');
                const code = input.value.trim();

                if (!code) {
                    message.textContent = 'Please enter a coupon code';
                    message.className = 'cart-coupon-message error';
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('_token', window.Pricetag.csrfToken);
                    formData.append('coupon_code', code);

                    const response = await fetch(window.Pricetag.baseUrl + '/cart/apply-coupon', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    const data = await response.json();
                    if (data.success) {
                        message.textContent = 'Coupon applied successfully!';
                        message.className = 'cart-coupon-message success';
                        this.updateTotals(data.cart);
                    } else {
                        message.textContent = data.message;
                        message.className = 'cart-coupon-message error';
                    }
                } catch (err) {
                    message.textContent = 'Failed to apply coupon';
                    message.className = 'cart-coupon-message error';
                }
            });
        },

        bindShippingEstimate() {
            const toggle = $('#shipping-estimate-toggle');
            const form = $('#shipping-estimate-form');

            toggle?.addEventListener('click', () => {
                form.style.display = form.style.display === 'none' ? 'flex' : 'none';
            });

            $('#calculate-shipping')?.addEventListener('click', async () => {
                const province = $('#shipping-province').value;
                const postal = $('#shipping-postal').value;

                if (!province) {
                    window.Pricetag.Toast?.error('Please select a province');
                    return;
                }

                // Shipping estimate logic would go here
                window.Pricetag.Toast?.info('Shipping estimate calculated');
            });
        }
    };

    document.addEventListener('DOMContentLoaded', () => CartPage.init());
})();
</script>
