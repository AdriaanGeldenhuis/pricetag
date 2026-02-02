<?php
/**
 * Product Card Component
 * Usage: Include with $product variable set
 */

$primaryImage = $product->getPrimaryImage();
$primaryCategory = $product->getPrimaryCategory();
$discount = $product->getDiscountPercentage();
?>
<article class="product-card">
    <div class="product-card-image">
        <a href="<?= url('/products/' . $product->slug) ?>">
            <?php if ($primaryImage): ?>
            <img src="<?= url('storage/uploads/' . e($primaryImage)) ?>"
                 alt="<?= e($product->name) ?>"
                 class="product-card-img"
                 loading="lazy">
            <?php else: ?>
            <div class="product-card-img" style="background: var(--color-neutral-100);"></div>
            <?php endif; ?>
        </a>

        <!-- Badges -->
        <div class="product-card-badges">
            <?php if ($product->is_on_sale && $discount): ?>
            <span class="product-card-badge product-card-badge-sale">-<?= $discount ?>%</span>
            <?php endif; ?>
            <?php if ($product->is_new): ?>
            <span class="product-card-badge product-card-badge-new">New</span>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="product-card-actions">
            <button type="button"
                    class="product-card-action"
                    data-wishlist-toggle="<?= $product->id ?>"
                    aria-label="Add to wishlist">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </button>
            <button type="button"
                    class="product-card-action"
                    data-add-to-cart="<?= $product->id ?>"
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
        <p class="product-card-category"><?= e($primaryCategory['name']) ?></p>
        <?php endif; ?>

        <h3 class="product-card-title">
            <a href="<?= url('/products/' . $product->slug) ?>"><?= e($product->name) ?></a>
        </h3>

        <?php if ($product->rating_count > 0): ?>
        <div class="product-card-rating">
            <div class="product-card-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg viewBox="0 0 24 24" fill="<?= $i <= round($product->rating_average) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <?php endfor; ?>
            </div>
            <span class="product-card-rating-count">(<?= $product->rating_count ?>)</span>
        </div>
        <?php endif; ?>

        <div class="product-card-price">
            <span class="product-card-price-current"><?= formatPrice($product->price) ?></span>
            <?php if ($product->compare_price && $product->compare_price > $product->price): ?>
            <span class="product-card-price-original"><?= formatPrice($product->compare_price) ?></span>
            <?php endif; ?>
        </div>
    </div>
</article>
