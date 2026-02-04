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
    <div class="product-layout">
        <!-- Product Gallery -->
        <div class="product-gallery">
            <div class="product-main-image-wrapper" id="product-gallery">
                <?php $mainImage = $images[0] ?? null; ?>
                <div class="product-main-image" data-zoom>
                    <img id="product-main-image"
                         src="<?= $mainImage ? url('storage/uploads/' . e($mainImage['path'])) : asset('images/placeholder.jpg') ?>"
                         alt="<?= e($product->name) ?>"
                         class="w-full rounded-xl"
                         data-lightbox>
                    <button type="button" class="product-zoom-btn" id="zoom-trigger" aria-label="Zoom image">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            <line x1="11" y1="8" x2="11" y2="14"/>
                            <line x1="8" y1="11" x2="14" y2="11"/>
                        </svg>
                    </button>
                    <!-- Badges -->
                    <div class="product-badges">
                        <?php if ($product->is_on_sale && $product->getDiscountPercentage()): ?>
                        <span class="product-badge product-badge-sale">-<?= $product->getDiscountPercentage() ?>%</span>
                        <?php endif; ?>
                        <?php if ($product->is_new): ?>
                        <span class="product-badge product-badge-new">New</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (count($images) > 1): ?>
            <div class="product-thumbs-container">
                <div class="product-thumbs">
                    <?php foreach ($images as $i => $image): ?>
                    <button type="button"
                            class="product-thumb <?= $i === 0 ? 'is-active' : '' ?>"
                            data-image="<?= url('storage/uploads/' . e($image['path'])) ?>"
                            data-index="<?= $i ?>">
                        <img src="<?= url('storage/uploads/' . e($image['path'])) ?>"
                             alt=""
                             loading="lazy">
                    </button>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="product-thumbs-nav product-thumbs-prev" aria-label="Previous">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>
                <button type="button" class="product-thumbs-nav product-thumbs-next" aria-label="Next">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <?php if ($primaryCategory): ?>
            <a href="<?= url('/categories/' . $primaryCategory['slug']) ?>" class="product-category-link">
                <?= e($primaryCategory['name']) ?>
            </a>
            <?php endif; ?>

            <h1 class="product-title"><?= e($product->name) ?></h1>

            <?php if ($product->sku): ?>
            <p class="product-sku">SKU: <?= e($product->sku) ?></p>
            <?php endif; ?>

            <!-- Rating -->
            <div class="product-rating">
                <div class="product-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $i <= round($product->rating_average ?? 0) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <?php endfor; ?>
                </div>
                <a href="#reviews" class="product-rating-link">
                    <?= $product->rating_count ?? 0 ?> <?= ($product->rating_count ?? 0) === 1 ? 'review' : 'reviews' ?>
                </a>
            </div>

            <!-- Price -->
            <div class="product-price-block">
                <div class="product-price-row">
                    <span id="product-price" class="product-price-current"><?= formatPrice($product->price) ?></span>
                    <?php if ($product->compare_price && $product->compare_price > $product->price): ?>
                    <span class="product-price-original"><?= formatPrice($product->compare_price) ?></span>
                    <span class="product-price-discount">Save <?= formatPrice($product->compare_price - $product->price) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (config('payment.tax.inclusive')): ?>
                <p class="product-price-tax">Price includes VAT</p>
                <?php endif; ?>
            </div>

            <!-- Stock Status -->
            <div class="product-stock">
                <?php if ($product->isInStock()): ?>
                <div class="product-stock-badge in-stock">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span id="stock-status"><?= $product->getStockStatus() ?></span>
                </div>
                <?php else: ?>
                <div class="product-stock-badge out-of-stock">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span id="stock-status">Out of Stock</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Short Description -->
            <?php if ($product->short_description): ?>
            <p class="product-description"><?= e($product->short_description) ?></p>
            <?php endif; ?>

            <!-- Variants / Attributes Selection -->
            <?php
            $groupedAttributes = [];
            foreach ($attributes as $attr) {
                $key = $attr['attribute_slug'] ?? $attr['attribute_name'];
                if (!isset($groupedAttributes[$key])) {
                    $groupedAttributes[$key] = [
                        'name' => $attr['attribute_name'],
                        'values' => []
                    ];
                }
                $groupedAttributes[$key]['values'][] = [
                    'value' => $attr['value'],
                    'slug' => $attr['value_slug'] ?? '',
                    'color' => $attr['color_code'] ?? null
                ];
            }
            ?>
            <?php if (!empty($groupedAttributes)): ?>
            <div class="product-variants">
                <?php foreach ($groupedAttributes as $attrSlug => $attr): ?>
                <div class="product-variant-group">
                    <label class="product-variant-label">
                        <?= e($attr['name']) ?>:
                        <span class="product-variant-selected" data-variant-display="<?= e($attrSlug) ?>">Select</span>
                    </label>
                    <div class="product-variant-options" data-variant="<?= e($attrSlug) ?>">
                        <?php if ($attrSlug === 'color' || $attrSlug === 'colour'): ?>
                        <!-- Color Swatches -->
                        <?php foreach ($attr['values'] as $val): ?>
                        <button type="button"
                                class="product-color-swatch"
                                style="<?= $val['color'] ? 'background-color: ' . e($val['color']) : '' ?>"
                                data-value="<?= e($val['slug'] ?: $val['value']) ?>"
                                title="<?= e($val['value']) ?>"
                                aria-label="<?= e($val['value']) ?>">
                            <?php if (!$val['color']): ?>
                            <span><?= e(substr($val['value'], 0, 2)) ?></span>
                            <?php endif; ?>
                        </button>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <!-- Size/Other Options -->
                        <?php foreach ($attr['values'] as $val): ?>
                        <button type="button"
                                class="product-size-btn"
                                data-value="<?= e($val['slug'] ?: $val['value']) ?>">
                            <?= e($val['value']) ?>
                        </button>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <form id="product-form" class="product-form">
                <input type="hidden" id="product-id" name="product_id" value="<?= $product->id ?>">
                <input type="hidden" id="variant-id" name="variant_id" value="">

                <?php if ($product->isInStock()): ?>
                <!-- Quantity Selector -->
                <div class="product-quantity">
                    <label class="product-quantity-label">Quantity:</label>
                    <div class="product-quantity-control">
                        <button type="button" class="product-qty-btn" id="qty-decrease" aria-label="Decrease quantity">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </button>
                        <input type="number" id="product-quantity" name="quantity" value="1" min="1" max="<?= min(99, $product->stock_quantity ?? 99) ?>" class="product-qty-input" readonly>
                        <button type="button" class="product-qty-btn" id="qty-increase" aria-label="Increase quantity">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="product-actions">
                    <button type="button"
                            id="add-to-cart-btn"
                            class="btn btn-primary btn-lg product-add-btn"
                            data-product-add="<?= $product->id ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <span>Add to Cart</span>
                    </button>
                    <button type="button"
                            class="btn btn-outline btn-lg product-wishlist-btn"
                            data-wishlist-toggle="<?= $product->id ?>"
                            aria-label="Add to wishlist">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>
                </div>
                <?php else: ?>
                <!-- Notify When Back in Stock -->
                <div class="product-notify">
                    <h4 class="product-notify-title">Notify me when available</h4>
                    <p class="product-notify-text">Enter your email to receive a notification when this product is back in stock.</p>
                    <form id="notify-form" class="product-notify-form">
                        <input type="email" name="email" placeholder="Enter your email" required class="product-notify-input">
                        <button type="submit" class="btn btn-primary">Notify Me</button>
                    </form>
                    <p class="product-notify-success" id="notify-success" style="display: none;">
                        We'll email you when it's back!
                    </p>
                </div>
                <?php endif; ?>
            </form>

            <!-- Trust Indicators -->
            <div class="product-trust">
                <div class="product-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Secure Checkout</span>
                </div>
                <div class="product-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <span>Fast Delivery</span>
                </div>
                <div class="product-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="17 1 21 5 17 9"/>
                        <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                        <polyline points="7 23 3 19 7 15"/>
                        <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                    </svg>
                    <span>Easy Returns</span>
                </div>
                <div class="product-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Quality Guaranteed</span>
                </div>
            </div>

            <!-- Social Share Buttons -->
            <div class="product-share mt-6 pt-4 border-t">
                <span class="text-sm text-muted mr-3">Share:</span>
                <div class="inline-flex gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('/products/' . $product->slug)) ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="btn btn-ghost btn-sm btn-icon" title="Share on Facebook">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($product->name) ?>&url=<?= urlencode(url('/products/' . $product->slug)) ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="btn btn-ghost btn-sm btn-icon" title="Share on X (Twitter)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($product->name . ' - ' . url('/products/' . $product->slug)) ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="btn btn-ghost btn-sm btn-icon" title="Share on WhatsApp">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </a>
                    <a href="mailto:?subject=<?= urlencode($product->name) ?>&body=<?= urlencode('Check out this product: ' . url('/products/' . $product->slug)) ?>"
                       class="btn btn-ghost btn-sm btn-icon" title="Share via Email">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </a>
                    <button type="button" onclick="copyProductLink()" class="btn btn-ghost btn-sm btn-icon" title="Copy Link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs -->
    <div class="product-tabs-container" id="product-tabs">
        <div class="product-tabs-nav" role="tablist">
            <button type="button" class="product-tab is-active" role="tab" aria-selected="true" data-tab="description">
                Description
            </button>
            <button type="button" class="product-tab" role="tab" aria-selected="false" data-tab="specs">
                Specifications
            </button>
            <button type="button" class="product-tab" role="tab" aria-selected="false" data-tab="reviews">
                Reviews (<?= $product->rating_count ?? 0 ?>)
            </button>
        </div>

        <div class="product-tabs-content">
            <!-- Description Tab -->
            <div class="product-tab-panel is-active" id="tab-description" role="tabpanel">
                <?php if ($product->description): ?>
                <div class="prose">
                    <?= nl2br(e($product->description)) ?>
                </div>
                <?php else: ?>
                <p class="text-muted">No description available.</p>
                <?php endif; ?>
            </div>

            <!-- Specifications Tab -->
            <div class="product-tab-panel" id="tab-specs" role="tabpanel">
                <?php $hasSpecs = !empty($specifications) || !empty($attributes) || $product->weight || ($product->length && $product->width && $product->height); ?>
                <?php if ($hasSpecs): ?>
                <table class="product-specs-table">
                    <tbody>
                        <?php if (!empty($specifications)): ?>
                        <?php foreach ($specifications as $spec): ?>
                        <tr>
                            <th><?= e($spec['spec_name']) ?></th>
                            <td><?= e($spec['spec_value']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($groupedAttributes)): ?>
                        <?php foreach ($groupedAttributes as $attrSlug => $attr): ?>
                        <tr>
                            <th><?= e($attr['name']) ?></th>
                            <td><?= e(implode(', ', array_column($attr['values'], 'value'))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($product->weight): ?>
                        <tr>
                            <th>Weight</th>
                            <td><?= $product->weight ?> kg</td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($product->length && $product->width && $product->height): ?>
                        <tr>
                            <th>Dimensions</th>
                            <td><?= $product->length ?> × <?= $product->width ?> × <?= $product->height ?> cm</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">No specifications available.</p>
                <?php endif; ?>
            </div>

            <!-- Reviews Tab -->
            <div class="product-tab-panel" id="tab-reviews" role="tabpanel">
                <div id="reviews">
                    <!-- Review Summary -->
                    <div class="review-summary">
                        <div class="review-summary-score">
                            <span class="review-score-value"><?= number_format($product->rating_average ?? 0, 1) ?></span>
                            <div class="review-score-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $i <= round($product->rating_average ?? 0) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                                <?php endfor; ?>
                            </div>
                            <span class="review-score-count">Based on <?= $product->rating_count ?? 0 ?> reviews</span>
                        </div>
                        <?php if (auth()): ?>
                        <button type="button" class="btn btn-outline" id="write-review-btn">Write a Review</button>
                        <?php else: ?>
                        <a href="<?= url('/login?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>" class="btn btn-outline">Login to Review</a>
                        <?php endif; ?>
                    </div>

                    <!-- Write Review Form -->
                    <?php if (auth()): ?>
                    <div class="review-form-container" id="review-form-container" style="display: none;">
                        <form id="review-form" class="review-form">
                            <input type="hidden" name="product_id" value="<?= $product->id ?>">
                            <?= csrfField() ?>

                            <div class="review-form-rating">
                                <label>Your Rating *</label>
                                <div class="rating-input" id="rating-input">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" class="rating-star" data-rating="<?= $i ?>" aria-label="<?= $i ?> star">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    </button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="rating-value" value="" required>
                            </div>

                            <div class="review-form-field">
                                <label for="review-title">Review Title</label>
                                <input type="text" id="review-title" name="title" placeholder="Sum it up in a few words" maxlength="100">
                            </div>

                            <div class="review-form-field">
                                <label for="review-content">Your Review *</label>
                                <textarea id="review-content" name="content" rows="4" placeholder="Tell us about your experience with this product" required minlength="20" maxlength="2000"></textarea>
                            </div>

                            <div class="review-form-actions">
                                <button type="button" class="btn btn-outline" id="cancel-review">Cancel</button>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Review List -->
                    <?php if (!empty($reviews)): ?>
                    <div class="review-list">
                        <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $i <= $review['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    <?php endfor; ?>
                                </div>
                                <?php if (!empty($review['is_verified'])): ?>
                                <span class="review-verified">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                        <polyline points="22 4 12 14.01 9 11.01"/>
                                    </svg>
                                    Verified Purchase
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($review['title'])): ?>
                            <h4 class="review-title"><?= e($review['title']) ?></h4>
                            <?php endif; ?>
                            <p class="review-content"><?= e($review['content']) ?></p>
                            <div class="review-meta">
                                <span class="review-author"><?= e($review['first_name'] ?? 'Anonymous') ?></span>
                                <span class="review-date"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="review-empty">
                        <p>No reviews yet. Be the first to review this product!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="product-related">
        <h2 class="product-related-title">You May Also Like</h2>
        <div class="product-carousel" data-carousel="related">
            <div class="product-carousel-track">
                <?php foreach ($relatedProducts as $relProduct): ?>
                <?php $product = $relProduct; ?>
                <div class="product-carousel-slide">
                    <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-btn carousel-btn-prev" data-carousel-prev aria-label="Previous">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </button>
            <button class="carousel-btn carousel-btn-next" data-carousel-next aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- Lightbox Modal -->
<div class="lightbox-modal" id="lightbox" aria-hidden="true">
    <div class="lightbox-overlay"></div>
    <div class="lightbox-content">
        <button type="button" class="lightbox-close" aria-label="Close">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <button type="button" class="lightbox-nav lightbox-prev" aria-label="Previous image">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>
        <img class="lightbox-image" id="lightbox-image" src="" alt="">
        <button type="button" class="lightbox-nav lightbox-next" aria-label="Next image">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>
        <div class="lightbox-counter" id="lightbox-counter"></div>
    </div>
</div>

<style>
/* Product Layout - 2 columns on desktop */
.product-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 1024px) {
    .product-layout {
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
    }
}

/* Product Gallery */
.product-gallery {
    position: relative;
}

.product-main-image-wrapper {
    position: relative;
    margin-bottom: var(--space-4);
}

.product-main-image {
    position: relative;
    overflow: hidden;
    border-radius: var(--radius-2xl);
    background: var(--color-background-alt);
    cursor: zoom-in;
}

.product-main-image img {
    max-height: 450px;
    width: auto;
    max-width: 100%;
    display: block;
    margin: 0 auto;
    object-fit: contain;
    transition: transform var(--duration-300);
}

.product-main-image:hover img {
    transform: scale(1.05);
}

.product-zoom-btn {
    position: absolute;
    bottom: var(--space-4);
    right: var(--space-4);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-background);
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-lg);
    z-index: 5;
    transition: var(--transition-all);
}

.product-zoom-btn:hover {
    background: var(--color-primary);
    color: white;
}

.product-badges {
    position: absolute;
    top: var(--space-4);
    left: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    z-index: 5;
}

.product-badge {
    padding: var(--space-1) var(--space-3);
    font-size: var(--text-xs);
    font-weight: var(--font-bold);
    border-radius: var(--radius-full);
}

.product-badge-sale {
    background: var(--color-danger);
    color: white;
}

.product-badge-new {
    background: var(--color-primary);
    color: white;
}

.product-thumbs-container {
    position: relative;
}

.product-thumbs {
    display: flex;
    gap: var(--space-2);
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
}

.product-thumbs::-webkit-scrollbar {
    display: none;
}

.product-thumb {
    flex-shrink: 0;
    width: 72px;
    height: 72px;
    border-radius: var(--radius-lg);
    overflow: hidden;
    border: 2px solid transparent;
    transition: var(--transition-all);
    scroll-snap-align: start;
}

.product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-thumb:hover,
.product-thumb.is-active {
    border-color: var(--color-primary);
}

.product-thumbs-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-sm);
    z-index: 5;
}

.product-thumbs-prev {
    left: -14px;
}

.product-thumbs-next {
    right: -14px;
}

/* Product Info */
.product-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.product-category-link {
    font-size: var(--text-sm);
    color: var(--color-primary);
    font-weight: var(--font-medium);
    text-transform: uppercase;
    letter-spacing: var(--tracking-wide);
}

.product-title {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: var(--font-bold);
    line-height: var(--leading-tight);
}

.product-sku {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

.product-rating {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.product-stars {
    display: flex;
    color: var(--color-accent);
}

.product-rating-link {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

.product-rating-link:hover {
    color: var(--color-primary);
    text-decoration: underline;
}

/* Price Block */
.product-price-block {
    padding: var(--space-4);
    background: var(--color-background-alt);
    border-radius: var(--radius-xl);
}

.product-price-row {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: var(--space-3);
}

.product-price-current {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
}

.product-price-original {
    font-size: var(--text-lg);
    color: var(--color-text-muted);
    text-decoration: line-through;
}

.product-price-discount {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-danger);
    background: var(--color-danger-50, #fee2e2);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
}

.product-price-tax {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    margin-top: var(--space-1);
}

/* Stock Status */
.product-stock-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    border-radius: var(--radius-full);
}

.product-stock-badge.in-stock {
    background: var(--color-success-100, #dcfce7);
    color: var(--color-success-700, #15803d);
}

.product-stock-badge.out-of-stock {
    background: var(--color-danger-100, #fee2e2);
    color: var(--color-danger-700, #b91c1c);
}

.product-description {
    font-size: var(--text-base);
    color: var(--color-text-muted);
    line-height: var(--leading-relaxed);
}

/* Variants */
.product-variants {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.product-variant-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.product-variant-label {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
}

.product-variant-selected {
    color: var(--color-text-muted);
    margin-left: var(--space-1);
}

.product-variant-options {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}

.product-color-swatch {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    border: 2px solid var(--color-border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: var(--font-bold);
    transition: var(--transition-all);
}

.product-color-swatch:hover,
.product-color-swatch.is-active {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px var(--color-primary-100);
}

.product-size-btn {
    min-width: 48px;
    padding: var(--space-2) var(--space-4);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: var(--transition-all);
}

.product-size-btn:hover,
.product-size-btn.is-active {
    border-color: var(--color-primary);
    background: var(--color-primary);
    color: white;
}

.product-size-btn.is-disabled {
    opacity: 0.5;
    text-decoration: line-through;
    cursor: not-allowed;
}

/* Quantity */
.product-quantity {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.product-quantity-label {
    font-weight: var(--font-medium);
}

.product-quantity-control {
    display: flex;
    align-items: center;
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
}

.product-qty-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
    transition: var(--transition-colors);
}

.product-qty-btn:hover {
    color: var(--color-text);
    background: var(--color-background-alt);
}

.product-qty-input {
    width: 56px;
    height: 44px;
    text-align: center;
    font-size: var(--text-base);
    font-weight: var(--font-medium);
    border: none;
    border-left: var(--border-1) solid var(--color-border);
    border-right: var(--border-1) solid var(--color-border);
}

/* Actions */
.product-actions {
    display: flex;
    gap: var(--space-3);
}

.product-add-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
}

.product-wishlist-btn {
    flex-shrink: 0;
    width: 56px;
}

/* Notify */
.product-notify {
    padding: var(--space-6);
    background: var(--color-background-alt);
    border-radius: var(--radius-xl);
}

.product-notify-title {
    font-size: var(--text-lg);
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-2);
}

.product-notify-text {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    margin-bottom: var(--space-4);
}

.product-notify-form {
    display: flex;
    gap: var(--space-2);
}

.product-notify-input {
    flex: 1;
    padding: var(--space-3);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
}

.product-notify-success {
    margin-top: var(--space-3);
    font-size: var(--text-sm);
    color: var(--color-success);
}

/* Trust */
.product-trust {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-3);
    padding-top: var(--space-6);
    border-top: var(--border-1) solid var(--color-border);
}

.product-trust-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

.product-trust-item svg {
    color: var(--color-success);
    flex-shrink: 0;
}

/* Tabs */
.product-tabs-container {
    margin-top: var(--space-16);
}

.product-tabs-nav {
    display: flex;
    border-bottom: var(--border-1) solid var(--color-border);
    overflow-x: auto;
}

.product-tab {
    padding: var(--space-4) var(--space-6);
    font-size: var(--text-base);
    font-weight: var(--font-medium);
    color: var(--color-text-muted);
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    transition: var(--transition-colors);
}

.product-tab:hover {
    color: var(--color-text);
}

.product-tab.is-active {
    color: var(--color-primary);
    border-bottom-color: var(--color-primary);
}

.product-tabs-content {
    padding-top: var(--space-8);
}

.product-tab-panel {
    display: none;
}

.product-tab-panel.is-active {
    display: block;
}

.prose {
    line-height: var(--leading-relaxed);
}

/* Specs Table */
.product-specs-table {
    width: 100%;
    border-collapse: collapse;
}

.product-specs-table th,
.product-specs-table td {
    padding: var(--space-3) var(--space-4);
    text-align: left;
    border-bottom: var(--border-1) solid var(--color-border);
}

.product-specs-table th {
    font-weight: var(--font-medium);
    color: var(--color-text-muted);
    width: 200px;
}

/* Reviews */
.review-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--space-4);
    padding-bottom: var(--space-6);
    border-bottom: var(--border-1) solid var(--color-border);
    margin-bottom: var(--space-6);
}

.review-summary-score {
    text-align: center;
}

.review-score-value {
    font-size: var(--text-4xl);
    font-weight: var(--font-bold);
    display: block;
}

.review-score-stars {
    display: flex;
    justify-content: center;
    color: var(--color-accent);
    margin: var(--space-2) 0;
}

.review-score-count {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

/* Review Form */
.review-form-container {
    background: var(--color-background-alt);
    padding: var(--space-6);
    border-radius: var(--radius-xl);
    margin-bottom: var(--space-6);
}

.review-form-rating {
    margin-bottom: var(--space-4);
}

.review-form-rating label {
    display: block;
    font-weight: var(--font-medium);
    margin-bottom: var(--space-2);
}

.rating-input {
    display: flex;
    gap: var(--space-1);
}

.rating-star {
    color: var(--color-border);
    transition: var(--transition-colors);
}

.rating-star:hover,
.rating-star.is-active {
    color: var(--color-accent);
}

.rating-star:hover svg,
.rating-star.is-active svg {
    fill: currentColor;
}

.review-form-field {
    margin-bottom: var(--space-4);
}

.review-form-field label {
    display: block;
    font-weight: var(--font-medium);
    margin-bottom: var(--space-2);
}

.review-form-field input,
.review-form-field textarea {
    width: 100%;
    padding: var(--space-3);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
}

.review-form-actions {
    display: flex;
    gap: var(--space-3);
    justify-content: flex-end;
}

/* Review List */
.review-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.review-item {
    padding: var(--space-6);
    background: var(--color-background-alt);
    border-radius: var(--radius-xl);
}

.review-header {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    margin-bottom: var(--space-3);
}

.review-stars {
    display: flex;
    color: var(--color-accent);
}

.review-verified {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--text-xs);
    color: var(--color-success);
}

.review-title {
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-2);
}

.review-content {
    color: var(--color-text-muted);
    line-height: var(--leading-relaxed);
}

.review-meta {
    display: flex;
    gap: var(--space-3);
    margin-top: var(--space-3);
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

.review-empty {
    text-align: center;
    padding: var(--space-12);
    color: var(--color-text-muted);
}

/* Related Products */
.product-related {
    margin-top: var(--space-16);
}

.product-related-title {
    font-size: var(--text-xl);
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-6);
}

/* Lightbox */
.lightbox-modal {
    position: fixed;
    inset: 0;
    z-index: var(--z-modal);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition-opacity);
}

.lightbox-modal.is-open {
    opacity: 1;
    visibility: visible;
}

.lightbox-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
}

.lightbox-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lightbox-image {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
}

.lightbox-close {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-full);
    transition: var(--transition-colors);
}

.lightbox-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-full);
    transition: var(--transition-colors);
}

.lightbox-nav:hover {
    background: rgba(255, 255, 255, 0.2);
}

.lightbox-prev {
    left: var(--space-4);
}

.lightbox-next {
    right: var(--space-4);
}

.lightbox-counter {
    position: absolute;
    bottom: var(--space-4);
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: var(--text-sm);
}

/* Gallery is now relative, no sticky behavior */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initGallery();
    initVariants();
    initQuantity();
    initTabs();
    initReviewForm();
    initLightbox();
    trackRecentlyViewed();
    initNotifyForm();
    initAddToCart();
});

// Copy product link to clipboard
function copyProductLink() {
    const url = '<?= url('/products/' . $product->slug) ?>';
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
            showToast('Link copied to clipboard!');
        }).catch(function() {
            fallbackCopy(url);
        });
    } else {
        fallbackCopy(url);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showToast('Link copied to clipboard!');
    } catch (err) {
        showToast('Failed to copy link', 'error');
    }
    document.body.removeChild(textarea);
}

function showToast(message, type = 'success') {
    // Check if toast container exists
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.style.cssText = 'background:' + (type === 'error' ? '#ef4444' : '#22c55e') + ';color:#fff;padding:12px 20px;border-radius:8px;margin-top:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

// Gallery
function initGallery() {
    const mainImage = document.getElementById('product-main-image');
    const thumbs = document.querySelectorAll('.product-thumb');
    const thumbsContainer = document.querySelector('.product-thumbs');
    const prevBtn = document.querySelector('.product-thumbs-prev');
    const nextBtn = document.querySelector('.product-thumbs-next');

    if (!mainImage || !thumbs.length) return;

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            mainImage.src = thumb.dataset.image;
            thumbs.forEach(t => t.classList.remove('is-active'));
            thumb.classList.add('is-active');
        });
    });

    if (prevBtn && nextBtn && thumbsContainer) {
        prevBtn.addEventListener('click', () => {
            thumbsContainer.scrollBy({ left: -150, behavior: 'smooth' });
        });
        nextBtn.addEventListener('click', () => {
            thumbsContainer.scrollBy({ left: 150, behavior: 'smooth' });
        });
    }
}

// Variants
function initVariants() {
    document.querySelectorAll('.product-variant-options').forEach(group => {
        const buttons = group.querySelectorAll('button');
        const variantName = group.dataset.variant;
        const display = document.querySelector(`[data-variant-display="${variantName}"]`);

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                if (display) {
                    display.textContent = btn.title || btn.textContent;
                }
            });
        });
    });
}

// Quantity
function initQuantity() {
    const input = document.getElementById('product-quantity');
    const decreaseBtn = document.getElementById('qty-decrease');
    const increaseBtn = document.getElementById('qty-increase');

    if (!input) return;

    const min = parseInt(input.min) || 1;
    const max = parseInt(input.max) || 99;

    if (decreaseBtn) {
        decreaseBtn.addEventListener('click', () => {
            input.value = Math.max(min, parseInt(input.value) - 1);
        });
    }

    if (increaseBtn) {
        increaseBtn.addEventListener('click', () => {
            input.value = Math.min(max, parseInt(input.value) + 1);
        });
    }
}

// Tabs
function initTabs() {
    const tabs = document.querySelectorAll('.product-tab');
    const panels = document.querySelectorAll('.product-tab-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            tabs.forEach(t => {
                t.classList.remove('is-active');
                t.setAttribute('aria-selected', 'false');
            });
            panels.forEach(p => p.classList.remove('is-active'));

            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');
            document.getElementById(`tab-${target}`)?.classList.add('is-active');
        });
    });
}

// Review Form
function initReviewForm() {
    const writeBtn = document.getElementById('write-review-btn');
    const cancelBtn = document.getElementById('cancel-review');
    const formContainer = document.getElementById('review-form-container');
    const form = document.getElementById('review-form');
    const ratingStars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('rating-value');

    if (writeBtn && formContainer) {
        writeBtn.addEventListener('click', () => {
            formContainer.style.display = 'block';
            writeBtn.style.display = 'none';
        });
    }

    if (cancelBtn && formContainer && writeBtn) {
        cancelBtn.addEventListener('click', () => {
            formContainer.style.display = 'none';
            writeBtn.style.display = 'inline-flex';
        });
    }

    // Star rating
    ratingStars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.dataset.rating);
            ratingInput.value = rating;

            ratingStars.forEach((s, i) => {
                s.classList.toggle('is-active', i < rating);
            });
        });

        star.addEventListener('mouseenter', () => {
            const rating = parseInt(star.dataset.rating);
            ratingStars.forEach((s, i) => {
                s.classList.toggle('is-active', i < rating);
            });
        });
    });

    document.querySelector('.rating-input')?.addEventListener('mouseleave', () => {
        const currentRating = parseInt(ratingInput.value) || 0;
        ratingStars.forEach((s, i) => {
            s.classList.toggle('is-active', i < currentRating);
        });
    });

    // Form submit
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!ratingInput.value) {
                alert('Please select a rating');
                return;
            }

            const formData = new FormData(form);
            try {
                const response = await fetch(window.Pricetag.baseUrl + '/api/reviews', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    window.Pricetag.Toast.success('Thank you for your review!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    window.Pricetag.Toast.error(data.message || 'Failed to submit review');
                }
            } catch (err) {
                window.Pricetag.Toast.error('Something went wrong');
            }
        });
    }
}

// Lightbox
function initLightbox() {
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxCounter = document.getElementById('lightbox-counter');
    const zoomTrigger = document.getElementById('zoom-trigger');
    const mainImage = document.getElementById('product-main-image');
    const thumbs = document.querySelectorAll('.product-thumb');

    if (!lightbox || !mainImage) return;

    let currentIndex = 0;
    const images = Array.from(thumbs).map(t => t.dataset.image);
    if (images.length === 0) images.push(mainImage.src);

    function openLightbox(index = 0) {
        currentIndex = index;
        lightboxImage.src = images[currentIndex];
        lightboxCounter.textContent = `${currentIndex + 1} / ${images.length}`;
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function navigate(direction) {
        currentIndex = (currentIndex + direction + images.length) % images.length;
        lightboxImage.src = images[currentIndex];
        lightboxCounter.textContent = `${currentIndex + 1} / ${images.length}`;
    }

    // Events
    zoomTrigger?.addEventListener('click', () => openLightbox(currentIndex));
    mainImage.addEventListener('click', () => openLightbox(currentIndex));

    thumbs.forEach((thumb, index) => {
        thumb.addEventListener('click', () => {
            currentIndex = index;
        });
    });

    lightbox.querySelector('.lightbox-close')?.addEventListener('click', closeLightbox);
    lightbox.querySelector('.lightbox-overlay')?.addEventListener('click', closeLightbox);
    lightbox.querySelector('.lightbox-prev')?.addEventListener('click', () => navigate(-1));
    lightbox.querySelector('.lightbox-next')?.addEventListener('click', () => navigate(1));

    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigate(-1);
        if (e.key === 'ArrowRight') navigate(1);
    });
}

// Track Recently Viewed
function trackRecentlyViewed() {
    try {
        const products = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        const currentProduct = {
            id: <?= $product->id ?? 'null' ?>,
            name: "<?= e(addslashes($product->name ?? '')) ?>",
            url: "<?= url('/products/' . ($product->slug ?? '')) ?>",
            image: "<?= $images[0]['path'] ?? '' ? url('storage/uploads/' . e($images[0]['path'] ?? '')) : '' ?>",
            price: "<?= formatPrice($product->price ?? 0) ?>"
        };

        // Remove if already exists
        const filtered = products.filter(p => p.id !== currentProduct.id);

        // Add to beginning
        filtered.unshift(currentProduct);

        // Keep only last 10
        localStorage.setItem('recentlyViewed', JSON.stringify(filtered.slice(0, 10)));
    } catch (e) {}
}

// Notify Form
function initNotifyForm() {
    const form = document.getElementById('notify-form');
    const success = document.getElementById('notify-success');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        formData.append('product_id', <?= $product->id ?? 'null' ?>);

        try {
            const response = await fetch(window.Pricetag.baseUrl + '/api/notify-stock', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                form.style.display = 'none';
                success.style.display = 'block';
            }
        } catch (e) {}
    });
}

// Add to Cart
function initAddToCart() {
    const btn = document.querySelector('[data-product-add]');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const productId = btn.dataset.productAdd;
        const quantity = parseInt(document.getElementById('product-quantity')?.value) || 1;
        const variantId = document.getElementById('variant-id')?.value || null;

        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"></path></svg> Adding...';

        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            if (variantId) formData.append('variant_id', variantId);

            const response = await fetch(window.Pricetag.baseUrl + '/cart/add', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            if (data.success) {
                window.Pricetag.Toast.success('Added to cart!');
                window.Pricetag.Cart?.refresh();
            } else {
                window.Pricetag.Toast.error(data.message || 'Failed to add to cart');
            }
        } catch (err) {
            window.Pricetag.Toast.error('Something went wrong');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><span>Add to Cart</span>';
        }
    });
}
</script>
