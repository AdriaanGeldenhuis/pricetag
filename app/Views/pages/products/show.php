<!-- Product Detail Page -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <?php foreach ($breadcrumbs as $crumb): ?>
    <div class="breadcrumb-item">
        <?php if ($crumb['url']): ?>
        <a href="<?= e($crumb['url']) ?>" class="breadcrumb-link"><?= e($crumb['name']) ?></a>
        <?php else: ?>
        <span class="breadcrumb-current"><?= e($crumb['name']) ?></span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</nav>

<div class="container py-8">
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
        <!-- Product Images -->
        <div class="product-gallery">
            <div class="product-main-image mb-4">
                <?php $mainImage = $images[0] ?? null; ?>
                <img id="product-main-image"
                     src="<?= $mainImage ? url('storage/uploads/' . e($mainImage['path'])) : asset('images/placeholder.jpg') ?>"
                     alt="<?= e($product->name) ?>"
                     class="w-full rounded-xl">
            </div>

            <?php if (count($images) > 1): ?>
            <div class="product-thumbs flex gap-2 overflow-x-auto">
                <?php foreach ($images as $i => $image): ?>
                <button type="button"
                        class="product-thumb flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 <?= $i === 0 ? 'is-active border-primary' : 'border-transparent' ?>"
                        data-image="<?= url('storage/uploads/' . e($image['path'])) ?>">
                    <img src="<?= url('storage/uploads/' . e($image['path'])) ?>"
                         alt=""
                         class="w-full h-full object-cover">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <?php if ($primaryCategory): ?>
            <a href="<?= url('/categories/' . $primaryCategory['slug']) ?>" class="text-sm text-primary font-medium">
                <?= e($primaryCategory['name']) ?>
            </a>
            <?php endif; ?>

            <h1 class="text-2xl lg:text-3xl font-bold mt-2 mb-4"><?= e($product->name) ?></h1>

            <!-- Rating -->
            <?php if ($product->rating_count > 0): ?>
            <div class="flex items-center gap-2 mb-4">
                <div class="flex text-accent">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $i <= round($product->rating_average) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <?php endfor; ?>
                </div>
                <span class="text-sm text-muted">(<?= $product->rating_count ?> reviews)</span>
            </div>
            <?php endif; ?>

            <!-- Price -->
            <div class="mb-6">
                <div class="flex items-baseline gap-3">
                    <span id="product-price" class="text-3xl font-bold"><?= formatPrice($product->price) ?></span>
                    <?php if ($product->compare_price && $product->compare_price > $product->price): ?>
                    <span class="text-lg text-muted line-through"><?= formatPrice($product->compare_price) ?></span>
                    <span class="badge badge-solid-danger">-<?= $product->getDiscountPercentage() ?>%</span>
                    <?php endif; ?>
                </div>
                <?php if (config('payment.tax.inclusive')): ?>
                <p class="text-sm text-muted mt-1">VAT included</p>
                <?php endif; ?>
            </div>

            <!-- Stock Status -->
            <div class="mb-6">
                <?php if ($product->isInStock()): ?>
                <span id="stock-status" class="badge badge-success"><?= $product->getStockStatus() ?></span>
                <?php else: ?>
                <span id="stock-status" class="badge badge-danger">Out of Stock</span>
                <?php endif; ?>
            </div>

            <!-- Short Description -->
            <?php if ($product->short_description): ?>
            <p class="text-muted mb-6"><?= e($product->short_description) ?></p>
            <?php endif; ?>

            <!-- Variants -->
            <?php if (!empty($variants)): ?>
            <div class="mb-6">
                <?php
                $variantAttributes = [];
                foreach ($variants as $variant) {
                    // Group variant attributes
                }
                ?>
                <!-- Variant selectors would go here -->
            </div>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <form id="product-form" class="mb-8">
                <input type="hidden" id="variant-id" name="variant_id" value="">

                <!-- Quantity -->
                <div class="flex items-center gap-4 mb-6">
                    <label class="font-medium">Quantity:</label>
                    <div class="flex items-center border rounded-lg">
                        <button type="button" id="qty-decrease" class="w-10 h-10 flex items-center justify-center text-lg">-</button>
                        <input type="number" id="product-quantity" name="quantity" value="1" min="1" max="99"
                               class="w-16 h-10 text-center border-x" readonly>
                        <button type="button" id="qty-increase" class="w-10 h-10 flex items-center justify-center text-lg">+</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="button"
                            id="add-to-cart-btn"
                            class="btn btn-primary btn-lg flex-1"
                            data-add-to-cart="<?= $product->id ?>"
                            <?= !$product->isInStock() ? 'disabled' : '' ?>>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add to Cart
                    </button>
                    <button type="button"
                            class="btn btn-outline btn-lg btn-icon"
                            data-wishlist-toggle="<?= $product->id ?>"
                            aria-label="Add to wishlist">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Trust Indicators -->
            <div class="border-t pt-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <span>Secure checkout</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                        <span>Fast delivery</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success">
                            <polyline points="9 11 12 14 22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                        <span>Quality guarantee</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>Easy returns</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    <?php if ($product->description): ?>
    <div class="mt-12">
        <h2 class="text-xl font-semibold mb-4">Product Description</h2>
        <div class="prose max-w-none">
            <?= nl2br(e($product->description)) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reviews -->
    <?php if (!empty($reviews)): ?>
    <div class="mt-12">
        <h2 class="text-xl font-semibold mb-6">Customer Reviews</h2>
        <div class="space-y-6">
            <?php foreach ($reviews as $review): ?>
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex text-accent">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $i <= $review['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            <?php endfor; ?>
                        </div>
                        <?php if ($review['is_verified']): ?>
                        <span class="badge badge-success">Verified Purchase</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($review['title']): ?>
                    <h4 class="font-medium mb-2"><?= e($review['title']) ?></h4>
                    <?php endif; ?>
                    <p class="text-muted"><?= e($review['content']) ?></p>
                    <p class="text-sm text-muted mt-3">
                        By <?= e($review['first_name'] ?? 'Anonymous') ?> on <?= date('d M Y', strtotime($review['created_at'])) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="mt-12">
        <h2 class="text-xl font-semibold mb-6">You May Also Like</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach ($relatedProducts as $product): ?>
            <?php include APP_PATH . '/Views/components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.product-thumb.is-active {
    border-color: var(--color-primary);
}
.prose {
    line-height: 1.75;
}
</style>
