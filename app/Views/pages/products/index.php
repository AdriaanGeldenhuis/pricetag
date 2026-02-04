<!-- Product Listing Page - Dark Theme -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <div class="breadcrumb-item"><a href="<?php echo url('/'); ?>" class="breadcrumb-link">Home</a></div>
    <div class="breadcrumb-item"><span class="breadcrumb-current">Products</span></div>
</nav>

<div class="container py-8">
    <!-- Filters Card - Full Width -->
    <div class="filters-card mb-8">
        <div class="filters-header">
            <h3 class="filters-title">Filters</h3>
        </div>
        <div class="filters-body">
            <form action="<?php echo url('/products'); ?>" method="GET" id="filter-form" class="filters-form">
                <?php if ($query): ?>
                <input type="hidden" name="q" value="<?php echo e($query); ?>">
                <?php endif; ?>

                <div class="filters-grid">
                    <!-- Category -->
                    <div class="filter-group">
                        <label class="filter-label">Category</label>
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat->id; ?>" <?php echo ($filters['category_id'] ?? '') == $cat->id ? 'selected' : ''; ?>>
                                <?php echo e($cat->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <label class="filter-label">Price</label>
                        <div class="filter-price-range">
                            <input type="number" name="min_price" placeholder="Min"
                                   value="<?php echo e($filters['min_price'] ?? ''); ?>"
                                   class="filter-input">
                            <input type="number" name="max_price" placeholder="Max"
                                   value="<?php echo e($filters['max_price'] ?? ''); ?>"
                                   class="filter-input">
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="filter-group">
                        <label class="filter-label">Availability</label>
                        <div class="filter-checkboxes">
                            <label class="filter-checkbox">
                                <input type="checkbox" name="in_stock" value="1"
                                       <?php echo !empty($filters['in_stock']) ? 'checked' : ''; ?>>
                                <span class="filter-checkbox-mark"></span>
                                <span class="filter-checkbox-text">In Stock Only</span>
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" name="on_sale" value="1"
                                       <?php echo !empty($filters['on_sale']) ? 'checked' : ''; ?>>
                                <span class="filter-checkbox-mark"></span>
                                <span class="filter-checkbox-text">On Sale</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="filter-actions">
                    <button type="submit" class="filter-btn filter-btn-primary">Apply Filters</button>
                    <?php if (array_filter($filters)): ?>
                    <a href="<?php echo url('/products'); ?>" class="filter-btn filter-btn-outline">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Header -->
    <div class="results-header">
        <div class="results-info">
            <h1 class="results-title">
                <?php if ($query): ?>
                Search: "<?php echo e($query); ?>"
                <?php else: ?>
                All Products
                <?php endif; ?>
            </h1>
            <p class="results-count"><?php echo $pagination['total']; ?> products found</p>
        </div>

        <div class="results-sort">
            <label class="sort-label">Sort by:</label>
            <select name="sort" class="sort-select"
                    onchange="window.location.href = window.location.pathname + '?sort=' + this.value + '<?php echo $query ? '&q=' . urlencode($query) : ''; ?>'">
                <option value="popular" <?php echo ($filters['sort'] ?? '') === 'popular' || empty($filters['sort']) ? 'selected' : ''; ?>>Most Popular</option>
                <option value="newest" <?php echo ($filters['sort'] ?? '') === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="price_asc" <?php echo ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="rating" <?php echo ($filters['sort'] ?? '') === 'rating' ? 'selected' : ''; ?>>Top Rated</option>
            </select>
        </div>
    </div>

    <?php if (empty($products)): ?>
    <!-- No Results -->
    <div class="no-results">
        <svg class="no-results-icon" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="M21 21l-4.35-4.35"></path>
        </svg>
        <h2 class="no-results-title">No products found</h2>
        <p class="no-results-text">Try adjusting your search or filters</p>
        <a href="<?php echo url('/products'); ?>" class="btn btn-primary">View All Products</a>
    </div>
    <?php else: ?>
    <!-- Products Grid -->
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
        <?php include APP_PATH . '/Views/components/product-card.php'; ?>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php echo \App\Core\View::pagination($pagination, url('/products')); ?>
    <?php endif; ?>
</div>

<style>
/* Filters Card - Dark Theme */
.filters-card {
    background-color: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.filters-header {
    padding: var(--space-4) var(--space-6);
    border-bottom: var(--border-1) solid var(--color-border);
}

.filters-title {
    font-size: var(--text-lg);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    margin: 0;
}

.filters-body {
    padding: var(--space-6);
}

.filters-form {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.filters-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 768px) {
    .filters-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.filter-label {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--color-text);
}

.filter-select,
.filter-input {
    height: var(--input-height);
    padding: 0 var(--space-4);
    background-color: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    color: var(--color-text);
    font-size: var(--text-base);
    transition: var(--transition-colors);
}

.filter-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23a1a1aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
    background-position: right var(--space-3) center;
    background-repeat: no-repeat;
    background-size: 20px;
    padding-right: var(--space-10);
}

.filter-select:focus,
.filter-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(139, 43, 43, 0.2);
}

.filter-input::placeholder {
    color: var(--color-text-muted);
}

.filter-price-range {
    display: flex;
    gap: var(--space-3);
}

.filter-price-range .filter-input {
    flex: 1;
}

.filter-checkboxes {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    cursor: pointer;
}

.filter-checkbox input {
    display: none;
}

.filter-checkbox-mark {
    width: 20px;
    height: 20px;
    border: var(--border-2) solid var(--color-border);
    border-radius: var(--radius-default);
    background-color: var(--color-background);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-colors);
}

.filter-checkbox input:checked + .filter-checkbox-mark {
    background-color: var(--color-primary);
    border-color: var(--color-primary);
}

.filter-checkbox input:checked + .filter-checkbox-mark::after {
    content: '';
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin-bottom: 2px;
}

.filter-checkbox-text {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
}

.filter-actions {
    display: flex;
    gap: var(--space-3);
    flex-wrap: wrap;
}

.filter-btn {
    padding: var(--space-3) var(--space-6);
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: var(--transition-all);
    border: var(--border-2) solid transparent;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.filter-btn-primary {
    background-color: var(--color-primary);
    color: var(--color-text);
    flex: 1;
}

.filter-btn-primary:hover {
    background-color: var(--color-primary-600);
}

.filter-btn-outline {
    background-color: transparent;
    border-color: var(--color-border);
    color: var(--color-text);
    flex: 1;
}

.filter-btn-outline:hover {
    background-color: var(--color-background-hover);
    border-color: var(--color-neutral-500);
}

/* Results Header */
.results-header {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}

@media (min-width: 640px) {
    .results-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.results-title {
    font-size: var(--text-xl);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    margin: 0;
}

.results-count {
    font-size: var(--text-sm);
    color: var(--color-accent);
    margin: var(--space-1) 0 0 0;
}

.results-sort {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.sort-label {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    white-space: nowrap;
}

.sort-select {
    padding: var(--space-2) var(--space-10) var(--space-2) var(--space-4);
    background-color: var(--color-background-elevated);
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
}

.sort-select:focus {
    outline: none;
    border-color: var(--color-primary);
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .products-grid {
        gap: var(--space-6);
    }
}

@media (min-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1024px) {
    .products-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* No Results */
.no-results {
    text-align: center;
    padding: var(--space-16) var(--space-4);
}

.no-results-icon {
    margin: 0 auto var(--space-6);
    color: var(--color-text-muted);
    opacity: 0.5;
}

.no-results-title {
    font-size: var(--text-xl);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    margin: 0 0 var(--space-2) 0;
}

.no-results-text {
    font-size: var(--text-base);
    color: var(--color-text-muted);
    margin: 0 0 var(--space-6) 0;
}
</style>
