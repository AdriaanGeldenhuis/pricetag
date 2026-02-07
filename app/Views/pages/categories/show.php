<!-- Category Detail Page - Full Width with Dual Sidebars -->

<!-- Breadcrumb Rings -->
<nav class="breadcrumb-rings">
    <div class="category-container">
        <div class="breadcrumb-rings-list">
            <?php foreach ($breadcrumbs as $i => $crumb): ?>
                <?php if ($i > 0): ?>
                <span class="bc-ring-sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
                <?php endif; ?>
                <?php if ($i === count($breadcrumbs) - 1): ?>
                <span class="bc-ring-item bc-ring-active">
                    <div class="bc-ring-wrap">
                        <div class="bc-ring-border"></div>
                        <div class="bc-ring-inner"><span><?= e(mb_substr($crumb['name'], 0, 1)) ?></span></div>
                    </div>
                    <span class="bc-ring-label"><?= e($crumb['name']) ?></span>
                </span>
                <?php elseif ($i === 0): ?>
                <a href="<?= $crumb['url'] ?>" class="bc-ring-item">
                    <div class="bc-ring-wrap">
                        <div class="bc-ring-border"></div>
                        <div class="bc-ring-inner">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                    </div>
                    <span class="bc-ring-label"><?= e($crumb['name']) ?></span>
                </a>
                <?php else: ?>
                <a href="<?= $crumb['url'] ?>" class="bc-ring-item">
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

<div class="category-page">
    <div class="category-container">
        <!-- Category Header -->
        <div class="category-header">
            <div class="category-header-content">
                <h1 class="category-title"><?= e($category->name) ?></h1>
                <?php if ($category->description): ?>
                <p class="category-description"><?= e($category->description) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subcategories -->
        <?php if (!empty($subcategories)): ?>
        <div class="subcategories-section">
            <h2 class="subcategories-title">Subcategories</h2>
            <div class="subcategory-rings">
                <?php foreach ($subcategories as $sub): ?>
                <a href="<?= url('/categories/' . $sub->slug) ?>" class="subcategory-ring-item">
                    <div class="subcategory-ring-wrap">
                        <div class="subcategory-ring-border"></div>
                        <div class="subcategory-ring-img">
                            <?php if (!empty($sub->image)): ?>
                            <img src="<?= url('storage/uploads/' . e($sub->image)) ?>" alt="<?= e($sub->name) ?>" loading="lazy">
                            <?php elseif (!empty($sub->icon)): ?>
                            <img src="<?= url('storage/uploads/' . e($sub->icon)) ?>" alt="<?= e($sub->name) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="subcat-placeholder">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="subcategory-ring-name"><?= e($sub->name) ?></span>
                    <?php if (isset($sub->product_count)): ?>
                    <span class="subcategory-ring-count"><?= $sub->product_count ?> <?= $sub->product_count === 1 ? 'product' : 'products' ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="category-layout">
            <!-- Left Sidebar - Filters -->
            <aside class="category-sidebar category-sidebar-left">
                <div class="filters-panel">
                    <div class="filters-panel-header">
                        <h3 class="filters-panel-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filters
                        </h3>
                        <?php if (array_filter($filters, fn($v) => !empty($v) && $v !== 'newest')): ?>
                        <a href="<?= url('/categories/' . $category->slug) ?>" class="filters-clear-btn">Clear All</a>
                        <?php endif; ?>
                    </div>

                    <form action="<?= url('/categories/' . $category->slug) ?>" method="GET" id="filter-form">
                        <!-- Price Range Slider -->
                        <?php if ($availableFilters['price_range']['min_price'] !== null):
                            $priceMin = (int)$availableFilters['price_range']['min_price'];
                            $priceMax = (int)$availableFilters['price_range']['max_price'];
                            $currentMin = (int)($filters['min_price'] ?? $priceMin);
                            $currentMax = (int)($filters['max_price'] ?? $priceMax);
                        ?>
                        <div class="filter-section">
                            <h4 class="filter-section-title">
                                <span>Price</span>
                                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </h4>
                            <div class="filter-section-content">
                                <div class="price-range-slider">
                                    <div class="price-range-track">
                                        <div class="price-range-progress" id="priceProgress"></div>
                                    </div>
                                    <input type="range" class="price-range-input" id="priceMin"
                                           min="<?= $priceMin ?>" max="<?= $priceMax ?>" value="<?= $currentMin ?>">
                                    <input type="range" class="price-range-input" id="priceMax"
                                           min="<?= $priceMin ?>" max="<?= $priceMax ?>" value="<?= $currentMax ?>">
                                </div>
                                <div class="price-range-values">
                                    <span id="priceMinDisplay"><?= formatPrice($currentMin) ?></span>
                                    <span class="price-range-separator">-</span>
                                    <span id="priceMaxDisplay"><?= formatPrice($currentMax) ?></span>
                                </div>
                                <input type="hidden" name="min_price" id="minPriceInput" value="<?= $currentMin ?>">
                                <input type="hidden" name="max_price" id="maxPriceInput" value="<?= $currentMax ?>">
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Availability -->
                        <div class="filter-section">
                            <h4 class="filter-section-title">
                                <span>Availability</span>
                                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </h4>
                            <div class="filter-section-content">
                                <label class="filter-checkbox-item">
                                    <input type="checkbox" name="in_stock" value="1"
                                           class="filter-checkbox-input"
                                           <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
                                    <span class="filter-checkbox-custom"></span>
                                    <span class="filter-checkbox-label">In Stock Only</span>
                                </label>
                                <label class="filter-checkbox-item">
                                    <input type="checkbox" name="on_sale" value="1"
                                           class="filter-checkbox-input"
                                           <?= !empty($filters['on_sale']) ? 'checked' : '' ?>>
                                    <span class="filter-checkbox-custom"></span>
                                    <span class="filter-checkbox-label">On Sale</span>
                                </label>
                            </div>
                        </div>

                        <!-- Attributes -->
                        <?php foreach ($availableFilters['attributes'] ?? [] as $attr): ?>
                        <?php if (!empty($attr['values'])): ?>
                        <div class="filter-section">
                            <h4 class="filter-section-title">
                                <span><?= e($attr['name']) ?></span>
                                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </h4>
                            <div class="filter-section-content">
                                <?php foreach ($attr['values'] as $val): ?>
                                <label class="filter-checkbox-item">
                                    <input type="checkbox" name="attr[<?= $attr['id'] ?>][]"
                                           value="<?= $val['id'] ?>"
                                           class="filter-checkbox-input"
                                           <?= in_array($val['id'], $filters['attributes'][$attr['id']] ?? []) ? 'checked' : '' ?>>
                                    <span class="filter-checkbox-custom">
                                        <?php if ($attr['type'] === 'color' && $val['color_code']): ?>
                                        <span class="filter-color-swatch" style="background-color: <?= e($val['color_code']) ?>"></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="filter-checkbox-label"><?= e($val['value']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>

                    </form>
                </div>
            </aside>

            <!-- Main Content - Product Grid -->
            <main class="category-main">
                <!-- Results Header -->
                <div class="category-results-header">
                    <p class="category-results-count">
                        <span class="count-number"><?= $pagination['total'] ?></span> products found
                    </p>

                    <div class="category-results-controls">
                        <!-- Mobile Filter Toggle -->
                        <button type="button" class="mobile-filter-toggle" id="mobileFilterToggle">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filters
                        </button>

                        <div class="category-sort">
                            <label class="sort-label">Sort by:</label>
                            <select name="sort" class="sort-select">
                                <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest</option>
                                <option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                                <option value="rating" <?= ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                            </select>
                        </div>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                <!-- No Results -->
                <div class="category-no-results">
                    <div class="no-results-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                    </div>
                    <h2 class="no-results-title">No products found</h2>
                    <p class="no-results-text">Try adjusting your filters or browse other categories</p>
                    <a href="<?= url('/categories/' . $category->slug) ?>" class="btn btn-primary">Clear Filters</a>
                </div>
                <?php else: ?>
                <!-- Products Grid -->
                <div class="category-products-grid">
                    <?php foreach ($products as $product): ?>
                    <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?= \App\Core\View::pagination($pagination, url('/categories/' . $category->slug)) ?>
                <?php endif; ?>
            </main>

            <!-- Right Sidebar - Ring Widgets -->
            <aside class="category-sidebar category-sidebar-right page-sidebar-right">
                <div class="sidebar-rings-container">
                    <?php
                    $sidebarLocation = 'category_top';
                    $sidebarBanners = getBanners($sidebarLocation);
                    if (!empty($sidebarBanners)):
                        foreach ($sidebarBanners as $sBanner):
                    ?>
                    <a href="<?= e($sBanner['url'] ?? '#') ?>" class="sidebar-ring-link">
                        <div class="sidebar-ring-wrap">
                            <div class="sidebar-ring-border"></div>
                            <div class="sidebar-ring-img">
                                <?php if (!empty($sBanner['image'])): ?>
                                <img src="<?= url('storage/uploads/' . e($sBanner['image'])) ?>" alt="<?= e($sBanner['title'] ?? '') ?>" loading="lazy">
                                <?php else: ?>
                                <span class="sidebar-ring-placeholder"><?= e(mb_substr($sBanner['title'] ?? '?', 0, 1)) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="sidebar-ring-label"><?= e($sBanner['title'] ?? '') ?></span>
                    </a>
                    <?php
                        endforeach;
                    endif;
                    $genericBanners = getBanners('sidebar');
                    if (!empty($genericBanners)):
                        foreach ($genericBanners as $sBanner):
                    ?>
                    <a href="<?= e($sBanner['url'] ?? '#') ?>" class="sidebar-ring-link">
                        <div class="sidebar-ring-wrap">
                            <div class="sidebar-ring-border"></div>
                            <div class="sidebar-ring-img">
                                <?php if (!empty($sBanner['image'])): ?>
                                <img src="<?= url('storage/uploads/' . e($sBanner['image'])) ?>" alt="<?= e($sBanner['title'] ?? '') ?>" loading="lazy">
                                <?php else: ?>
                                <span class="sidebar-ring-placeholder"><?= e(mb_substr($sBanner['title'] ?? '?', 0, 1)) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="sidebar-ring-label"><?= e($sBanner['title'] ?? '') ?></span>
                    </a>
                    <?php
                        endforeach;
                    endif;
                    ?>

                    <a href="<?= url('/products?on_sale=1') ?>" class="sidebar-ring-link">
                        <div class="sidebar-ring-wrap">
                            <div class="sidebar-ring-border sidebar-ring-border-hot"></div>
                            <div class="sidebar-ring-img sidebar-ring-img-hot">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            </div>
                        </div>
                        <span class="sidebar-ring-label">Hot Deals</span>
                    </a>

                    <a href="<?= url('/products?sort=newest') ?>" class="sidebar-ring-link">
                        <div class="sidebar-ring-wrap">
                            <div class="sidebar-ring-border"></div>
                            <div class="sidebar-ring-img">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            </div>
                        </div>
                        <span class="sidebar-ring-label">New Arrivals</span>
                    </a>

                    <a href="<?= url('/contact') ?>" class="sidebar-ring-link">
                        <div class="sidebar-ring-wrap">
                            <div class="sidebar-ring-border"></div>
                            <div class="sidebar-ring-img">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </div>
                        </div>
                        <span class="sidebar-ring-label">Newsletter</span>
                    </a>

                    <a href="<?= url('/contact') ?>" class="sidebar-ring-link">
                        <div class="sidebar-ring-wrap">
                            <div class="sidebar-ring-border"></div>
                            <div class="sidebar-ring-img">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                        </div>
                        <span class="sidebar-ring-label">Need Help?</span>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</div>

<!-- Mobile Filter Sidebar Overlay -->
<div class="mobile-filter-overlay" id="mobileFilterOverlay"></div>

<style>
/* =========================================================================
   CATEGORY PAGE - Full Width with Dual Sidebars
   ========================================================================= */

.category-page {
    min-height: 100vh;
    padding-bottom: var(--space-16);
}

.category-container {
    width: 100%;
    max-width: 1920px;
    margin: 0 auto;
    padding: 0 var(--space-4);
}

@media (min-width: 1024px) {
    .category-container {
        padding: 0 var(--space-6);
    }
}

@media (min-width: 1440px) {
    .category-container {
        padding: 0 var(--space-8);
    }
}

/* Breadcrumb Rings Container */
.breadcrumb-rings .category-container {
    display: flex;
    align-items: center;
}

/* Category Header */
.category-header {
    padding: var(--space-8) 0;
    margin-bottom: var(--space-4);
    border-bottom: var(--border-1) solid var(--color-border);
}

.category-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-2) 0;
    background: linear-gradient(135deg, var(--color-text) 0%, var(--color-primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.category-description {
    font-size: var(--text-base);
    color: var(--color-text-secondary);
    margin: 0;
    max-width: 800px;
}

/* Subcategories */
.subcategories-section {
    margin-bottom: var(--space-6);
}

.subcategories-title {
    font-size: var(--text-lg);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    margin: 0 0 var(--space-4) 0;
}

/* Subcategory ring styles in components.css */

/* Category Layout - 3 Columns */
.category-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 1024px) {
    .category-layout {
        grid-template-columns: 260px 1fr;
    }
}

@media (min-width: 1440px) {
    .category-layout {
        grid-template-columns: 280px 1fr 180px;
    }
}

/* =========================================================================
   SIDEBAR STYLES (Shared)
   ========================================================================= */

.category-sidebar {
    display: none;
}

@media (min-width: 1024px) {
    .category-sidebar-left {
        display: block;
    }
}

@media (min-width: 1440px) {
    .category-sidebar-right {
        display: block;
    }
}

/* Filters Panel */
.filters-panel {
    position: sticky;
    top: calc(var(--header-height) + var(--space-4) + 48px);
    background: linear-gradient(145deg, var(--color-background-elevated) 0%, var(--color-background-alt) 100%);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.filters-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-5);
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-600) 100%);
    border-bottom: var(--border-1) solid var(--color-border);
}

.filters-panel-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0;
}

.filters-clear-btn {
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
    color: rgba(255, 255, 255, 0.8);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-md);
    transition: var(--transition-colors);
}

.filters-clear-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--color-text);
}

/* Filter Sections */
.filter-section {
    border-bottom: var(--border-1) solid var(--color-border);
}

.filter-section:last-of-type {
    border-bottom: none;
}

.filter-section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-4) var(--space-5);
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    cursor: pointer;
    transition: var(--transition-colors);
    margin: 0;
}

.filter-section-title:hover {
    background-color: var(--color-background-hover);
}

.filter-toggle-icon {
    color: var(--color-text-muted);
    transition: transform var(--duration-200);
}

.filter-section.collapsed .filter-toggle-icon {
    transform: rotate(-90deg);
}

.filter-section.collapsed .filter-section-content {
    display: none;
}

.filter-section-content {
    padding: 0 var(--space-5) var(--space-5);
}

/* Price Range Slider */
.price-range-slider {
    position: relative;
    height: 8px;
    margin: var(--space-4) 0;
}

.price-range-track {
    position: absolute;
    width: 100%;
    height: 8px;
    background-color: var(--color-background);
    border-radius: var(--radius-full);
}

.price-range-progress {
    position: absolute;
    height: 100%;
    background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-accent) 100%);
    border-radius: var(--radius-full);
}

.price-range-input {
    position: absolute;
    width: 100%;
    height: 8px;
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
    pointer-events: none;
    top: 0;
}

.price-range-input::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    background: var(--color-primary);
    border: 3px solid var(--color-background);
    border-radius: 50%;
    cursor: pointer;
    pointer-events: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    transition: transform var(--duration-200);
}

.price-range-input::-webkit-slider-thumb:hover {
    transform: scale(1.2);
}

.price-range-input::-moz-range-thumb {
    width: 20px;
    height: 20px;
    background: var(--color-primary);
    border: 3px solid var(--color-background);
    border-radius: 50%;
    cursor: pointer;
    pointer-events: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.price-range-values {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-primary);
    margin-top: var(--space-2);
}

.price-range-separator {
    color: var(--color-text-muted);
}

/* Filter Checkboxes */
.filter-checkbox-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-2) 0;
    cursor: pointer;
    transition: var(--transition-colors);
}

.filter-checkbox-item:hover {
    color: var(--color-primary);
}

.filter-checkbox-input {
    display: none;
}

.filter-checkbox-custom {
    width: 20px;
    height: 20px;
    border: var(--border-2) solid var(--color-border);
    border-radius: var(--radius-md);
    background-color: var(--color-background);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-all);
    flex-shrink: 0;
}

.filter-checkbox-input:checked + .filter-checkbox-custom {
    background-color: var(--color-primary);
    border-color: var(--color-primary);
}

.filter-checkbox-input:checked + .filter-checkbox-custom::after {
    content: '';
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin-bottom: 2px;
}

.filter-color-swatch {
    width: 14px;
    height: 14px;
    border-radius: var(--radius-full);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.filter-checkbox-label {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
}

.filter-checkbox-item:hover .filter-checkbox-label {
    color: var(--color-text);
}

/* =========================================================================
   RIGHT SIDEBAR - BANNERS
   ========================================================================= */

.category-sidebar-right {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    align-items: center;
    position: sticky;
    top: calc(var(--header-height) + var(--space-4) + 48px);
    max-height: calc(100vh - var(--header-height) - 80px);
    overflow-y: auto;
    align-self: start;
    scrollbar-width: thin;
}

.sidebar-banner {
    position: relative;
    padding: var(--space-5);
    background: linear-gradient(145deg, var(--color-background-elevated) 0%, var(--color-background-alt) 100%);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-2xl);
    overflow: hidden;
    transition: var(--transition-all);
}

.sidebar-banner:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

/* Featured Deal Banner */
.sidebar-banner-featured {
    background: linear-gradient(135deg, var(--color-primary) 0%, #991b1b 50%, #7f1d1d 100%);
    border-color: var(--color-primary);
}

.sidebar-banner-featured .sidebar-banner-glow {
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
    pointer-events: none;
}

.sidebar-banner-badge {
    position: absolute;
    top: var(--space-3);
    right: var(--space-3);
    padding: var(--space-1) var(--space-3);
    font-size: 10px;
    font-weight: var(--font-bold);
    text-transform: uppercase;
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--color-text);
    border-radius: var(--radius-full);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.sidebar-banner-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--color-background);
    border-radius: var(--radius-xl);
    margin-bottom: var(--space-3);
    color: var(--color-primary);
}

.sidebar-banner-content {
    position: relative;
    z-index: 1;
}

.sidebar-banner-title {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-2) 0;
}

.sidebar-banner-text {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    margin: 0 0 var(--space-4) 0;
    line-height: var(--leading-relaxed);
}

.sidebar-banner-featured .sidebar-banner-text {
    color: rgba(255, 255, 255, 0.8);
}

.sidebar-banner-btn {
    display: inline-flex;
    align-items: center;
    padding: var(--space-3) var(--space-5);
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--color-text);
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    border-radius: var(--radius-lg);
    transition: var(--transition-all);
}

.sidebar-banner-btn:hover {
    background-color: rgba(255, 255, 255, 0.3);
    transform: translateX(4px);
}

.sidebar-banner-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-primary);
    transition: var(--transition-all);
}

.sidebar-banner-link:hover {
    color: var(--color-accent);
}

.sidebar-banner-link:hover svg {
    transform: translateX(4px);
}

.sidebar-banner-link svg {
    transition: transform var(--duration-200);
}

/* Newsletter Banner */
.sidebar-newsletter-form {
    display: flex;
    gap: var(--space-2);
}

.sidebar-newsletter-input {
    flex: 1;
    padding: var(--space-3);
    background-color: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    color: var(--color-text);
    font-size: var(--text-sm);
}

.sidebar-newsletter-input::placeholder {
    color: var(--color-text-muted);
}

.sidebar-newsletter-input:focus {
    outline: none;
    border-color: var(--color-primary);
}

.sidebar-newsletter-btn {
    padding: var(--space-3);
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-600) 100%);
    border: none;
    border-radius: var(--radius-lg);
    color: var(--color-text);
    cursor: pointer;
    transition: var(--transition-all);
}

.sidebar-newsletter-btn:hover {
    transform: scale(1.05);
}

/* =========================================================================
   MAIN CONTENT AREA
   ========================================================================= */

.category-main {
    min-width: 0;
}

/* Results Header */
.category-results-header {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    padding: var(--space-4);
    background-color: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-xl);
    margin-bottom: var(--space-6);
}

@media (min-width: 640px) {
    .category-results-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.category-results-count {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    margin: 0;
}

.count-number {
    font-weight: var(--font-bold);
    color: var(--color-primary);
    font-size: var(--text-lg);
}

.category-results-controls {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

/* Mobile Filter Toggle */
.mobile-filter-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    background-color: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--color-text);
    cursor: pointer;
    transition: var(--transition-colors);
}

.mobile-filter-toggle:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
}

@media (min-width: 1024px) {
    .mobile-filter-toggle {
        display: none;
    }
}

/* Sort Dropdown */
.category-sort {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.sort-label {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    white-space: nowrap;
    display: none;
}

@media (min-width: 640px) {
    .sort-label {
        display: block;
    }
}

.sort-select {
    padding: var(--space-2) var(--space-10) var(--space-2) var(--space-4);
    background-color: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    color: var(--color-text);
    font-size: var(--text-sm);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23a1a1aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
    background-position: right var(--space-3) center;
    background-repeat: no-repeat;
    background-size: 16px;
    cursor: pointer;
    transition: var(--transition-colors);
}

.sort-select:focus {
    outline: none;
    border-color: var(--color-primary);
}

/* Products Grid */
.category-products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .category-products-grid {
        gap: var(--space-6);
    }
}

@media (min-width: 768px) {
    .category-products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1280px) {
    .category-products-grid {
        grid-template-columns: repeat(5, 1fr);
    }
}

/* No Results */
.category-no-results {
    text-align: center;
    padding: var(--space-16) var(--space-4);
    background: linear-gradient(145deg, var(--color-background-elevated) 0%, var(--color-background-alt) 100%);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-2xl);
}

.no-results-icon {
    margin: 0 auto var(--space-6);
    color: var(--color-text-muted);
    opacity: 0.3;
}

.no-results-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-2) 0;
}

.no-results-text {
    font-size: var(--text-base);
    color: var(--color-text-muted);
    margin: 0 0 var(--space-6) 0;
}

/* =========================================================================
   MOBILE FILTER SIDEBAR
   ========================================================================= */

.mobile-filter-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: var(--z-modal);
    opacity: 0;
    visibility: hidden;
    transition: all var(--duration-300);
}

.mobile-filter-overlay.is-active {
    opacity: 1;
    visibility: visible;
}

@media (min-width: 1024px) {
    .mobile-filter-overlay {
        display: none;
    }
}

/* Mobile Sidebar Panel */
.category-sidebar.mobile-active {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    width: min(320px, 85vw);
    height: 100%;
    z-index: calc(var(--z-modal) + 1);
    overflow-y: auto;
    transform: translateX(-100%);
    transition: transform var(--duration-300) var(--ease-out);
    background-color: var(--color-background);
}

.category-sidebar.mobile-active.is-open {
    transform: translateX(0);
}

.category-sidebar.mobile-active .filters-panel {
    position: static;
    border-radius: 0;
    border: none;
    height: 100%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Price Range Slider
    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    const progress = document.getElementById('priceProgress');
    const minDisplay = document.getElementById('priceMinDisplay');
    const maxDisplay = document.getElementById('priceMaxDisplay');
    const minInput = document.getElementById('minPriceInput');
    const maxInput = document.getElementById('maxPriceInput');

    if (priceMin && priceMax) {
        const min = parseInt(priceMin.min);
        const max = parseInt(priceMin.max);
        const gap = Math.max(1, Math.floor((max - min) * 0.01));

        function formatPrice(value) {
            return 'R' + parseInt(value).toLocaleString('en-ZA');
        }

        function updateSlider() {
            let minVal = parseInt(priceMin.value);
            let maxVal = parseInt(priceMax.value);

            if (maxVal - minVal < gap) {
                if (this === priceMin) {
                    priceMin.value = maxVal - gap;
                    minVal = maxVal - gap;
                } else {
                    priceMax.value = minVal + gap;
                    maxVal = minVal + gap;
                }
            }

            const leftPercent = ((minVal - min) / (max - min)) * 100;
            const rightPercent = ((maxVal - min) / (max - min)) * 100;
            progress.style.left = leftPercent + '%';
            progress.style.width = (rightPercent - leftPercent) + '%';

            minDisplay.textContent = formatPrice(minVal);
            maxDisplay.textContent = formatPrice(maxVal);

            minInput.value = minVal;
            maxInput.value = maxVal;
        }

        priceMin.addEventListener('input', updateSlider);
        priceMax.addEventListener('input', updateSlider);
        updateSlider.call(priceMin);
    }

    // Mobile Filter Toggle
    const mobileFilterToggle = document.getElementById('mobileFilterToggle');
    const mobileFilterOverlay = document.getElementById('mobileFilterOverlay');
    const sidebar = document.querySelector('.category-sidebar-left');
    let mobileSidebar = null;

    if (mobileFilterToggle && sidebar) {
        mobileSidebar = sidebar.cloneNode(true);
        mobileSidebar.classList.add('mobile-active');
        document.body.appendChild(mobileSidebar);

        mobileFilterToggle.addEventListener('click', function() {
            mobileSidebar.classList.add('is-open');
            mobileFilterOverlay.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        });

        mobileFilterOverlay.addEventListener('click', function() {
            mobileSidebar.classList.remove('is-open');
            mobileFilterOverlay.classList.remove('is-active');
            document.body.style.overflow = '';
        });
    }

    // Collapsible filter sections
    document.querySelectorAll('.filter-section-title').forEach(title => {
        title.addEventListener('click', function() {
            this.closest('.filter-section').classList.toggle('collapsed');
        });
    });

    // =========================================================================
    // REAL-TIME AJAX FILTERING
    // =========================================================================
    const filterForm = document.getElementById('filter-form');
    const productGrid = document.querySelector('.category-products-grid');
    const resultsHeader = document.querySelector('.category-results-header');
    const categoryMain = document.querySelector('.category-main');
    if (!filterForm || !productGrid) return;

    let filterTimeout = null;
    let filterController = null;

    function buildFilterUrl() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== '' && value !== null) {
                params.append(key, value);
            }
        }
        // Include sort from the sort dropdown in the main content area
        const sortSelect = categoryMain?.querySelector('.sort-select');
        if (sortSelect && sortSelect.value) {
            params.set('sort', sortSelect.value);
        }
        const qs = params.toString();
        return window.location.pathname + (qs ? '?' + qs : '');
    }

    function applyFilters(delay) {
        clearTimeout(filterTimeout);
        if (filterController) filterController.abort();

        filterTimeout = setTimeout(async function() {
            const url = buildFilterUrl();

            // Show loading state
            productGrid.style.opacity = '0.4';
            productGrid.style.pointerEvents = 'none';
            productGrid.style.transition = 'opacity 0.2s ease';

            filterController = new AbortController();
            try {
                const response = await fetch(url, {
                    signal: filterController.signal,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();

                // Parse the response and extract updated content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Update product grid
                const newGrid = doc.querySelector('.category-products-grid');
                const newNoResults = doc.querySelector('.category-no-results');
                const newPagination = doc.querySelector('.pagination');
                const newResultsHeader = doc.querySelector('.category-results-header');

                // Update results header (count)
                if (newResultsHeader && resultsHeader) {
                    resultsHeader.innerHTML = newResultsHeader.innerHTML;
                    // Re-bind sort dropdown
                    bindSortDropdown();
                }

                // Replace grid content or show no-results
                const existingNoResults = categoryMain.querySelector('.category-no-results');
                const existingPagination = categoryMain.querySelector('.pagination');

                if (newGrid) {
                    productGrid.innerHTML = newGrid.innerHTML;
                    productGrid.style.display = '';
                    if (existingNoResults) existingNoResults.remove();
                } else if (newNoResults) {
                    productGrid.innerHTML = '';
                    productGrid.style.display = 'none';
                    if (existingNoResults) {
                        existingNoResults.innerHTML = newNoResults.innerHTML;
                    } else {
                        productGrid.insertAdjacentHTML('afterend', newNoResults.outerHTML);
                    }
                }

                // Update pagination
                if (existingPagination) existingPagination.remove();
                if (newPagination) {
                    productGrid.insertAdjacentHTML('afterend', newPagination.outerHTML);
                }

                // Update URL without reload
                history.pushState(null, '', url);

            } catch (e) {
                if (e.name === 'AbortError') return;
                console.error('Filter error:', e);
            } finally {
                productGrid.style.opacity = '';
                productGrid.style.pointerEvents = '';
                filterController = null;
            }
        }, delay);
    }

    // Listen for checkbox/radio changes — instant
    filterForm.addEventListener('change', function(e) {
        const tag = e.target.tagName;
        const type = e.target.type;
        if (type === 'checkbox' || type === 'radio' || tag === 'SELECT') {
            // Sync to mobile sidebar
            syncFiltersToMobile();
            applyFilters(50);
        }
    });

    // Listen for price slider — debounced
    if (priceMin) priceMin.addEventListener('input', function() { applyFilters(400); });
    if (priceMax) priceMax.addEventListener('input', function() { applyFilters(400); });

    // Prevent traditional form submission
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        applyFilters(0);
    });

    // Sort dropdown in main content (outside filter form)
    function bindSortDropdown() {
        const sortSelect = categoryMain?.querySelector('.sort-select');
        if (sortSelect) {
            sortSelect.onchange = function() {
                applyFilters(0);
            };
        }
    }
    bindSortDropdown();

    // Sync checkbox states to mobile sidebar clone
    function syncFiltersToMobile() {
        if (!mobileSidebar) return;
        const desktopInputs = filterForm.querySelectorAll('input[type="checkbox"]');
        desktopInputs.forEach(function(input) {
            const mobileInput = mobileSidebar.querySelector('input[name="' + input.name + '"][value="' + input.value + '"]');
            if (mobileInput) mobileInput.checked = input.checked;
        });
    }

    // Also listen for changes on the mobile sidebar clone
    if (mobileSidebar) {
        mobileSidebar.addEventListener('change', function(e) {
            const type = e.target.type;
            if (type === 'checkbox' || type === 'radio') {
                // Sync back to desktop form
                const name = e.target.name;
                const value = e.target.value;
                const desktopInput = filterForm.querySelector('input[name="' + name + '"][value="' + value + '"]');
                if (desktopInput) desktopInput.checked = e.target.checked;
                applyFilters(50);
            }
        });
    }

    // Handle browser back/forward
    window.addEventListener('popstate', function() {
        window.location.reload();
    });
});
</script>
