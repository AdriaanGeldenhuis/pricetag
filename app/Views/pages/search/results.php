<?php
/**
 * Search Results Page
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Features:
 * - Instant search with debounce
 * - Comprehensive filters (price range slider, categories, attributes, rating)
 * - Sort options
 * - Load More pagination
 * - No results state with suggestions
 */

$priceRange = $filterOptions['price_range'] ?? ['min' => 0, 'max' => 10000];
$filterCategories = $filterOptions['categories'] ?? [];
$filterAttributes = $filterOptions['attributes'] ?? [];
$totalResults = $pagination['total'] ?? 0;
?>

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <div class="breadcrumb-item"><a href="<?= url('/') ?>" class="breadcrumb-link">Home</a></div>
    <div class="breadcrumb-item"><span class="breadcrumb-current">Search: "<?= e($query) ?>"</span></div>
</nav>

<div class="search-page container py-6">
    <!-- Search Header -->
    <div class="search-header">
        <div class="search-header-content">
            <h1 class="search-title">
                Search Results for "<span class="search-query"><?= e($query) ?></span>"
            </h1>
            <p class="search-count">
                <span id="results-count"><?= number_format($totalResults) ?></span> products found
            </p>
        </div>

        <!-- Inline Search Refinement -->
        <div class="search-refine">
            <form action="<?= url('/search') ?>" method="GET" class="search-refine-form" id="search-refine-form">
                <div class="search-refine-input-wrap">
                    <input type="search" name="q" value="<?= e($query) ?>" class="search-refine-input"
                           placeholder="Refine your search..." autocomplete="off" id="refine-search-input">
                    <button type="submit" class="search-refine-btn" aria-label="Search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="search-layout">
        <!-- Filters Sidebar -->
        <aside class="search-sidebar" id="search-sidebar">
            <div class="search-sidebar-header">
                <h2 class="search-sidebar-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Filters
                </h2>
                <button type="button" class="search-sidebar-close" id="close-filters" aria-label="Close filters">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="filter-form" class="filter-form">
                <input type="hidden" name="q" value="<?= e($query) ?>">

                <!-- Active Filters -->
                <div class="active-filters" id="active-filters" style="display: none;">
                    <div class="active-filters-header">
                        <span class="active-filters-title">Active Filters</span>
                        <button type="button" class="active-filters-clear" id="clear-all-filters">Clear All</button>
                    </div>
                    <div class="active-filters-tags" id="active-filters-tags"></div>
                </div>

                <!-- Price Range -->
                <div class="filter-section">
                    <button type="button" class="filter-section-toggle" aria-expanded="true">
                        <span>Price Range</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="filter-section-content">
                        <div class="price-range-slider">
                            <div class="price-range-track" id="price-track">
                                <div class="price-range-fill" id="price-fill"></div>
                            </div>
                            <input type="range" class="price-range-input price-range-min" id="price-min"
                                   min="<?= $priceRange['min'] ?>" max="<?= $priceRange['max'] ?>"
                                   value="<?= $filters['min_price'] ?? $priceRange['min'] ?>">
                            <input type="range" class="price-range-input price-range-max" id="price-max"
                                   min="<?= $priceRange['min'] ?>" max="<?= $priceRange['max'] ?>"
                                   value="<?= $filters['max_price'] ?? $priceRange['max'] ?>">
                        </div>
                        <div class="price-range-values">
                            <div class="price-range-value">
                                <span><?= config('currency.symbol') ?></span>
                                <input type="number" name="min_price" id="min-price-input"
                                       value="<?= $filters['min_price'] ?? $priceRange['min'] ?>"
                                       min="<?= $priceRange['min'] ?>" max="<?= $priceRange['max'] ?>">
                            </div>
                            <span class="price-range-separator">-</span>
                            <div class="price-range-value">
                                <span><?= config('currency.symbol') ?></span>
                                <input type="number" name="max_price" id="max-price-input"
                                       value="<?= $filters['max_price'] ?? $priceRange['max'] ?>"
                                       min="<?= $priceRange['min'] ?>" max="<?= $priceRange['max'] ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <?php if (!empty($filterCategories)): ?>
                <div class="filter-section">
                    <button type="button" class="filter-section-toggle" aria-expanded="true">
                        <span>Categories</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="filter-section-content">
                        <div class="filter-checkbox-list" data-max-visible="6">
                            <?php foreach ($filterCategories as $i => $cat): ?>
                            <label class="filter-checkbox <?= $i >= 6 ? 'filter-checkbox-hidden' : '' ?>">
                                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>"
                                       <?= in_array($cat['id'], $filters['category_ids'] ?? []) ? 'checked' : '' ?>>
                                <span class="filter-checkbox-label">
                                    <?= e($cat['name']) ?>
                                    <span class="filter-checkbox-count">(<?= $cat['product_count'] ?>)</span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                            <?php if (count($filterCategories) > 6): ?>
                            <button type="button" class="filter-show-more">
                                Show <?= count($filterCategories) - 6 ?> more
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Availability -->
                <div class="filter-section">
                    <button type="button" class="filter-section-toggle" aria-expanded="true">
                        <span>Availability</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="filter-section-content">
                        <label class="filter-checkbox">
                            <input type="checkbox" name="in_stock" value="1"
                                   <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
                            <span class="filter-checkbox-label">In Stock Only</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="on_sale" value="1"
                                   <?= !empty($filters['on_sale']) ? 'checked' : '' ?>>
                            <span class="filter-checkbox-label">On Sale</span>
                        </label>
                    </div>
                </div>

                <!-- Rating -->
                <div class="filter-section">
                    <button type="button" class="filter-section-toggle" aria-expanded="true">
                        <span>Rating</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="filter-section-content">
                        <div class="filter-rating-list">
                            <?php for ($stars = 5; $stars >= 1; $stars--): ?>
                            <label class="filter-rating">
                                <input type="radio" name="rating" value="<?= $stars ?>"
                                       <?= ($filters['rating'] ?? 0) == $stars ? 'checked' : '' ?>>
                                <span class="filter-rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24"
                                         fill="<?= $i <= $stars ? 'currentColor' : 'none' ?>"
                                         stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    <?php endfor; ?>
                                </span>
                                <span class="filter-rating-label">& Up</span>
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Attributes -->
                <?php foreach ($filterAttributes as $attribute): ?>
                <div class="filter-section">
                    <button type="button" class="filter-section-toggle" aria-expanded="false">
                        <span><?= e($attribute['name']) ?></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="filter-section-content" style="display: none;">
                        <div class="filter-checkbox-list">
                            <?php foreach ($attribute['values'] as $value): ?>
                            <label class="filter-checkbox">
                                <input type="checkbox" name="attr[<?= $attribute['slug'] ?>][]" value="<?= $value['id'] ?>"
                                       <?= in_array($value['id'], $filters['attributes'][$attribute['slug']] ?? []) ? 'checked' : '' ?>>
                                <span class="filter-checkbox-label">
                                    <?= e($value['value']) ?>
                                    <span class="filter-checkbox-count">(<?= $value['count'] ?>)</span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Apply Filters (Mobile) -->
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                    <button type="button" class="btn btn-outline btn-block" id="reset-filters">Reset All</button>
                </div>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="search-main">
            <!-- Toolbar -->
            <div class="search-toolbar">
                <button type="button" class="search-filter-toggle" id="open-filters">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Filters
                </button>

                <div class="search-toolbar-right">
                    <!-- View Toggle -->
                    <div class="search-view-toggle">
                        <button type="button" class="search-view-btn is-active" data-view="grid" aria-label="Grid view">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </button>
                        <button type="button" class="search-view-btn" data-view="list" aria-label="List view">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <!-- Sort -->
                    <div class="search-sort">
                        <label class="search-sort-label">Sort by:</label>
                        <select class="search-sort-select" id="sort-select" name="sort">
                            <option value="relevance" <?= ($filters['sort'] ?? '') === 'relevance' ? 'selected' : '' ?>>Relevance</option>
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
            <div class="search-no-results">
                <div class="search-no-results-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                        <path d="M8 8l6 6M14 8l-6 6" stroke-width="1.5"></path>
                    </svg>
                </div>
                <h2 class="search-no-results-title">No products found</h2>
                <p class="search-no-results-text">
                    We couldn't find any products matching "<strong><?= e($query) ?></strong>".
                    Try different keywords or browse our categories.
                </p>

                <!-- Search Suggestions -->
                <?php if (!empty($popularSearches)): ?>
                <div class="search-suggestions-section">
                    <h3 class="search-suggestions-title">Popular Searches</h3>
                    <div class="search-suggestions-tags">
                        <?php foreach ($popularSearches as $search): ?>
                        <a href="<?= url('/search?q=' . urlencode($search['term'])) ?>" class="search-suggestion-tag">
                            <?= e($search['term']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Related Categories -->
                <?php if (!empty($relatedCategories)): ?>
                <div class="search-suggestions-section">
                    <h3 class="search-suggestions-title">Browse Categories</h3>
                    <div class="search-category-cards">
                        <?php foreach ($relatedCategories as $cat): ?>
                        <a href="<?= url('/categories/' . $cat['slug']) ?>" class="search-category-card">
                            <?php if (!empty($cat['image'])): ?>
                            <img src="<?= url('storage/uploads/' . $cat['image']) ?>" alt="" class="search-category-img">
                            <?php else: ?>
                            <div class="search-category-img search-category-placeholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                            </div>
                            <?php endif; ?>
                            <span class="search-category-name"><?= e($cat['name']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <a href="<?= url('/products') ?>" class="btn btn-primary">View All Products</a>
            </div>
            <?php else: ?>
            <!-- Product Grid -->
            <div class="search-products" id="products-container" data-view="grid">
                <?php foreach ($products as $product): ?>
                <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <!-- Load More / Pagination -->
            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
            <div class="search-load-more" id="load-more-container">
                <button type="button" class="btn btn-outline btn-lg" id="load-more-btn"
                        data-page="<?= $pagination['current_page'] ?>"
                        data-total-pages="<?= $pagination['last_page'] ?>">
                    <span class="load-more-text">Load More Products</span>
                    <span class="load-more-loader" style="display: none;">
                        <svg class="spinner" width="20" height="20" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="60" stroke-dashoffset="20"/>
                        </svg>
                        Loading...
                    </span>
                </button>
                <p class="load-more-progress">
                    Showing <span id="shown-count"><?= count($products) ?></span> of <span id="total-count"><?= $pagination['total'] ?></span> products
                </p>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Filter Overlay (Mobile) -->
<div class="search-sidebar-overlay" id="sidebar-overlay"></div>

<style>
/* =========================================================================
   SEARCH PAGE STYLES
   ========================================================================= */

.search-page {
    min-height: 60vh;
}

/* Search Header */
.search-header {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-6);
    border-bottom: var(--border-1) solid var(--color-border-light);
}

@media (min-width: 768px) {
    .search-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.search-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-1);
}

.search-query {
    color: var(--color-primary);
}

.search-count {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

.search-refine-form {
    position: relative;
}

.search-refine-input-wrap {
    position: relative;
}

.search-refine-input {
    width: 100%;
    min-width: 280px;
    height: var(--input-height);
    padding: 0 var(--space-12) 0 var(--space-4);
    background-color: var(--color-background-alt);
    border: var(--border-1) solid transparent;
    border-radius: var(--radius-full);
    transition: var(--transition-all);
}

.search-refine-input:focus {
    background-color: var(--color-background);
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
}

.search-refine-btn {
    position: absolute;
    right: var(--space-1);
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-full);
    color: var(--color-text-muted);
    transition: var(--transition-colors);
}

.search-refine-btn:hover {
    color: var(--color-primary);
    background-color: var(--color-primary-50);
}

/* Search Layout */
.search-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 1024px) {
    .search-layout {
        grid-template-columns: 280px 1fr;
    }
}

/* Sidebar */
.search-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: min(320px, 90vw);
    height: 100%;
    z-index: var(--z-modal);
    background-color: var(--color-background);
    transform: translateX(-100%);
    transition: transform var(--duration-300) var(--ease-out);
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.search-sidebar.is-open {
    transform: translateX(0);
}

@media (min-width: 1024px) {
    .search-sidebar {
        position: static;
        width: auto;
        height: auto;
        transform: none;
        z-index: auto;
        background-color: transparent;
        overflow: visible;
    }
}

.search-sidebar-overlay {
    position: fixed;
    inset: 0;
    background-color: rgb(0 0 0 / 0.5);
    z-index: calc(var(--z-modal) - 1);
    opacity: 0;
    visibility: hidden;
    transition: all var(--duration-300);
}

.search-sidebar-overlay.is-visible {
    opacity: 1;
    visibility: visible;
}

.search-sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-4);
    border-bottom: var(--border-1) solid var(--color-border-light);
}

@media (min-width: 1024px) {
    .search-sidebar-header {
        display: none;
    }
}

.search-sidebar-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-lg);
    font-weight: var(--font-semibold);
}

.search-sidebar-close {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-full);
}

.search-sidebar-close:hover {
    background-color: var(--color-background-alt);
}

/* Filter Form */
.filter-form {
    flex: 1;
    padding: var(--space-4);
    overflow-y: auto;
}

@media (min-width: 1024px) {
    .filter-form {
        padding: 0;
        background-color: var(--color-background);
        border-radius: var(--radius-xl);
        border: var(--border-1) solid var(--color-border-light);
        padding: var(--space-4);
    }
}

/* Active Filters */
.active-filters {
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-4);
    border-bottom: var(--border-1) solid var(--color-border-light);
}

.active-filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-3);
}

.active-filters-title {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
}

.active-filters-clear {
    font-size: var(--text-xs);
    color: var(--color-primary);
    text-decoration: underline;
}

.active-filters-tags {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}

.active-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-1) var(--space-2);
    font-size: var(--text-xs);
    background-color: var(--color-primary-50);
    color: var(--color-primary);
    border-radius: var(--radius-full);
}

.active-filter-tag button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: var(--radius-full);
    transition: var(--transition-colors);
}

.active-filter-tag button:hover {
    background-color: var(--color-primary);
    color: var(--color-text-inverse);
}

/* Filter Sections */
.filter-section {
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-4);
    border-bottom: var(--border-1) solid var(--color-border-light);
}

.filter-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.filter-section-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: var(--space-2) 0;
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    text-align: left;
}

.filter-section-toggle svg {
    transition: transform var(--duration-200);
}

.filter-section-toggle[aria-expanded="false"] svg {
    transform: rotate(-90deg);
}

.filter-section-content {
    padding-top: var(--space-3);
}

/* Price Range Slider */
.price-range-slider {
    position: relative;
    height: 6px;
    margin: var(--space-6) 0;
}

.price-range-track {
    position: absolute;
    width: 100%;
    height: 6px;
    background-color: var(--color-neutral-200);
    border-radius: var(--radius-full);
}

.price-range-fill {
    position: absolute;
    height: 100%;
    background-color: var(--color-primary);
    border-radius: var(--radius-full);
}

.price-range-input {
    position: absolute;
    width: 100%;
    height: 6px;
    background: transparent;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
}

.price-range-input::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: var(--color-background);
    border: 2px solid var(--color-primary);
    cursor: pointer;
    pointer-events: auto;
    box-shadow: var(--shadow-md);
    transition: var(--transition-transform);
}

.price-range-input::-webkit-slider-thumb:hover {
    transform: scale(1.1);
}

.price-range-input::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: var(--color-background);
    border: 2px solid var(--color-primary);
    cursor: pointer;
    pointer-events: auto;
    box-shadow: var(--shadow-md);
}

.price-range-values {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.price-range-value {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    flex: 1;
}

.price-range-value span {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

.price-range-value input {
    flex: 1;
    min-width: 0;
    height: 36px;
    padding: 0 var(--space-2);
    font-size: var(--text-sm);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-md);
    text-align: center;
}

.price-range-separator {
    color: var(--color-text-muted);
}

/* Filter Checkboxes */
.filter-checkbox-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-2);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: var(--transition-colors);
}

.filter-checkbox:hover {
    background-color: var(--color-background-alt);
}

.filter-checkbox input {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    accent-color: var(--color-primary);
}

.filter-checkbox-label {
    flex: 1;
    display: flex;
    justify-content: space-between;
    font-size: var(--text-sm);
}

.filter-checkbox-count {
    color: var(--color-text-muted);
}

.filter-checkbox-hidden {
    display: none;
}

.filter-checkbox-list.is-expanded .filter-checkbox-hidden {
    display: flex;
}

.filter-show-more {
    font-size: var(--text-sm);
    color: var(--color-primary);
    padding: var(--space-2);
    text-align: left;
}

.filter-show-more:hover {
    text-decoration: underline;
}

/* Rating Filter */
.filter-rating-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.filter-rating {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-2);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: var(--transition-colors);
}

.filter-rating:hover {
    background-color: var(--color-background-alt);
}

.filter-rating input {
    width: 18px;
    height: 18px;
    accent-color: var(--color-primary);
}

.filter-rating-stars {
    display: flex;
    gap: 2px;
    color: var(--color-accent);
}

.filter-rating-label {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

/* Filter Actions (Mobile) */
.filter-actions {
    padding: var(--space-4);
    border-top: var(--border-1) solid var(--color-border-light);
    background-color: var(--color-background);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

@media (min-width: 1024px) {
    .filter-actions {
        display: none;
    }
}

/* Search Main */
.search-main {
    min-width: 0;
}

/* Toolbar */
.search-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
    padding: var(--space-3) var(--space-4);
    background-color: var(--color-background-alt);
    border-radius: var(--radius-xl);
}

.search-filter-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    background-color: var(--color-background);
    border-radius: var(--radius-lg);
    transition: var(--transition-colors);
}

.search-filter-toggle:hover {
    background-color: var(--color-primary-50);
    color: var(--color-primary);
}

@media (min-width: 1024px) {
    .search-filter-toggle {
        display: none;
    }
}

.search-toolbar-right {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.search-view-toggle {
    display: none;
    gap: var(--space-1);
}

@media (min-width: 640px) {
    .search-view-toggle {
        display: flex;
    }
}

.search-view-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    color: var(--color-text-muted);
    transition: var(--transition-colors);
}

.search-view-btn:hover,
.search-view-btn.is-active {
    background-color: var(--color-background);
    color: var(--color-primary);
}

.search-sort {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.search-sort-label {
    display: none;
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

@media (min-width: 640px) {
    .search-sort-label {
        display: block;
    }
}

.search-sort-select {
    height: 36px;
    padding: 0 var(--space-8) 0 var(--space-3);
    font-size: var(--text-sm);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    background-color: var(--color-background);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    cursor: pointer;
}

/* Products Grid */
.search-products {
    display: grid;
    gap: var(--space-4);
}

.search-products[data-view="grid"] {
    grid-template-columns: repeat(2, 1fr);
}

@media (min-width: 640px) {
    .search-products[data-view="grid"] {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1280px) {
    .search-products[data-view="grid"] {
        grid-template-columns: repeat(4, 1fr);
    }
}

.search-products[data-view="list"] {
    grid-template-columns: 1fr;
}

.search-products[data-view="list"] .product-card {
    display: grid;
    grid-template-columns: 180px 1fr;
}

.search-products[data-view="list"] .product-card-image {
    aspect-ratio: 1;
}

.search-products[data-view="list"] .product-card-body {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Load More */
.search-load-more {
    text-align: center;
    margin-top: var(--space-8);
    padding-top: var(--space-8);
    border-top: var(--border-1) solid var(--color-border-light);
}

#load-more-btn {
    min-width: 200px;
}

.load-more-progress {
    margin-top: var(--space-3);
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

.spinner {
    animation: spin 1s linear infinite;
}

/* No Results */
.search-no-results {
    text-align: center;
    padding: var(--space-12) var(--space-4);
}

.search-no-results-icon {
    color: var(--color-neutral-300);
    margin-bottom: var(--space-6);
}

.search-no-results-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-2);
}

.search-no-results-text {
    color: var(--color-text-muted);
    margin-bottom: var(--space-8);
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.search-suggestions-section {
    margin-bottom: var(--space-8);
}

.search-suggestions-title {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-text-muted);
    margin-bottom: var(--space-4);
}

.search-suggestions-tags {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: var(--space-2);
}

.search-suggestion-tag {
    padding: var(--space-2) var(--space-4);
    font-size: var(--text-sm);
    background-color: var(--color-background-alt);
    border-radius: var(--radius-full);
    transition: var(--transition-colors);
}

.search-suggestion-tag:hover {
    background-color: var(--color-primary-50);
    color: var(--color-primary);
}

.search-category-cards {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: var(--space-3);
    margin-bottom: var(--space-8);
}

.search-category-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-4);
    min-width: 100px;
    background-color: var(--color-background-alt);
    border-radius: var(--radius-xl);
    transition: var(--transition-all);
}

.search-category-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.search-category-img {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: var(--radius-lg);
}

.search-category-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--color-neutral-200);
    color: var(--color-text-muted);
}

.search-category-name {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    text-align: center;
}
</style>

<script>
(function() {
    'use strict';

    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    // =========================================================================
    // FILTER SIDEBAR (MOBILE)
    // =========================================================================

    const FilterSidebar = {
        sidebar: null,
        overlay: null,

        init() {
            this.sidebar = $('#search-sidebar');
            this.overlay = $('#sidebar-overlay');
            if (!this.sidebar) return;

            $('#open-filters')?.addEventListener('click', () => this.open());
            $('#close-filters')?.addEventListener('click', () => this.close());
            this.overlay?.addEventListener('click', () => this.close());

            // Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.close();
            });
        },

        open() {
            this.sidebar.classList.add('is-open');
            this.overlay.classList.add('is-visible');
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.sidebar.classList.remove('is-open');
            this.overlay.classList.remove('is-visible');
            document.body.style.overflow = '';
        }
    };

    // =========================================================================
    // FILTER SECTIONS TOGGLE
    // =========================================================================

    const FilterToggle = {
        init() {
            $$('.filter-section-toggle').forEach(btn => {
                btn.addEventListener('click', () => {
                    const content = btn.nextElementSibling;
                    const isExpanded = btn.getAttribute('aria-expanded') === 'true';

                    btn.setAttribute('aria-expanded', !isExpanded);
                    content.style.display = isExpanded ? 'none' : 'block';
                });
            });

            // Show more buttons
            $$('.filter-show-more').forEach(btn => {
                btn.addEventListener('click', () => {
                    const list = btn.closest('.filter-checkbox-list');
                    list.classList.toggle('is-expanded');
                    btn.textContent = list.classList.contains('is-expanded') ? 'Show less' : btn.textContent;
                });
            });
        }
    };

    // =========================================================================
    // PRICE RANGE SLIDER
    // =========================================================================

    const PriceSlider = {
        minSlider: null,
        maxSlider: null,
        minInput: null,
        maxInput: null,
        fill: null,

        init() {
            this.minSlider = $('#price-min');
            this.maxSlider = $('#price-max');
            this.minInput = $('#min-price-input');
            this.maxInput = $('#max-price-input');
            this.fill = $('#price-fill');

            if (!this.minSlider || !this.maxSlider) return;

            this.minSlider.addEventListener('input', () => this.updateFromSlider('min'));
            this.maxSlider.addEventListener('input', () => this.updateFromSlider('max'));
            this.minInput.addEventListener('change', () => this.updateFromInput('min'));
            this.maxInput.addEventListener('change', () => this.updateFromInput('max'));

            this.updateFill();
        },

        updateFromSlider(type) {
            let minVal = parseInt(this.minSlider.value);
            let maxVal = parseInt(this.maxSlider.value);

            if (type === 'min' && minVal > maxVal) {
                this.minSlider.value = maxVal;
                minVal = maxVal;
            }
            if (type === 'max' && maxVal < minVal) {
                this.maxSlider.value = minVal;
                maxVal = minVal;
            }

            this.minInput.value = minVal;
            this.maxInput.value = maxVal;
            this.updateFill();
            this.debounceFilter();
        },

        updateFromInput(type) {
            let minVal = parseInt(this.minInput.value) || 0;
            let maxVal = parseInt(this.maxInput.value) || 0;

            const min = parseInt(this.minSlider.min);
            const max = parseInt(this.minSlider.max);

            minVal = Math.max(min, Math.min(maxVal, minVal));
            maxVal = Math.max(minVal, Math.min(max, maxVal));

            this.minSlider.value = minVal;
            this.maxSlider.value = maxVal;
            this.minInput.value = minVal;
            this.maxInput.value = maxVal;
            this.updateFill();
            this.debounceFilter();
        },

        updateFill() {
            const min = parseInt(this.minSlider.min);
            const max = parseInt(this.minSlider.max);
            const minVal = parseInt(this.minSlider.value);
            const maxVal = parseInt(this.maxSlider.value);

            const left = ((minVal - min) / (max - min)) * 100;
            const right = ((maxVal - min) / (max - min)) * 100;

            this.fill.style.left = left + '%';
            this.fill.style.width = (right - left) + '%';
        },

        debounceFilter: debounce(() => {
            SearchFilters.applyFilters();
        }, 500)
    };

    // =========================================================================
    // VIEW TOGGLE
    // =========================================================================

    const ViewToggle = {
        init() {
            $$('.search-view-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const view = btn.dataset.view;
                    $$('.search-view-btn').forEach(b => b.classList.remove('is-active'));
                    btn.classList.add('is-active');
                    $('#products-container')?.setAttribute('data-view', view);

                    // Save preference
                    localStorage.setItem('product_view', view);
                });
            });

            // Restore preference
            const savedView = localStorage.getItem('product_view');
            if (savedView) {
                $(`.search-view-btn[data-view="${savedView}"]`)?.click();
            }
        }
    };

    // =========================================================================
    // SEARCH FILTERS
    // =========================================================================

    const SearchFilters = {
        form: null,
        debounceTimer: null,

        init() {
            this.form = $('#filter-form');
            if (!this.form) return;

            // Listen for filter changes
            this.form.addEventListener('change', (e) => {
                if (e.target.matches('input[type="checkbox"], input[type="radio"], select')) {
                    this.applyFilters();
                }
            });

            // Sort select
            $('#sort-select')?.addEventListener('change', () => {
                this.applyFilters();
            });

            // Reset filters
            $('#reset-filters')?.addEventListener('click', () => {
                this.resetFilters();
            });

            // Clear all filters
            $('#clear-all-filters')?.addEventListener('click', () => {
                this.resetFilters();
            });

            // Update active filters display
            this.updateActiveFilters();
        },

        getFilters() {
            const formData = new FormData(this.form);
            const filters = {};

            filters.q = formData.get('q');
            filters.sort = $('#sort-select')?.value || 'relevance';
            filters.min_price = formData.get('min_price');
            filters.max_price = formData.get('max_price');
            filters.in_stock = formData.get('in_stock') ? '1' : '';
            filters.on_sale = formData.get('on_sale') ? '1' : '';
            filters.rating = formData.get('rating');

            // Categories
            const categories = formData.getAll('categories[]');
            if (categories.length) {
                filters.categories = categories;
            }

            // Attributes
            for (const [key, value] of formData.entries()) {
                if (key.startsWith('attr[')) {
                    if (!filters.attr) filters.attr = {};
                    const attrName = key.match(/attr\[(.+)\]/)?.[1];
                    if (attrName) {
                        if (!filters.attr[attrName]) filters.attr[attrName] = [];
                        filters.attr[attrName].push(value);
                    }
                }
            }

            return filters;
        },

        applyFilters() {
            const filters = this.getFilters();
            const params = new URLSearchParams();

            Object.entries(filters).forEach(([key, value]) => {
                if (value && value !== '') {
                    if (Array.isArray(value)) {
                        value.forEach(v => params.append(key + '[]', v));
                    } else if (typeof value === 'object') {
                        Object.entries(value).forEach(([k, v]) => {
                            if (Array.isArray(v)) {
                                v.forEach(val => params.append(`attr[${k}][]`, val));
                            }
                        });
                    } else {
                        params.set(key, value);
                    }
                }
            });

            // Navigate to filtered URL
            window.location.href = window.location.pathname + '?' + params.toString();
        },

        resetFilters() {
            const query = $('#filter-form input[name="q"]')?.value;
            window.location.href = window.location.pathname + '?q=' + encodeURIComponent(query);
        },

        updateActiveFilters() {
            const container = $('#active-filters');
            const tagsContainer = $('#active-filters-tags');
            if (!container || !tagsContainer) return;

            const tags = [];
            const filters = this.getFilters();

            // Check for active filters (excluding query)
            if (filters.min_price && filters.min_price !== $('#price-min')?.min) {
                tags.push({ label: `Min: R${filters.min_price}`, type: 'min_price' });
            }
            if (filters.max_price && filters.max_price !== $('#price-max')?.max) {
                tags.push({ label: `Max: R${filters.max_price}`, type: 'max_price' });
            }
            if (filters.in_stock) {
                tags.push({ label: 'In Stock', type: 'in_stock' });
            }
            if (filters.on_sale) {
                tags.push({ label: 'On Sale', type: 'on_sale' });
            }
            if (filters.rating) {
                tags.push({ label: `${filters.rating}+ Stars`, type: 'rating' });
            }

            if (tags.length > 0) {
                container.style.display = 'block';
                tagsContainer.innerHTML = tags.map(tag => `
                    <span class="active-filter-tag" data-filter="${tag.type}">
                        ${tag.label}
                        <button type="button" aria-label="Remove filter">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </span>
                `).join('');

                // Remove individual filters
                tagsContainer.querySelectorAll('button').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const tag = btn.closest('.active-filter-tag');
                        const filterType = tag.dataset.filter;
                        this.removeFilter(filterType);
                    });
                });
            } else {
                container.style.display = 'none';
            }
        },

        removeFilter(type) {
            const input = this.form.querySelector(`[name="${type}"]`);
            if (input) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = input.min || '';
                }
            }
            this.applyFilters();
        }
    };

    // =========================================================================
    // LOAD MORE
    // =========================================================================

    const LoadMore = {
        btn: null,
        container: null,
        page: 1,
        loading: false,

        init() {
            this.btn = $('#load-more-btn');
            this.container = $('#products-container');
            if (!this.btn || !this.container) return;

            this.page = parseInt(this.btn.dataset.page) || 1;

            this.btn.addEventListener('click', () => this.loadMore());
        },

        async loadMore() {
            if (this.loading) return;

            this.loading = true;
            this.page++;

            // Show loader
            this.btn.querySelector('.load-more-text').style.display = 'none';
            this.btn.querySelector('.load-more-loader').style.display = 'inline-flex';

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('page', this.page);

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (data.success && data.html) {
                    this.container.insertAdjacentHTML('beforeend', data.html);

                    // Update count
                    const shownCount = this.container.children.length;
                    $('#shown-count').textContent = shownCount;

                    // Check if more pages
                    if (!data.hasMore) {
                        $('#load-more-container').style.display = 'none';
                    }
                }
            } catch (err) {
                console.error('Load more failed:', err);
                window.Pricetag?.Toast?.error('Failed to load more products');
            } finally {
                this.loading = false;
                this.btn.querySelector('.load-more-text').style.display = 'inline';
                this.btn.querySelector('.load-more-loader').style.display = 'none';
            }
        }
    };

    // =========================================================================
    // DEBOUNCE UTILITY
    // =========================================================================

    function debounce(fn, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn(...args), delay);
        };
    }

    // =========================================================================
    // INIT
    // =========================================================================

    document.addEventListener('DOMContentLoaded', () => {
        FilterSidebar.init();
        FilterToggle.init();
        PriceSlider.init();
        ViewToggle.init();
        SearchFilters.init();
        LoadMore.init();
    });

})();
</script>
