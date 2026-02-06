<!-- Product Listing Page - Full Width with Sidebar Filters -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs">
    <div class="shop-container">
        <div class="breadcrumb-item"><a href="<?php echo url('/'); ?>" class="breadcrumb-link">Home</a></div>
        <div class="breadcrumb-item"><span class="breadcrumb-current">Products</span></div>
    </div>
</nav>

<div class="shop-page">
    <div class="shop-container">
        <!-- Page Header -->
        <div class="shop-header">
            <h1 class="shop-title">
                <?php if ($query): ?>
                Search: "<?php echo e($query); ?>"
                <?php else: ?>
                All Products
                <?php endif; ?>
            </h1>
            <?php if (!$query): ?>
            <p class="shop-subtitle">Discover our complete collection of premium tech products</p>
            <?php endif; ?>
        </div>

        <div class="shop-layout">
            <!-- Sidebar Filters -->
            <aside class="shop-sidebar">
                <div class="filters-panel">
                    <div class="filters-panel-header">
                        <h3 class="filters-panel-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filters
                        </h3>
                        <?php if (array_filter($filters)): ?>
                        <a href="<?php echo url('/products'); ?>" class="filters-clear-btn">Clear All</a>
                        <?php endif; ?>
                    </div>

                    <form action="<?php echo url('/products'); ?>" method="GET" id="filter-form">
                        <?php if ($query): ?>
                        <input type="hidden" name="q" value="<?php echo e($query); ?>">
                        <?php endif; ?>

                        <!-- Category Filter -->
                        <div class="filter-section">
                            <h4 class="filter-section-title">
                                <span>Category</span>
                                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </h4>
                            <div class="filter-section-content">
                                <select name="category" class="filter-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat->id; ?>" <?php echo ($filters['category_id'] ?? '') == $cat->id ? 'selected' : ''; ?>>
                                        <?php echo e($cat->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Price Range Filter -->
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
                                           min="0" max="100000" value="<?php echo e($filters['min_price'] ?? 0); ?>">
                                    <input type="range" class="price-range-input" id="priceMax"
                                           min="0" max="100000" value="<?php echo e($filters['max_price'] ?? 100000); ?>">
                                </div>
                                <div class="price-range-values">
                                    <span id="priceMinDisplay">R<?php echo number_format($filters['min_price'] ?? 0); ?></span>
                                    <span class="price-range-separator">-</span>
                                    <span id="priceMaxDisplay">R<?php echo number_format($filters['max_price'] ?? 100000); ?></span>
                                </div>
                                <input type="hidden" name="min_price" id="minPriceInput" value="<?php echo e($filters['min_price'] ?? ''); ?>">
                                <input type="hidden" name="max_price" id="maxPriceInput" value="<?php echo e($filters['max_price'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Availability Filter -->
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
                                           <?php echo !empty($filters['in_stock']) ? 'checked' : ''; ?>>
                                    <span class="filter-checkbox-custom"></span>
                                    <span class="filter-checkbox-label">In Stock Only</span>
                                </label>
                                <label class="filter-checkbox-item">
                                    <input type="checkbox" name="on_sale" value="1"
                                           class="filter-checkbox-input"
                                           <?php echo !empty($filters['on_sale']) ? 'checked' : ''; ?>>
                                    <span class="filter-checkbox-custom"></span>
                                    <span class="filter-checkbox-label">On Sale</span>
                                </label>
                            </div>
                        </div>

                        <!-- Dynamic Attribute Filters (shown when a category is selected) -->
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

                        <button type="submit" class="filter-apply-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Apply Filters
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="shop-main">
                <!-- Results Header -->
                <div class="shop-results-header">
                    <p class="shop-results-count">
                        <span class="count-number"><?php echo $pagination['total']; ?></span> products found
                    </p>

                    <div class="shop-results-controls">
                        <!-- Mobile Filter Toggle -->
                        <button type="button" class="mobile-filter-toggle" id="mobileFilterToggle">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filters
                        </button>

                        <div class="shop-sort">
                            <label class="sort-label">Sort by:</label>
                            <select name="sort" class="sort-select"
                                    onchange="window.location.href = window.location.pathname + '?sort=' + this.value + '<?php echo $query ? '&q=' . urlencode($query) : ''; ?>'">
                                <option value="newest" <?php echo ($filters['sort'] ?? '') === 'newest' ? 'selected' : ''; ?>>Newest</option>
                                <option value="popular" <?php echo ($filters['sort'] ?? '') === 'popular' || empty($filters['sort']) ? 'selected' : ''; ?>>Most Popular</option>
                                <option value="price_asc" <?php echo ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_desc" <?php echo ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="rating" <?php echo ($filters['sort'] ?? '') === 'rating' ? 'selected' : ''; ?>>Top Rated</option>
                            </select>
                        </div>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                <!-- No Results -->
                <div class="shop-no-results">
                    <div class="no-results-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                            <path d="M8 8l6 6M14 8l-6 6"></path>
                        </svg>
                    </div>
                    <h2 class="no-results-title">No products found</h2>
                    <p class="no-results-text">Try adjusting your search or filters to find what you're looking for</p>
                    <a href="<?php echo url('/products'); ?>" class="btn btn-primary">View All Products</a>
                </div>
                <?php else: ?>
                <!-- Products Grid -->
                <div class="shop-products-grid">
                    <?php foreach ($products as $product): ?>
                    <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php echo \App\Core\View::pagination($pagination, url('/products')); ?>
                <?php endif; ?>
            </main>

            <!-- Right Sidebar - Ring Widgets -->
            <aside class="shop-sidebar-right page-sidebar-right">
                <div class="sidebar-rings-container">
                    <?php
                    $sidebarLocation = 'sidebar';
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
   SHOP PAGE - Full Width Layout with Sidebar
   ========================================================================= */

.shop-page {
    min-height: 100vh;
    padding-bottom: var(--space-16);
}

.shop-container {
    width: 100%;
    max-width: 1920px;
    margin: 0 auto;
    padding: 0 var(--space-4);
}

@media (min-width: 1024px) {
    .shop-container {
        padding: 0 var(--space-8);
    }
}

@media (min-width: 1440px) {
    .shop-container {
        padding: 0 var(--space-12);
    }
}

/* Breadcrumbs override for full width */
.breadcrumbs .shop-container {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

/* Shop Header */
.shop-header {
    padding: var(--space-8) 0;
    margin-bottom: var(--space-6);
    border-bottom: var(--border-1) solid var(--color-border);
}

.shop-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-2) 0;
    background: linear-gradient(135deg, var(--color-text) 0%, var(--color-primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.shop-subtitle {
    font-size: var(--text-base);
    color: var(--color-text-secondary);
    margin: 0;
}

/* Shop Layout - Left Sidebar + Main + Right Sidebar */
.shop-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-8);
}

@media (min-width: 1024px) {
    .shop-layout {
        grid-template-columns: 280px 1fr;
    }
}

@media (min-width: 1440px) {
    .shop-layout {
        grid-template-columns: 280px 1fr 180px;
    }
}

/* Right sidebar hidden on smaller screens */
.shop-sidebar-right {
    display: none;
}

@media (min-width: 1440px) {
    .shop-sidebar-right {
        display: flex;
        position: sticky;
        top: calc(var(--header-height) + var(--space-4) + 48px);
        max-height: calc(100vh - var(--header-height) - 80px);
        overflow-y: auto;
        align-self: start;
        scrollbar-width: thin;
    }
}

/* =========================================================================
   SIDEBAR FILTERS
   ========================================================================= */

.shop-sidebar {
    display: none;
}

@media (min-width: 1024px) {
    .shop-sidebar {
        display: block;
    }
}

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
    padding: var(--space-5) var(--space-5);
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

/* Filter Select */
.filter-select {
    width: 100%;
    height: var(--input-height);
    padding: 0 var(--space-10) 0 var(--space-4);
    background-color: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    color: var(--color-text);
    font-size: var(--text-sm);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23a1a1aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
    background-position: right var(--space-3) center;
    background-repeat: no-repeat;
    background-size: 20px;
    cursor: pointer;
    transition: var(--transition-all);
}

.filter-select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(139, 43, 43, 0.2);
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

.filter-checkbox-label {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
}

.filter-checkbox-item:hover .filter-checkbox-label {
    color: var(--color-text);
}

/* Apply Button */
.filter-apply-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    width: calc(100% - var(--space-10));
    margin: var(--space-5);
    padding: var(--space-4);
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-600) 100%);
    color: var(--color-text);
    font-size: var(--text-sm);
    font-weight: var(--font-bold);
    border: none;
    border-radius: var(--radius-xl);
    cursor: pointer;
    transition: var(--transition-all);
    box-shadow: 0 4px 15px rgba(139, 43, 43, 0.3);
}

.filter-apply-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 43, 43, 0.4);
}

/* =========================================================================
   MAIN CONTENT AREA
   ========================================================================= */

.shop-main {
    min-width: 0;
}

/* Results Header */
.shop-results-header {
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
    .shop-results-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.shop-results-count {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    margin: 0;
}

.count-number {
    font-weight: var(--font-bold);
    color: var(--color-primary);
    font-size: var(--text-lg);
}

.shop-results-controls {
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
.shop-sort {
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
.shop-products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .shop-products-grid {
        gap: var(--space-6);
    }
}

@media (min-width: 768px) {
    .shop-products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1280px) {
    .shop-products-grid {
        grid-template-columns: repeat(5, 1fr);
    }
}

/* No Results */
.shop-no-results {
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
.shop-sidebar.mobile-active {
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

.shop-sidebar.mobile-active.is-open {
    transform: translateX(0);
}

.shop-sidebar.mobile-active .filters-panel {
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
    const sidebar = document.querySelector('.shop-sidebar');

    if (mobileFilterToggle && sidebar) {
        // Clone sidebar for mobile
        const mobileSidebar = sidebar.cloneNode(true);
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
});
</script>
