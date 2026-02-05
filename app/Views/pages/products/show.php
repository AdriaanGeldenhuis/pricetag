<!-- Product Page CSS -->
<link rel="stylesheet" href="<?= asset('css/product-page.css') ?>">

<?php
$mainImage = $images[0] ?? null;
$groupedAttributes = [];
foreach ($attributes as $attr) {
    $key = $attr['attribute_slug'] ?? $attr['attribute_name'];
    if (!isset($groupedAttributes[$key])) {
        $groupedAttributes[$key] = ['name' => $attr['attribute_name'], 'values' => []];
    }
    $groupedAttributes[$key]['values'][] = [
        'value' => $attr['value'],
        'slug' => $attr['value_slug'] ?? '',
        'color' => $attr['color_code'] ?? null
    ];
}
$hasSpecs = !empty($specifications) || !empty($groupedAttributes) || $product->weight || ($product->length && $product->width && $product->height);
$specCount = count($specifications ?? []) + count($groupedAttributes) + ($product->weight ? 1 : 0) + (($product->length && $product->width && $product->height) ? 1 : 0);
?>

<div class="pdp">

    <!-- HERO: Split Screen -->
    <section class="pdp-hero">

        <!-- Visual Side -->
        <div class="pdp-hero__visual">
            <!-- Badges -->
            <div class="pdp-hero__badges">
                <?php if ($product->is_on_sale && $product->getDiscountPercentage()): ?>
                <span class="pdp-badge">-<?= $product->getDiscountPercentage() ?>%</span>
                <?php endif; ?>
                <?php if ($product->is_new): ?>
                <span class="pdp-badge pdp-badge--new">New</span>
                <?php endif; ?>
            </div>

            <!-- Main Image -->
            <div class="pdp-hero__image-wrap">
                <img id="pdp-main-image"
                     src="<?= $mainImage ? url('storage/uploads/' . e($mainImage['path'])) : asset('images/placeholder.jpg') ?>"
                     alt="<?= e($product->name) ?>"
                     class="pdp-hero__image"
                     data-lightbox-trigger>
            </div>

            <!-- Image Dots -->
            <?php if (count($images) > 1): ?>
            <div class="pdp-hero__nav" id="pdp-dots">
                <?php foreach ($images as $i => $image): ?>
                <button type="button"
                        class="pdp-hero__dot <?= $i === 0 ? 'is-active' : '' ?>"
                        data-image="<?= url('storage/uploads/' . e($image['path'])) ?>"
                        data-index="<?= $i ?>"
                        aria-label="Image <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Content Side -->
        <div class="pdp-hero__content">

            <!-- Breadcrumb -->
            <nav class="pdp-crumb">
                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?><span class="pdp-crumb__sep">/</span><?php endif; ?>
                    <?php if ($crumb['url']): ?>
                    <a href="<?= e($crumb['url']) ?>"><?= e($crumb['name']) ?></a>
                    <?php else: ?>
                    <span><?= e($crumb['name']) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <!-- Category -->
            <?php if ($primaryCategory): ?>
            <a href="<?= url('/categories/' . $primaryCategory['slug']) ?>" class="pdp-category">
                <?= e($primaryCategory['name']) ?>
            </a>
            <?php endif; ?>

            <!-- Title -->
            <h1 class="pdp-title"><?= e($product->name) ?></h1>

            <!-- SKU -->
            <?php if ($product->sku): ?>
            <p class="pdp-sku"><?= e($product->sku) ?></p>
            <?php endif; ?>

            <!-- Rating -->
            <div class="pdp-rating">
                <div class="pdp-rating__stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg class="pdp-rating__star <?= $i <= round($product->rating_average ?? 0) ? 'is-filled' : '' ?>" viewBox="0 0 24 24" fill="<?= $i <= round($product->rating_average ?? 0) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <?php endfor; ?>
                </div>
                <a href="#pdp-reviews" class="pdp-rating__count"><?= $product->rating_count ?? 0 ?> Reviews</a>
            </div>

            <!-- Price -->
            <div class="pdp-price">
                <span class="pdp-price__current" id="pdp-price"><?= formatPrice($product->price) ?></span>
                <?php if ($product->compare_price && $product->compare_price > $product->price): ?>
                <div class="pdp-price__meta">
                    <span class="pdp-price__old"><?= formatPrice($product->compare_price) ?></span>
                    <span class="pdp-price__save">Save <?= formatPrice($product->compare_price - $product->price) ?></span>
                </div>
                <?php endif; ?>
                <?php if (config('payment.tax.inclusive')): ?>
                <p class="pdp-price__vat">Incl. VAT</p>
                <?php endif; ?>
            </div>

            <!-- Stock -->
            <div class="pdp-stock <?= $product->isInStock() ? ($product->stock_quantity <= ($product->low_stock_threshold ?? 5) ? 'pdp-stock--low' : '') : 'pdp-stock--out' ?>">
                <span class="pdp-stock__dot"></span>
                <span id="pdp-stock-status"><?= $product->isInStock() ? $product->getStockStatus() : 'Out of Stock' ?></span>
            </div>

            <!-- Description -->
            <?php if ($product->short_description): ?>
            <p class="pdp-desc"><?= e($product->short_description) ?></p>
            <?php endif; ?>

            <!-- Variants -->
            <?php if (!empty($groupedAttributes)): ?>
            <div class="pdp-variants">
                <?php foreach ($groupedAttributes as $attrSlug => $attr): ?>
                <div class="pdp-variant">
                    <label class="pdp-variant__label">
                        <?= e($attr['name']) ?>
                        <span data-variant-display="<?= e($attrSlug) ?>">—</span>
                    </label>
                    <div class="pdp-variant__options" data-variant="<?= e($attrSlug) ?>">
                        <?php if ($attrSlug === 'color' || $attrSlug === 'colour'): ?>
                            <?php foreach ($attr['values'] as $val): ?>
                            <button type="button"
                                    class="pdp-swatch"
                                    style="background-color: <?= e($val['color'] ?: '#888') ?>"
                                    data-value="<?= e($val['slug'] ?: $val['value']) ?>"
                                    title="<?= e($val['value']) ?>"
                                    aria-label="<?= e($val['value']) ?>"></button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($attr['values'] as $val): ?>
                            <button type="button"
                                    class="pdp-size"
                                    data-value="<?= e($val['slug'] ?: $val['value']) ?>"><?= e($val['value']) ?></button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <form id="pdp-form">
                <input type="hidden" id="pdp-product-id" name="product_id" value="<?= $product->id ?>">
                <input type="hidden" id="pdp-variant-id" name="variant_id" value="">

                <?php if ($product->isInStock()): ?>
                <div class="pdp-actions">
                    <div class="pdp-qty">
                        <button type="button" class="pdp-qty__btn" id="pdp-qty-dec" aria-label="Decrease">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                        <input type="number" id="pdp-quantity" name="quantity" value="1" min="1" max="<?= min(99, $product->stock_quantity ?? 99) ?>" class="pdp-qty__input" readonly>
                        <button type="button" class="pdp-qty__btn" id="pdp-qty-inc" aria-label="Increase">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                    </div>
                    <button type="button" id="pdp-add-cart" class="pdp-btn-cart" data-product-add="<?= $product->id ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Add to Bag
                    </button>
                    <button type="button" class="pdp-btn-wish" data-wishlist-toggle="<?= $product->id ?>" aria-label="Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                </div>
                <?php else: ?>
                <div class="pdp-notify">
                    <h4 class="pdp-notify__title">Notify Me</h4>
                    <p class="pdp-notify__text">Get notified when this product is back in stock.</p>
                    <form id="pdp-notify-form" class="pdp-notify__form">
                        <input type="email" name="email" placeholder="Email address" required class="pdp-notify__input">
                        <button type="submit" class="pdp-notify__btn">Submit</button>
                    </form>
                    <p class="pdp-notify__success" id="pdp-notify-success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        We'll let you know!
                    </p>
                </div>
                <?php endif; ?>
            </form>

            <!-- Features -->
            <div class="pdp-features">
                <div class="pdp-feature">
                    <svg class="pdp-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span class="pdp-feature__text">Secure</span>
                </div>
                <div class="pdp-feature">
                    <svg class="pdp-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span class="pdp-feature__text">Fast Ship</span>
                </div>
                <div class="pdp-feature">
                    <svg class="pdp-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    <span class="pdp-feature__text">Returns</span>
                </div>
                <div class="pdp-feature">
                    <svg class="pdp-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                    <span class="pdp-feature__text">Quality</span>
                </div>
            </div>

            <!-- Share -->
            <div class="pdp-share">
                <span class="pdp-share__label">Share</span>
                <div class="pdp-share__buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('/products/' . $product->slug)) ?>" target="_blank" rel="noopener" class="pdp-share__btn" title="Facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($product->name) ?>&url=<?= urlencode(url('/products/' . $product->slug)) ?>" target="_blank" rel="noopener" class="pdp-share__btn" title="X">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($product->name . ' - ' . url('/products/' . $product->slug)) ?>" target="_blank" rel="noopener" class="pdp-share__btn" title="WhatsApp">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <button type="button" onclick="pdpCopyLink()" class="pdp-share__btn" title="Copy">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </button>
                </div>
            </div>

        </div>
    </section>

    <!-- GALLERY STRIP -->
    <?php if (count($images) > 1): ?>
    <section class="pdp-gallery-strip">
        <h2 class="pdp-gallery-strip__title">Gallery</h2>
        <div class="pdp-gallery-strip__track" id="pdp-gallery-track">
            <?php foreach ($images as $i => $image): ?>
            <div class="pdp-gallery-strip__item" data-index="<?= $i ?>">
                <img src="<?= url('storage/uploads/' . e($image['path'])) ?>" alt="<?= e($product->name) ?>" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- DATA SHEET -->
    <section class="pdp-data">
        <div class="pdp-data__header">
            <div>
                <p class="pdp-data__label">Product Details</p>
                <h2 class="pdp-data__title">Data Sheet</h2>
            </div>
            <?php if ($specCount > 0): ?>
            <span class="pdp-data__count"><?= $specCount ?> specs</span>
            <?php endif; ?>
        </div>

        <?php if ($product->description): ?>
        <div class="pdp-data__desc">
            <p class="pdp-data__desc-text"><?= nl2br(e($product->description)) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($hasSpecs): ?>
        <div class="pdp-specs">
            <?php if (!empty($specifications)): ?>
                <?php foreach ($specifications as $spec): ?>
                <div class="pdp-spec">
                    <span class="pdp-spec__label"><?= e($spec['spec_name']) ?></span>
                    <span class="pdp-spec__value"><?= e($spec['spec_value']) ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php foreach ($groupedAttributes as $attr): ?>
            <div class="pdp-spec">
                <span class="pdp-spec__label"><?= e($attr['name']) ?></span>
                <span class="pdp-spec__value"><?= e(implode(', ', array_column($attr['values'], 'value'))) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if ($product->weight): ?>
            <div class="pdp-spec">
                <span class="pdp-spec__label">Weight</span>
                <span class="pdp-spec__value"><?= $product->weight ?> kg</span>
            </div>
            <?php endif; ?>
            <?php if ($product->length && $product->width && $product->height): ?>
            <div class="pdp-spec">
                <span class="pdp-spec__label">Dimensions</span>
                <span class="pdp-spec__value"><?= $product->length ?> x <?= $product->width ?> x <?= $product->height ?> cm</span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- REVIEWS -->
    <section class="pdp-reviews" id="pdp-reviews">
        <div class="pdp-reviews__header">
            <div class="pdp-reviews__title-wrap">
                <p class="pdp-reviews__label">Customer Feedback</p>
                <h2 class="pdp-reviews__title">Reviews</h2>
            </div>
            <div class="pdp-score">
                <span class="pdp-score__number"><?= number_format($product->rating_average ?? 0, 1) ?></span>
                <div class="pdp-score__meta">
                    <div class="pdp-score__stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg viewBox="0 0 24 24" fill="<?= $i <= round($product->rating_average ?? 0) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <?php endfor; ?>
                    </div>
                    <span class="pdp-score__count"><?= $product->rating_count ?? 0 ?> reviews</span>
                </div>
            </div>
        </div>

        <?php if (auth()): ?>
        <button type="button" class="pdp-btn-review" id="pdp-write-review">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Write Review
        </button>
        <?php else: ?>
        <a href="<?= url('/login?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>" class="pdp-btn-review">Login to Review</a>
        <?php endif; ?>

        <?php if (auth()): ?>
        <div class="pdp-review-form" id="pdp-review-form">
            <form id="pdp-review-submit">
                <input type="hidden" name="product_id" value="<?= $product->id ?>">
                <?= csrfField() ?>

                <div class="pdp-review-form__group">
                    <label class="pdp-review-form__label">Rating</label>
                    <div class="pdp-review-form__stars" id="pdp-rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="pdp-review-form__star" data-rating="<?= $i ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="pdp-rating-value" value="">
                </div>

                <div class="pdp-review-form__group">
                    <label class="pdp-review-form__label">Title</label>
                    <input type="text" name="title" placeholder="Summary" maxlength="100" class="pdp-review-form__input">
                </div>

                <div class="pdp-review-form__group">
                    <label class="pdp-review-form__label">Review</label>
                    <textarea name="content" placeholder="Your experience..." required minlength="20" maxlength="2000" class="pdp-review-form__textarea"></textarea>
                </div>

                <div class="pdp-review-form__actions">
                    <button type="button" class="pdp-btn-cancel" id="pdp-cancel-review">Cancel</button>
                    <button type="submit" class="pdp-btn-submit">Submit</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if (!empty($reviews)): ?>
        <div class="pdp-review-list">
            <?php foreach ($reviews as $review): ?>
            <div class="pdp-review">
                <div class="pdp-review__header">
                    <div class="pdp-review__stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg viewBox="0 0 24 24" fill="<?= $i <= $review['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <?php endfor; ?>
                    </div>
                    <?php if (!empty($review['is_verified'])): ?>
                    <span class="pdp-review__verified">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Verified
                    </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($review['title'])): ?>
                <h4 class="pdp-review__title"><?= e($review['title']) ?></h4>
                <?php endif; ?>
                <p class="pdp-review__text"><?= e($review['content']) ?></p>
                <div class="pdp-review__meta">
                    <span><?= e($review['first_name'] ?? 'Anonymous') ?></span>
                    <span><?= date('M d, Y', strtotime($review['created_at'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="pdp-review-empty">
            <p>No reviews yet. Be the first.</p>
        </div>
        <?php endif; ?>
    </section>

    <!-- RELATED -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="pdp-related">
        <div class="pdp-related__header">
            <h2 class="pdp-related__title">You May Also Like</h2>
            <div class="pdp-related__nav">
                <button type="button" class="pdp-related__btn" id="pdp-related-prev" aria-label="Previous">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button type="button" class="pdp-related__btn" id="pdp-related-next" aria-label="Next">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
        <div class="pdp-related__track" id="pdp-related-track">
            <?php foreach ($relatedProducts as $relProduct): ?>
            <?php $product = $relProduct; ?>
            <div class="pdp-related__slide">
                <?php include APP_PATH . '/Views/components/product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<!-- Lightbox -->
<div class="pdp-lightbox" id="pdp-lightbox">
    <img class="pdp-lightbox__image" id="pdp-lightbox-image" src="" alt="">
    <button type="button" class="pdp-lightbox__close" id="pdp-lightbox-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <button type="button" class="pdp-lightbox__nav pdp-lightbox__prev" id="pdp-lightbox-prev" aria-label="Previous">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button type="button" class="pdp-lightbox__nav pdp-lightbox__next" id="pdp-lightbox-next" aria-label="Next">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
    <div class="pdp-lightbox__counter" id="pdp-lightbox-counter"></div>
</div>

<script>
(function() {
    'use strict';

    const images = <?= json_encode(array_map(fn($img) => url('storage/uploads/' . $img['path']), $images)) ?>;
    let currentIndex = 0;

    // Gallery dots
    const dots = document.querySelectorAll('.pdp-hero__dot');
    const mainImage = document.getElementById('pdp-main-image');

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            currentIndex = parseInt(dot.dataset.index);
            mainImage.src = dot.dataset.image;
            dots.forEach(d => d.classList.remove('is-active'));
            dot.classList.add('is-active');
        });
    });

    // Gallery strip click
    document.querySelectorAll('.pdp-gallery-strip__item').forEach(item => {
        item.addEventListener('click', () => {
            currentIndex = parseInt(item.dataset.index);
            openLightbox();
        });
    });

    // Main image click
    mainImage?.addEventListener('click', openLightbox);

    // Lightbox
    const lightbox = document.getElementById('pdp-lightbox');
    const lightboxImg = document.getElementById('pdp-lightbox-image');
    const lightboxCounter = document.getElementById('pdp-lightbox-counter');

    function openLightbox() {
        lightboxImg.src = images[currentIndex];
        lightboxCounter.textContent = `${currentIndex + 1} / ${images.length}`;
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function navigate(dir) {
        currentIndex = (currentIndex + dir + images.length) % images.length;
        lightboxImg.src = images[currentIndex];
        lightboxCounter.textContent = `${currentIndex + 1} / ${images.length}`;
        // Sync dots
        dots.forEach((d, i) => d.classList.toggle('is-active', i === currentIndex));
        if (mainImage) mainImage.src = images[currentIndex];
    }

    document.getElementById('pdp-lightbox-close')?.addEventListener('click', closeLightbox);
    document.getElementById('pdp-lightbox-prev')?.addEventListener('click', () => navigate(-1));
    document.getElementById('pdp-lightbox-next')?.addEventListener('click', () => navigate(1));
    lightbox?.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });
    document.addEventListener('keydown', e => {
        if (!lightbox?.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigate(-1);
        if (e.key === 'ArrowRight') navigate(1);
    });

    // Quantity
    const qtyInput = document.getElementById('pdp-quantity');
    document.getElementById('pdp-qty-dec')?.addEventListener('click', () => {
        qtyInput.value = Math.max(1, parseInt(qtyInput.value) - 1);
    });
    document.getElementById('pdp-qty-inc')?.addEventListener('click', () => {
        qtyInput.value = Math.min(parseInt(qtyInput.max), parseInt(qtyInput.value) + 1);
    });

    // Variants
    document.querySelectorAll('[data-variant]').forEach(group => {
        const btns = group.querySelectorAll('button');
        const display = document.querySelector(`[data-variant-display="${group.dataset.variant}"]`);
        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                btns.forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                if (display) display.textContent = btn.title || btn.textContent.trim();
            });
        });
    });

    // Add to cart
    document.getElementById('pdp-add-cart')?.addEventListener('click', async function() {
        const btn = this;
        const productId = btn.dataset.productAdd;
        const quantity = parseInt(qtyInput?.value) || 1;

        btn.disabled = true;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<span class="pdp-spinner"></span>';

        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            const response = await fetch(window.Pricetag.baseUrl + '/cart/add', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            if (data.success) {
                window.Pricetag?.Toast?.success('Added to bag');
                window.Pricetag?.Cart?.refresh();
            } else {
                window.Pricetag?.Toast?.error(data.message || 'Error');
            }
        } catch (err) {
            window.Pricetag?.Toast?.error('Something went wrong');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    });

    // Review form
    const reviewForm = document.getElementById('pdp-review-form');
    const writeBtn = document.getElementById('pdp-write-review');
    const cancelBtn = document.getElementById('pdp-cancel-review');

    writeBtn?.addEventListener('click', () => {
        reviewForm?.classList.add('is-open');
        writeBtn.classList.add('pdp-hidden');
    });
    cancelBtn?.addEventListener('click', () => {
        reviewForm?.classList.remove('is-open');
        writeBtn?.classList.remove('pdp-hidden');
    });

    // Rating stars
    const ratingStars = document.querySelectorAll('.pdp-review-form__star');
    const ratingInput = document.getElementById('pdp-rating-value');
    ratingStars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.dataset.rating);
            ratingInput.value = rating;
            ratingStars.forEach((s, i) => {
                s.classList.toggle('is-active', i < rating);
                s.querySelector('svg').setAttribute('fill', i < rating ? 'currentColor' : 'none');
            });
        });
    });

    // Submit review
    document.getElementById('pdp-review-submit')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!ratingInput?.value) {
            window.Pricetag?.Toast?.error('Please select a rating');
            return;
        }
        const formData = new FormData(this);
        try {
            const response = await fetch(window.Pricetag.baseUrl + '/api/reviews', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            if (data.success) {
                window.Pricetag?.Toast?.success('Review submitted');
                setTimeout(() => location.reload(), 1500);
            } else {
                window.Pricetag?.Toast?.error(data.message || 'Error');
            }
        } catch (err) {
            window.Pricetag?.Toast?.error('Something went wrong');
        }
    });

    // Related carousel
    const relatedTrack = document.getElementById('pdp-related-track');
    document.getElementById('pdp-related-prev')?.addEventListener('click', () => {
        relatedTrack?.scrollBy({ left: -300, behavior: 'smooth' });
    });
    document.getElementById('pdp-related-next')?.addEventListener('click', () => {
        relatedTrack?.scrollBy({ left: 300, behavior: 'smooth' });
    });

    // Notify form
    document.getElementById('pdp-notify-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('product_id', <?= $product->id ?? 'null' ?>);
        try {
            const response = await fetch(window.Pricetag.baseUrl + '/api/notify-stock', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                this.classList.add('pdp-hidden');
                document.getElementById('pdp-notify-success')?.classList.add('is-visible');
            }
        } catch (e) {}
    });

    // Track recently viewed
    try {
        const products = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        const current = {
            id: <?= $product->id ?? 'null' ?>,
            name: "<?= e(addslashes($product->name ?? '')) ?>",
            url: "<?= url('/products/' . ($product->slug ?? '')) ?>",
            image: images[0] || '',
            price: "<?= formatPrice($product->price ?? 0) ?>"
        };
        const filtered = products.filter(p => p.id !== current.id);
        filtered.unshift(current);
        localStorage.setItem('recentlyViewed', JSON.stringify(filtered.slice(0, 10)));
    } catch (e) {}
})();

function pdpCopyLink() {
    const url = '<?= url('/products/' . $product->slug) ?>';
    navigator.clipboard?.writeText(url).then(() => {
        window.Pricetag?.Toast?.success('Link copied');
    }).catch(() => {
        window.Pricetag?.Toast?.error('Copy failed');
    });
}
</script>
