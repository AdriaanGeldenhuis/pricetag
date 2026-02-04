<?php
/**
 * Product Card Component - Dark Theme
 * Usage: Include with $product variable set
 */

$primaryImage = $product->getPrimaryImage();
$primaryCategory = $product->getPrimaryCategory();
$discount = $product->getDiscountPercentage();
?>
<article class="product-card">
    <div class="product-card-image">
        <a href="<?php echo url('/products/' . $product->slug); ?>">
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
        </a>

        <!-- Badges -->
        <?php if (($product->is_on_sale && $discount) || $product->is_new): ?>
        <div class="product-card-badges">
            <?php if ($product->is_on_sale && $discount): ?>
            <span class="product-badge badge-sale">-<?php echo $discount; ?>%</span>
            <?php endif; ?>
            <?php if ($product->is_new): ?>
            <span class="product-badge badge-new">New</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="product-card-actions">
            <button type="button"
                    class="product-action-btn"
                    data-quick-view="<?php echo $product->id; ?>"
                    aria-label="Quick view">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
            <button type="button"
                    class="product-action-btn"
                    data-wishlist-toggle="<?php echo $product->id; ?>"
                    aria-label="Add to wishlist">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </button>
            <button type="button"
                    class="product-action-btn product-action-cart"
                    data-add-to-cart="<?php echo $product->id; ?>"
                    aria-label="Add to cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="product-card-body">
        <?php if ($primaryCategory): ?>
        <p class="product-card-category"><?php echo e($primaryCategory['name']); ?></p>
        <?php endif; ?>

        <h3 class="product-card-title">
            <a href="<?php echo url('/products/' . $product->slug); ?>"><?php echo e($product->name); ?></a>
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
    background-color: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: var(--transition-all);
}

.product-card:hover {
    border-color: var(--color-primary);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    transform: translateY(-4px);
}

.product-card-image {
    position: relative;
    aspect-ratio: 1 / 1;
    background-color: var(--color-background);
    overflow: hidden;
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

/* Quick Actions */
.product-card-actions {
    position: absolute;
    bottom: var(--space-3);
    left: var(--space-3);
    right: var(--space-3);
    display: flex;
    justify-content: center;
    gap: var(--space-2);
    opacity: 0;
    transform: translateY(10px);
    transition: var(--transition-all);
}

.product-card:hover .product-card-actions {
    opacity: 1;
    transform: translateY(0);
}

.product-action-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    color: var(--color-text-secondary);
    transition: var(--transition-colors);
}

.product-action-btn:hover {
    background-color: var(--color-background-hover);
    color: var(--color-text);
    border-color: var(--color-neutral-500);
}

.product-action-btn.product-action-cart:hover {
    background-color: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.product-action-btn svg {
    width: 18px;
    height: 18px;
}

/* Card Body */
.product-card-body {
    padding: var(--space-4);
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
}

.product-card-title a {
    color: var(--color-text);
    transition: var(--transition-colors);
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
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-1);
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

@media (min-width: 640px) {
    .product-card-price {
        flex-direction: row;
        align-items: center;
        gap: var(--space-2);
    }
}
</style>
