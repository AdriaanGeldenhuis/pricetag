<!-- Product Page CSS -->
<link rel="stylesheet" href="<?= asset('css/product-page.css') ?>?v=<?= filemtime(PUBLIC_PATH . '/assets/css/product-page.css') ?>">

<!-- Product Detail Page -->
<div class="pt-page">

    <!-- Breadcrumb Rings -->
    <nav class="breadcrumb-rings">
        <div class="container">
            <div class="breadcrumb-rings-list">
                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?>
                    <span class="bc-ring-sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
                    <?php endif; ?>
                    <?php if (!$crumb['url']): ?>
                    <span class="bc-ring-item bc-ring-active">
                        <div class="bc-ring-wrap">
                            <div class="bc-ring-border"></div>
                            <div class="bc-ring-inner"><span><?= e(mb_substr($crumb['name'], 0, 1)) ?></span></div>
                        </div>
                        <span class="bc-ring-label"><?= e($crumb['name']) ?></span>
                    </span>
                    <?php elseif ($i === 0): ?>
                    <a href="<?= e($crumb['url']) ?>" class="bc-ring-item">
                        <div class="bc-ring-wrap">
                            <div class="bc-ring-border"></div>
                            <div class="bc-ring-inner">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                        </div>
                        <span class="bc-ring-label"><?= e($crumb['name']) ?></span>
                    </a>
                    <?php else: ?>
                    <a href="<?= e($crumb['url']) ?>" class="bc-ring-item">
                        <div class="bc-ring-wrap">
                            <div class="bc-ring-border"></div>
                            <div class="bc-ring-inner"><span><?= e(mb_substr($crumb['name'], 0, 1)) ?></span></div>
                        </div>
                        <span class="bc-ring-label"><?= e($crumb['name']) ?></span>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <!-- ===== PRODUCT SHOWCASE ===== -->
    <section class="pt-showcase">

        <!-- Product Content (inside container) -->
        <div class="container">
            <div class="pt-hero">

                <!-- Gallery with thumbnails below -->
                <div class="pt-gallery" id="pt-gallery">
                    <div class="pt-gallery-main">
                        <?php $mainImage = $images[0] ?? null; ?>

                        <!-- Badges -->
                        <div class="pt-gallery-badges">
                            <?php if ($product->is_on_sale && $product->getDiscountPercentage()): ?>
                            <span class="pt-badge pt-badge-sale">-<?= $product->getDiscountPercentage() ?>%</span>
                            <?php endif; ?>
                            <?php if ($product->is_new): ?>
                            <span class="pt-badge pt-badge-new">New</span>
                            <?php endif; ?>
                        </div>

                        <div class="pt-gallery-image-container" data-lightbox-trigger>
                            <img id="pt-main-image"
                                 src="<?= $mainImage ? url('storage/uploads/' . e($mainImage['path'])) : asset('images/placeholder.jpg') ?>"
                                 alt="<?= e($product->name) ?>"
                                 class="pt-gallery-image">
                        </div>

                        <button type="button" class="pt-gallery-control" id="pt-zoom-btn" aria-label="Zoom image">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                <line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Thumbnail Rings below main image -->
                    <?php if (count($images) > 1): ?>
                    <div class="pt-thumb-rings" id="pt-thumbs">
                        <?php foreach ($images as $i => $image): ?>
                        <button type="button"
                                class="pt-thumb-ring-item <?= $i === 0 ? 'is-active' : '' ?>"
                                data-image="<?= url('storage/uploads/' . e($image['path'])) ?>"
                                data-index="<?= $i ?>">
                            <div class="pt-thumb-ring">
                                <div class="pt-thumb-ring-border"></div>
                                <div class="pt-thumb-ring-image">
                                    <img src="<?= url('storage/uploads/' . e($image['path'])) ?>" alt="" loading="lazy">
                                </div>
                            </div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="pt-product-info">

                    <!-- Category -->
                    <?php if ($primaryCategory): ?>
                    <a href="<?= url('/categories/' . $primaryCategory['slug']) ?>" class="pt-category-link">
                        <?= e($primaryCategory['name']) ?>
                    </a>
                    <?php endif; ?>

                    <h1 class="pt-product-title"><?= e($product->name) ?></h1>

                    <!-- Rating + SKU row -->
                    <div class="pt-meta-row">
                        <div class="pt-rating">
                            <div class="pt-rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="pt-star <?= $i <= round($product->rating_average ?? 0) ? 'is-filled' : '' ?>" viewBox="0 0 24 24" fill="<?= $i <= round($product->rating_average ?? 0) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                                <?php endfor; ?>
                            </div>
                            <a href="#reviews" class="pt-rating-link">
                                <?= $product->rating_count ?? 0 ?> <?= ($product->rating_count ?? 0) === 1 ? 'Review' : 'Reviews' ?>
                            </a>
                        </div>
                        <?php if ($product->sku): ?>
                        <span class="pt-sku">SKU: <?= e($product->sku) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Divider -->
                    <div class="pt-divider"></div>

                    <!-- Price Zone - no card, just bold display -->
                    <div class="pt-price-zone">
                        <span id="pt-price" class="pt-price"><?= formatPrice($product->price) ?></span>
                        <?php if ($product->compare_price && $product->compare_price > $product->price): ?>
                        <span class="pt-price-was"><?= formatPrice($product->compare_price) ?></span>
                        <span class="pt-price-save">Save <?= formatPrice($product->compare_price - $product->price) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (config('payment.tax.inclusive')): ?>
                    <p class="pt-tax-note">Price includes VAT</p>
                    <?php endif; ?>

                    <!-- Stock -->
                    <div class="pt-stock <?= $product->isInStock() ? ($product->stock_quantity <= ($product->low_stock_threshold ?? 5) ? 'pt-stock-low' : 'pt-stock-in') : 'pt-stock-out' ?>">
                        <span class="pt-stock-indicator"></span>
                        <span id="pt-stock-status"><?= $product->isInStock() ? $product->getStockStatus() : 'Out of Stock' ?></span>
                    </div>

                    <!-- Short Description -->
                    <?php if ($product->short_description): ?>
                    <div class="pt-short-desc"><?= nl2br(e($product->short_description)) ?></div>
                    <?php endif; ?>

                    <!-- Variants -->
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
                    <div class="pt-variants">
                        <?php foreach ($groupedAttributes as $attrSlug => $attr): ?>
                        <div class="pt-variant-group">
                            <label class="pt-variant-label">
                                <?= e($attr['name']) ?>: <span class="pt-variant-selected" data-variant-display="<?= e($attrSlug) ?>">Select</span>
                            </label>
                            <div class="pt-variant-options" data-variant="<?= e($attrSlug) ?>">
                                <?php if ($attrSlug === 'color' || $attrSlug === 'colour'): ?>
                                <?php foreach ($attr['values'] as $val): ?>
                                <button type="button" class="pt-color-swatch"
                                        style="<?= $val['color'] ? 'background-color: ' . e($val['color']) : '' ?>"
                                        data-value="<?= e($val['slug'] ?: $val['value']) ?>"
                                        title="<?= e($val['value']) ?>" aria-label="<?= e($val['value']) ?>"></button>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <?php foreach ($attr['values'] as $val): ?>
                                <button type="button" class="pt-size-btn"
                                        data-value="<?= e($val['slug'] ?: $val['value']) ?>"><?= e($val['value']) ?></button>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Purchase Area -->
                    <form id="pt-product-form">
                        <input type="hidden" id="pt-product-id" name="product_id" value="<?= $product->id ?>">
                        <input type="hidden" id="pt-variant-id" name="variant_id" value="">

                        <?php if ($product->isInStock()): ?>
                        <div class="pt-purchase">
                            <div class="pt-quantity">
                                <div class="pt-quantity-control">
                                    <button type="button" class="pt-qty-btn" id="pt-qty-decrease" aria-label="Decrease">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    </button>
                                    <input type="number" id="pt-quantity" name="quantity" value="1" min="1" max="<?= min(99, $product->stock_quantity ?? 99) ?>" class="pt-qty-input" readonly>
                                    <button type="button" class="pt-qty-btn" id="pt-qty-increase" aria-label="Increase">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="pt-actions">
                                <button type="button" id="pt-add-cart-btn" class="pt-btn-cart" data-product-add="<?= $product->id ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                    <span>Add to Cart</span>
                                </button>
                                <button type="button" class="pt-btn-wish" data-wishlist-toggle="<?= $product->id ?>" aria-label="Add to wishlist">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                </button>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="pt-notify-card">
                            <h4 class="pt-notify-title">Get Notified When Available</h4>
                            <p class="pt-notify-text">Enter your email and we'll let you know when this product is back in stock.</p>
                            <form id="pt-notify-form" class="pt-notify-form">
                                <input type="email" name="email" placeholder="your@email.com" required class="pt-notify-input">
                                <button type="submit" class="pt-btn-primary">Notify Me</button>
                            </form>
                            <p class="pt-notify-success pt-hidden" id="pt-notify-success">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                We'll email you when it's back!
                            </p>
                        </div>
                        <?php endif; ?>
                    </form>

                    <!-- Trust Strip -->
                    <div class="pt-trust-strip">
                        <div class="pt-trust-badge" title="Secure Checkout">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                            <span>Secure</span>
                        </div>
                        <div class="pt-trust-badge" title="Fast Delivery">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                            <span>Fast Delivery</span>
                        </div>
                        <div class="pt-trust-badge" title="Easy Returns">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            <span>Easy Returns</span>
                        </div>
                        <div class="pt-trust-badge" title="Quality Guarantee">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                            <span>Guaranteed</span>
                        </div>
                    </div>

                    <!-- Share -->
                    <div class="pt-share-row">
                        <span class="pt-share-label">Share:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('/products/' . $product->slug)) ?>" target="_blank" rel="noopener noreferrer" class="pt-share-btn" title="Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($product->name) ?>&url=<?= urlencode(url('/products/' . $product->slug)) ?>" target="_blank" rel="noopener noreferrer" class="pt-share-btn" title="X">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($product->name . ' - ' . url('/products/' . $product->slug)) ?>" target="_blank" rel="noopener noreferrer" class="pt-share-btn" title="WhatsApp">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <button type="button" onclick="ptCopyLink()" class="pt-share-btn" title="Copy Link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </button>
                    </div>
                </div>

            </div><!-- /.pt-hero -->
        </div><!-- /.container -->

        <!-- Right Sidebar: Widget Rings (fixed to right screen edge) -->
        <aside class="pt-sidebar-right">
                <?php
                $sidebarLocation = 'product_sidebar';
                $sidebarBanners = getBanners($sidebarLocation);
                if (!empty($sidebarBanners)):
                    foreach ($sidebarBanners as $sBanner):
                ?>
                <a href="<?= e($sBanner['url'] ?? '#') ?>" class="pt-sidebar-ring-item">
                    <div class="pt-sidebar-ring">
                        <div class="pt-sidebar-ring-border"></div>
                        <div class="pt-sidebar-ring-image">
                            <?php if (!empty($sBanner['image'])): ?>
                            <img src="<?= url('storage/uploads/' . e($sBanner['image'])) ?>" alt="<?= e($sBanner['title'] ?? '') ?>" loading="lazy">
                            <?php else: ?>
                            <span class="pt-sidebar-ring-placeholder"><?= e(mb_substr($sBanner['title'] ?? '?', 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="pt-sidebar-ring-label"><?= e($sBanner['title'] ?? '') ?></span>
                </a>
                <?php
                    endforeach;
                endif;
                ?>
                <?php
                $genericBanners = getBanners('sidebar');
                if (!empty($genericBanners)):
                    foreach ($genericBanners as $sBanner):
                ?>
                <a href="<?= e($sBanner['url'] ?? '#') ?>" class="pt-sidebar-ring-item">
                    <div class="pt-sidebar-ring">
                        <div class="pt-sidebar-ring-border"></div>
                        <div class="pt-sidebar-ring-image">
                            <?php if (!empty($sBanner['image'])): ?>
                            <img src="<?= url('storage/uploads/' . e($sBanner['image'])) ?>" alt="<?= e($sBanner['title'] ?? '') ?>" loading="lazy">
                            <?php else: ?>
                            <span class="pt-sidebar-ring-placeholder"><?= e(mb_substr($sBanner['title'] ?? '?', 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="pt-sidebar-ring-label"><?= e($sBanner['title'] ?? '') ?></span>
                </a>
                <?php
                    endforeach;
                endif;
                ?>
                <a href="<?= url('/contact') ?>" class="pt-sidebar-ring-item">
                    <div class="pt-sidebar-ring">
                        <div class="pt-sidebar-ring-border"></div>
                        <div class="pt-sidebar-ring-image">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                    </div>
                    <span class="pt-sidebar-ring-label">Questions?</span>
                </a>
                <a href="<?= url('/products?on_sale=1') ?>" class="pt-sidebar-ring-item">
                    <div class="pt-sidebar-ring">
                        <div class="pt-sidebar-ring-border pt-sidebar-ring-border-hot"></div>
                        <div class="pt-sidebar-ring-image pt-sidebar-ring-image-hot">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </div>
                    </div>
                    <span class="pt-sidebar-ring-label">Hot Deals</span>
                </a>
                <a href="<?= url('/contact') ?>" class="pt-sidebar-ring-item">
                    <div class="pt-sidebar-ring">
                        <div class="pt-sidebar-ring-border"></div>
                        <div class="pt-sidebar-ring-image">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        </div>
                    </div>
                    <span class="pt-sidebar-ring-label">Newsletter</span>
                </a>
        </aside>
    </section>

    <!-- ===== PRODUCT DETAILS ===== -->
    <section class="pt-details">
        <div class="container">
            <!-- Pill Tabs -->
            <div class="pt-pill-tabs" role="tablist">
                <button type="button" class="pt-pill is-active pt-tab-btn" role="tab" aria-selected="true" data-tab="description">Description</button>
                <button type="button" class="pt-pill pt-tab-btn" role="tab" aria-selected="false" data-tab="specs">Specifications</button>
                <button type="button" class="pt-pill pt-tab-btn" role="tab" aria-selected="false" data-tab="reviews">Reviews (<?= $product->rating_count ?? 0 ?>)</button>
            </div>

            <div class="pt-detail-content">
                <!-- Description -->
                <div class="pt-tab-panel is-active" id="pt-tab-description" role="tabpanel">
                    <?php if ($product->description): ?>
                    <div class="pt-prose">
                        <?= nl2br(e($product->description)) ?>
                    </div>
                    <?php else: ?>
                    <p class="pt-empty">No description available.</p>
                    <?php endif; ?>
                </div>

                <!-- Specifications -->
                <div class="pt-tab-panel" id="pt-tab-specs" role="tabpanel">
                    <?php $hasSpecs = !empty($specifications) || !empty($attributes) || $product->weight || ($product->length && $product->width && $product->height); ?>
                    <?php if ($hasSpecs): ?>
                    <div class="pt-specs">
                        <?php if (!empty($specifications)): ?>
                        <?php foreach ($specifications as $spec): ?>
                        <div class="pt-spec-row">
                            <span class="pt-spec-key"><?= e($spec['spec_name']) ?></span>
                            <span class="pt-spec-val"><?= e($spec['spec_value']) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($groupedAttributes)): ?>
                        <?php foreach ($groupedAttributes as $attrSlug => $attr): ?>
                        <div class="pt-spec-row">
                            <span class="pt-spec-key"><?= e($attr['name']) ?></span>
                            <span class="pt-spec-val"><?= e(implode(', ', array_column($attr['values'], 'value'))) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($product->weight): ?>
                        <div class="pt-spec-row">
                            <span class="pt-spec-key">Weight</span>
                            <span class="pt-spec-val"><?= $product->weight ?> kg</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($product->length && $product->width && $product->height): ?>
                        <div class="pt-spec-row">
                            <span class="pt-spec-key">Dimensions</span>
                            <span class="pt-spec-val"><?= $product->length ?> x <?= $product->width ?> x <?= $product->height ?> cm</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <p class="pt-empty">No specifications available.</p>
                    <?php endif; ?>
                </div>

                <!-- Reviews -->
                <div class="pt-tab-panel" id="pt-tab-reviews" role="tabpanel">
                    <div class="pt-reviews" id="reviews">
                        <div class="pt-reviews-top">
                            <div class="pt-score-block">
                                <span class="pt-score-num"><?= number_format($product->rating_average ?? 0, 1) ?></span>
                                <div class="pt-score-info">
                                    <div class="pt-score-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $i <= round($product->rating_average ?? 0) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="pt-score-count"><?= $product->rating_count ?? 0 ?> reviews</span>
                                </div>
                            </div>
                            <?php if (auth()): ?>
                            <button type="button" class="pt-btn-outline" id="pt-write-review-btn">Write a Review</button>
                            <?php else: ?>
                            <a href="<?= url('/login?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>" class="pt-btn-outline">Login to Review</a>
                            <?php endif; ?>
                        </div>

                        <?php if (auth()): ?>
                        <div class="pt-review-form-container pt-hidden" id="pt-review-form-container">
                            <form id="pt-review-form" class="pt-review-form">
                                <input type="hidden" name="product_id" value="<?= $product->id ?>">
                                <?= csrfField() ?>
                                <div class="pt-form-group">
                                    <label class="pt-form-label">Your Rating *</label>
                                    <div class="pt-rating-input" id="pt-rating-input">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <button type="button" class="pt-rating-star" data-rating="<?= $i ?>" aria-label="<?= $i ?> star">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        </button>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating" id="pt-rating-value" value="" required>
                                </div>
                                <div class="pt-form-group">
                                    <label class="pt-form-label" for="pt-review-title">Title</label>
                                    <input type="text" id="pt-review-title" name="title" placeholder="Sum it up" maxlength="100" class="pt-form-input">
                                </div>
                                <div class="pt-form-group">
                                    <label class="pt-form-label" for="pt-review-content">Your Review *</label>
                                    <textarea id="pt-review-content" name="content" rows="4" placeholder="Tell us about your experience" required minlength="20" maxlength="2000" class="pt-form-textarea"></textarea>
                                </div>
                                <div class="pt-form-actions">
                                    <button type="button" class="pt-btn-ghost" id="pt-cancel-review">Cancel</button>
                                    <button type="submit" class="pt-btn-primary">Submit Review</button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($reviews)): ?>
                        <div class="pt-review-list">
                            <?php foreach ($reviews as $review): ?>
                            <div class="pt-review-item">
                                <div class="pt-review-head">
                                    <div class="pt-review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg viewBox="0 0 24 24" fill="<?= $i <= $review['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if (!empty($review['is_verified'])): ?>
                                    <span class="pt-verified"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Verified</span>
                                    <?php endif; ?>
                                    <span class="pt-review-date"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                                </div>
                                <?php if (!empty($review['title'])): ?>
                                <h4 class="pt-review-title"><?= e($review['title']) ?></h4>
                                <?php endif; ?>
                                <p class="pt-review-body"><?= e($review['content']) ?></p>
                                <span class="pt-review-author"><?= e($review['first_name'] ?? 'Anonymous') ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="pt-empty-reviews">
                            <p>No reviews yet. Be the first to share your thoughts!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== RELATED PRODUCTS ===== -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="pt-related">
        <div class="container">
            <h2 class="pt-section-title">You May Also Like</h2>
            <div class="pt-carousel" data-carousel="related">
                <div class="pt-carousel-track">
                    <?php foreach ($relatedProducts as $relProduct): ?>
                    <?php $product = $relProduct; ?>
                    <div class="pt-carousel-slide">
                        <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="pt-carousel-nav pt-carousel-prev" data-carousel-prev aria-label="Previous">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="pt-carousel-nav pt-carousel-next" data-carousel-next aria-label="Next">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

</div>

<!-- Sticky Purchase Bar (appears on scroll) -->
<div class="pt-sticky-bar" id="pt-sticky-bar">
    <div class="container">
        <div class="pt-sticky-inner">
            <div class="pt-sticky-info">
                <?php if ($mainImage): ?>
                <img src="<?= url('storage/uploads/' . e($mainImage['path'])) ?>" alt="" class="pt-sticky-img">
                <?php endif; ?>
                <div class="pt-sticky-text">
                    <span class="pt-sticky-name"><?= e($product->name ?? '') ?></span>
                    <span class="pt-sticky-price"><?= formatPrice($product->price ?? 0) ?></span>
                </div>
            </div>
            <?php if ($product->isInStock()): ?>
            <button type="button" class="pt-sticky-btn" onclick="document.getElementById('pt-add-cart-btn')?.click()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                Add to Cart
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div class="pt-lightbox" id="pt-lightbox" aria-hidden="true">
    <div class="pt-lightbox-overlay"></div>
    <div class="pt-lightbox-content">
        <img class="pt-lightbox-image" id="pt-lightbox-image" src="" alt="">
    </div>
    <button type="button" class="pt-lightbox-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <button type="button" class="pt-lightbox-nav pt-lightbox-prev" aria-label="Previous">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button type="button" class="pt-lightbox-nav pt-lightbox-next" aria-label="Next">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
    <div class="pt-lightbox-counter" id="pt-lightbox-counter"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ptInitGallery();
    ptInitVariants();
    ptInitQuantity();
    ptInitTabs();
    ptInitReviewForm();
    ptInitLightbox();
    ptTrackRecentlyViewed();
    ptInitNotifyForm();
    ptInitAddToCart();
    ptInitCarousel();
    ptInitStickyBar();
});

function ptCopyLink() {
    const url = '<?= url('/products/' . $product->slug) ?>';
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => ptShowToast('Link copied!')).catch(() => ptFallbackCopy(url));
    } else { ptFallbackCopy(url); }
}

function ptFallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.cssText = 'position:fixed;opacity:0;';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); ptShowToast('Link copied!'); } catch(e) { ptShowToast('Failed to copy', 'error'); }
    document.body.removeChild(ta);
}

function ptShowToast(message, type = 'success') {
    if (window.Pricetag?.Toast) { type === 'error' ? window.Pricetag.Toast.error(message) : window.Pricetag.Toast.success(message); return; }
    let c = document.getElementById('pt-toast-container');
    if (!c) { c = document.createElement('div'); c.id = 'pt-toast-container'; c.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;'; document.body.appendChild(c); }
    const t = document.createElement('div');
    t.style.cssText = 'background:' + (type === 'error' ? '#ef4444' : '#22c55e') + ';color:#fff;padding:12px 20px;border-radius:8px;margin-top:8px;box-shadow:0 8px 32px rgba(0,0,0,0.4);font-weight:600;font-size:0.875rem;';
    t.textContent = message; c.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(100%)'; t.style.transition = 'all 0.3s ease'; setTimeout(() => t.remove(), 300); }, 3000);
}

function ptInitGallery() {
    const mainImage = document.getElementById('pt-main-image');
    const thumbs = document.querySelectorAll('.pt-thumb-ring-item');
    if (!mainImage || !thumbs.length) return;
    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            mainImage.src = thumb.dataset.image;
            thumbs.forEach(t => t.classList.remove('is-active'));
            thumb.classList.add('is-active');
        });
    });
}

function ptInitVariants() {
    document.querySelectorAll('.pt-variant-options').forEach(group => {
        const buttons = group.querySelectorAll('button');
        const variantName = group.dataset.variant;
        const display = document.querySelector(`[data-variant-display="${variantName}"]`);
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                if (display) display.textContent = btn.title || btn.textContent.trim();
            });
        });
    });
}

function ptInitQuantity() {
    const input = document.getElementById('pt-quantity');
    const dec = document.getElementById('pt-qty-decrease');
    const inc = document.getElementById('pt-qty-increase');
    if (!input) return;
    const min = parseInt(input.min) || 1, max = parseInt(input.max) || 99;
    dec?.addEventListener('click', () => { input.value = Math.max(min, parseInt(input.value) - 1); });
    inc?.addEventListener('click', () => { input.value = Math.min(max, parseInt(input.value) + 1); });
}

function ptInitTabs() {
    const tabs = document.querySelectorAll('.pt-tab-btn');
    const panels = document.querySelectorAll('.pt-tab-panel');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            tabs.forEach(t => { t.classList.remove('is-active'); t.setAttribute('aria-selected', 'false'); });
            panels.forEach(p => p.classList.remove('is-active'));
            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');
            document.getElementById(`pt-tab-${target}`)?.classList.add('is-active');
        });
    });
}

function ptInitReviewForm() {
    const writeBtn = document.getElementById('pt-write-review-btn');
    const cancelBtn = document.getElementById('pt-cancel-review');
    const formContainer = document.getElementById('pt-review-form-container');
    const form = document.getElementById('pt-review-form');
    const ratingStars = document.querySelectorAll('.pt-rating-star');
    const ratingInput = document.getElementById('pt-rating-value');

    if (writeBtn && formContainer) writeBtn.addEventListener('click', () => { formContainer.classList.remove('pt-hidden'); writeBtn.classList.add('pt-hidden'); });
    if (cancelBtn && formContainer && writeBtn) cancelBtn.addEventListener('click', () => { formContainer.classList.add('pt-hidden'); writeBtn.classList.remove('pt-hidden'); });

    ratingStars.forEach(star => {
        star.addEventListener('click', () => {
            const r = parseInt(star.dataset.rating);
            if (ratingInput) ratingInput.value = r;
            ratingStars.forEach((s, i) => { s.classList.toggle('is-active', i < r); s.querySelector('svg').setAttribute('fill', i < r ? 'currentColor' : 'none'); });
        });
        star.addEventListener('mouseenter', () => {
            const r = parseInt(star.dataset.rating);
            ratingStars.forEach((s, i) => { s.querySelector('svg').setAttribute('fill', i < r ? 'currentColor' : 'none'); });
        });
    });
    document.getElementById('pt-rating-input')?.addEventListener('mouseleave', () => {
        const cr = parseInt(ratingInput?.value) || 0;
        ratingStars.forEach((s, i) => { s.classList.toggle('is-active', i < cr); s.querySelector('svg').setAttribute('fill', i < cr ? 'currentColor' : 'none'); });
    });

    if (form) form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!ratingInput?.value) { ptShowToast('Please select a rating', 'error'); return; }
        try {
            const res = await fetch(window.Pricetag.baseUrl + '/api/reviews', { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (data.success) { ptShowToast('Thank you for your review!'); setTimeout(() => location.reload(), 1500); }
            else ptShowToast(data.message || 'Failed to submit review', 'error');
        } catch(e) { ptShowToast('Something went wrong', 'error'); }
    });
}

function ptInitLightbox() {
    const lb = document.getElementById('pt-lightbox'), img = document.getElementById('pt-lightbox-image'), counter = document.getElementById('pt-lightbox-counter');
    const mainImg = document.getElementById('pt-main-image'), thumbs = document.querySelectorAll('.pt-thumb-ring-item');
    const galleryContainer = document.querySelector('.pt-gallery-image-container');
    if (!lb || !mainImg) return;
    let idx = 0;
    const imgs = Array.from(thumbs).map(t => t.dataset.image);
    if (!imgs.length) imgs.push(mainImg.src);

    function open(i = 0) { idx = i; img.src = imgs[idx]; counter.textContent = `${idx+1} / ${imgs.length}`; lb.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
    function close() { lb.classList.remove('is-open'); document.body.style.overflow = ''; }
    function nav(d) { idx = (idx + d + imgs.length) % imgs.length; img.src = imgs[idx]; counter.textContent = `${idx+1} / ${imgs.length}`; }

    document.getElementById('pt-zoom-btn')?.addEventListener('click', () => open(idx));
    galleryContainer?.addEventListener('click', () => open(idx));
    thumbs.forEach((t, i) => { t.addEventListener('click', () => { idx = i; }); });
    lb.querySelector('.pt-lightbox-close')?.addEventListener('click', close);
    lb.querySelector('.pt-lightbox-overlay')?.addEventListener('click', close);
    lb.querySelector('.pt-lightbox-prev')?.addEventListener('click', () => nav(-1));
    lb.querySelector('.pt-lightbox-next')?.addEventListener('click', () => nav(1));
    document.addEventListener('keydown', (e) => { if (!lb.classList.contains('is-open')) return; if (e.key === 'Escape') close(); if (e.key === 'ArrowLeft') nav(-1); if (e.key === 'ArrowRight') nav(1); });
}

function ptTrackRecentlyViewed() {
    try {
        const ps = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        const cp = { id: <?= $product->id ?? 'null' ?>, name: "<?= e(addslashes($product->name ?? '')) ?>", url: "<?= url('/products/' . ($product->slug ?? '')) ?>", image: "<?= $images[0]['path'] ?? '' ? url('storage/uploads/' . e($images[0]['path'] ?? '')) : '' ?>", price: "<?= formatPrice($product->price ?? 0) ?>" };
        const f = ps.filter(p => p.id !== cp.id); f.unshift(cp);
        localStorage.setItem('recentlyViewed', JSON.stringify(f.slice(0, 10)));
    } catch(e) {}
}

function ptInitNotifyForm() {
    const form = document.getElementById('pt-notify-form'), success = document.getElementById('pt-notify-success');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form); fd.append('product_id', <?= $product->id ?? 'null' ?>);
        try { const r = await fetch(window.Pricetag.baseUrl + '/api/notify-stock', { method: 'POST', body: fd }); const d = await r.json(); if (d.success) { form.classList.add('pt-hidden'); success?.classList.remove('pt-hidden'); } } catch(e) {}
    });
}

function ptInitAddToCart() {
    const btn = document.getElementById('pt-add-cart-btn');
    if (!btn) return;
    btn.addEventListener('click', async () => {
        const pid = btn.dataset.productAdd, qty = parseInt(document.getElementById('pt-quantity')?.value) || 1, vid = document.getElementById('pt-variant-id')?.value || null;
        btn.disabled = true; const orig = btn.innerHTML; btn.innerHTML = '<span class="pt-spinner"></span> Adding...';
        try {
            const fd = new FormData(); fd.append('product_id', pid); fd.append('quantity', qty); if (vid) fd.append('variant_id', vid);
            const r = await fetch(window.Pricetag.baseUrl + '/cart/add', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const d = await r.json();
            if (d.success) { ptShowToast('Added to cart!'); window.Pricetag.Cart?.refresh(); } else ptShowToast(d.message || 'Failed to add to cart', 'error');
        } catch(e) { ptShowToast('Something went wrong', 'error'); } finally { btn.disabled = false; btn.innerHTML = orig; }
    });
}

function ptInitCarousel() {
    document.querySelectorAll('.pt-carousel').forEach(c => {
        const track = c.querySelector('.pt-carousel-track');
        if (!track) return;
        c.querySelector('.pt-carousel-prev')?.addEventListener('click', () => { track.scrollBy({ left: -300, behavior: 'smooth' }); });
        c.querySelector('.pt-carousel-next')?.addEventListener('click', () => { track.scrollBy({ left: 300, behavior: 'smooth' }); });
    });
}

function ptInitStickyBar() {
    const bar = document.getElementById('pt-sticky-bar');
    const purchaseArea = document.querySelector('.pt-purchase') || document.querySelector('.pt-notify-card');
    if (!bar || !purchaseArea) return;

    const observer = new IntersectionObserver(([entry]) => {
        bar.classList.toggle('is-visible', !entry.isIntersecting);
    }, { threshold: 0, rootMargin: '-80px 0px 0px 0px' });
    observer.observe(purchaseArea);
}
</script>
