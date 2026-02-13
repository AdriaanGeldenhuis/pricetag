<?php
/**
 * Product Card Component - Dark Theme
 * Usage: Include with $product variable set
 */

$primaryImage = $product->getPrimaryImage();
$primaryCategory = $product->getPrimaryCategory();
$discount = $product->getDiscountPercentage();
$inStock = $product->isInStock();
$stockStatus = $product->getStockStatus();
$productUrl = url('/products/' . $product->slug);
?>
<article class="product-card <?php echo !$inStock ? 'product-card-out-of-stock' : ''; ?>">
    <!-- Full card clickable link -->
    <a href="<?php echo $productUrl; ?>" class="product-card-link" aria-label="<?php echo e($product->name); ?>"></a>

    <div class="product-card-image">
        <?php if ($primaryImage): ?>
        <img src="<?php echo url('storage/uploads/' . e($primaryImage)); ?>"
             alt="<?php echo e($product->name); ?>"
             class="product-card-img"
             loading="lazy">
        <?php else: ?>
        <div class="product-card-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
        </div>
        <?php endif; ?>

        <!-- Badges -->
        <?php if (!$inStock || ($product->is_on_sale && $discount) || $product->is_new): ?>
        <div class="product-card-badges">
            <?php if (!$inStock): ?>
            <span class="product-badge badge-out-of-stock">Out of Stock</span>
            <?php else: ?>
                <?php if ($product->is_on_sale && $discount): ?>
                <span class="product-badge badge-sale">-<?php echo $discount; ?>%</span>
                <?php endif; ?>
                <?php if ($product->is_new): ?>
                <span class="product-badge badge-new">New</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>

    <!-- 3-Dot Menu Button (outside image for overflow visibility) -->
    <div class="product-card-menu-wrapper">
        <button type="button"
                class="product-card-menu-btn"
                data-product-menu-toggle
                aria-label="Product options"
                aria-haspopup="true"
                aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                <circle cx="12" cy="5" r="2"></circle>
                <circle cx="12" cy="12" r="2"></circle>
                <circle cx="12" cy="19" r="2"></circle>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div class="product-card-dropdown" data-product-dropdown>
            <button type="button"
                    class="product-dropdown-item"
                    data-quick-view="<?php echo $product->id; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                Quick Look
            </button>
            <button type="button"
                    class="product-dropdown-item"
                    data-wishlist-toggle="<?php echo $product->id; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                Wishlist
            </button>
            <button type="button"
                    class="product-dropdown-item"
                    data-quote-add="<?php echo $product->id; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg>
                Add to Quote
            </button>
            <button type="button"
                    class="product-dropdown-item"
                    data-similar-products="<?php echo $product->id; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Similar Products
            </button>
            <button type="button"
                    class="product-dropdown-item"
                    data-ask-question="<?php echo $product->id; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                Ask a Question
            </button>
            <label class="product-dropdown-item product-dropdown-compare">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="16 3 21 3 21 8"></polyline>
                    <line x1="4" y1="20" x2="21" y2="3"></line>
                    <polyline points="21 16 21 21 16 21"></polyline>
                    <line x1="15" y1="15" x2="21" y2="21"></line>
                    <line x1="4" y1="4" x2="9" y2="9"></line>
                </svg>
                Compare List
                <input type="checkbox" class="product-compare-checkbox" data-compare="<?php echo $product->id; ?>">
                <span class="product-compare-check"></span>
            </label>
            <button type="button"
                    class="product-dropdown-close"
                    data-product-menu-close
                    aria-label="Close menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    <div class="product-card-body">
        <?php if ($primaryCategory): ?>
        <p class="product-card-category"><?php echo e($primaryCategory['name']); ?></p>
        <?php endif; ?>

        <h3 class="product-card-title">
            <a href="<?php echo $productUrl; ?>"><?php echo e($product->name); ?></a>
        </h3>

        <?php if ($product->rating_count > 0): ?>
        <div class="product-card-rating">
            <div class="product-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg viewBox="0 0 24 24" fill="<?php echo $i <= round($product->rating_average) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <?php endfor; ?>
            </div>
            <span class="product-rating-count">(<?php echo $product->rating_count; ?>)</span>
        </div>
        <?php endif; ?>

        <div class="product-card-price">
            <span class="product-price-current"><?php echo formatPrice($product->price); ?></span>
            <?php if ($product->compare_price && $product->compare_price > $product->price): ?>
            <span class="product-price-original"><?php echo formatPrice($product->compare_price); ?></span>
            <?php endif; ?>
        </div>
    </div>
</article>

<style>
/* Product Card - Dark Theme */
.product-card {
    position: relative;
    background-color: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-xl);
    transition: var(--transition-all);
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    border-color: var(--color-primary);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    transform: translateY(-4px);
}

/* Full card clickable overlay */
.product-card-link {
    position: absolute;
    inset: 0;
    z-index: 1;
}

.product-card-image {
    position: relative;
    aspect-ratio: 1 / 1;
    background-color: var(--color-background);
    overflow: hidden;
    border-radius: var(--radius-xl) var(--radius-xl) 0 0;
}

.product-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-300) var(--ease-out);
}

.product-card:hover .product-card-img {
    transform: scale(1.05);
}

.product-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--color-background-alt);
    color: var(--color-text-muted);
}

.product-card-placeholder svg {
    width: 48px;
    height: 48px;
    opacity: 0.3;
}

/* Badges */
.product-card-badges {
    position: absolute;
    top: var(--space-3);
    left: var(--space-3);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    z-index: 2;
}

.product-badge {
    padding: var(--space-1) var(--space-2);
    font-size: 10px;
    font-weight: var(--font-bold);
    text-transform: uppercase;
    border-radius: var(--radius-default);
}

.badge-sale {
    background-color: var(--color-danger);
    color: white;
}

.badge-new {
    background-color: var(--color-success);
    color: var(--color-neutral-900);
}

.badge-out-of-stock {
    background-color: var(--color-neutral-600);
    color: white;
}

/* Out of Stock State */
.product-card-out-of-stock .product-card-img {
    opacity: 0.6;
    filter: grayscale(30%);
}

.product-card-out-of-stock:hover .product-card-img {
    transform: none;
}

/* ===== 3-DOT MENU ===== */
.product-card-menu-wrapper {
    position: absolute;
    top: var(--space-3);
    right: var(--space-3);
    z-index: 3;
}

.product-card-menu-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    color: rgba(255, 255, 255, 0.85);
    cursor: pointer;
    transition: all 0.2s ease;
    opacity: 1;
    transform: scale(1);
}

.product-card-menu-btn:hover {
    background: rgba(0, 0, 0, 0.75);
    color: white;
    border-color: rgba(255, 255, 255, 0.2);
}

.product-card-menu-btn.is-active {
    opacity: 1;
    transform: scale(1);
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: white;
}

.product-card-menu-btn svg {
    width: 16px;
    height: 16px;
}

/* Dropdown */
.product-card-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    min-width: 190px;
    background: rgba(30, 30, 40, 0.92);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-xl);
    padding: var(--space-2);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px) scale(0.95);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
}

.product-card-dropdown.is-open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.product-dropdown-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    width: 100%;
    padding: var(--space-2) var(--space-3);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: rgba(255, 255, 255, 0.85);
    background: transparent;
    border: none;
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: left;
    white-space: nowrap;
}

.product-dropdown-item:hover {
    background: rgba(255, 255, 255, 0.08);
    color: white;
}

.product-dropdown-item svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    opacity: 0.7;
}

.product-dropdown-item:hover svg {
    opacity: 1;
}

/* Compare checkbox */
.product-dropdown-compare {
    position: relative;
    cursor: pointer;
}

.product-compare-checkbox {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.product-compare-check {
    margin-left: auto;
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.25);
    border-radius: var(--radius-default);
    transition: all 0.15s ease;
    flex-shrink: 0;
}

.product-compare-checkbox:checked + .product-compare-check {
    background: var(--color-primary);
    border-color: var(--color-primary);
}

.product-compare-checkbox:checked + .product-compare-check::after {
    content: '';
    display: block;
    width: 5px;
    height: 9px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin: 1px auto 0;
}

/* Close button */
.product-dropdown-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: var(--space-2);
    margin-top: var(--space-1);
    background: rgba(255, 255, 255, 0.05);
    border: none;
    border-radius: var(--radius-lg);
    color: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.15s ease;
}

.product-dropdown-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
}

.product-dropdown-close svg {
    width: 16px;
    height: 16px;
}

/* Card Body */
.product-card-body {
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    flex: 1;
}

.product-card-category {
    font-size: var(--text-xs);
    color: var(--color-accent);
    text-transform: uppercase;
    letter-spacing: var(--tracking-wide);
    margin: 0 0 var(--space-2) 0;
}

.product-card-title {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    line-height: var(--leading-snug);
    margin: 0 0 var(--space-2) 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

.product-card-title a {
    color: var(--color-text);
    transition: var(--transition-colors);
    position: relative;
    z-index: 2;
}

.product-card-title a:hover {
    color: var(--color-accent);
}

/* Rating */
.product-card-rating {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-2);
}

.product-stars {
    display: flex;
    gap: 2px;
    color: var(--color-warning);
}

.product-stars svg {
    width: 14px;
    height: 14px;
}

.product-rating-count {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
}

/* Price */
.product-card-price {
    display: flex;
    align-items: baseline;
    gap: var(--space-2);
    margin-top: auto;
}

.product-price-current {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--color-text);
}

.product-price-original {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    text-decoration: line-through;
}
</style>
