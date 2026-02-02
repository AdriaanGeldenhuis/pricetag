<!-- Product Listing Page -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <div class="breadcrumb-item"><a href="<?= url('/') ?>" class="breadcrumb-link">Home</a></div>
    <div class="breadcrumb-item"><span class="breadcrumb-current">Products</span></div>
</nav>

<div class="container py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:w-64 flex-shrink-0">
            <div class="card">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Filters</h3>

                    <form action="<?= url('/products') ?>" method="GET" id="filter-form">
                        <?php if ($query): ?>
                        <input type="hidden" name="q" value="<?= e($query) ?>">
                        <?php endif; ?>

                        <!-- Categories -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium mb-3">Category</h4>
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>" <?= ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' ?>>
                                    <?= e($cat->name) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium mb-3">Price</h4>
                            <div class="flex gap-2">
                                <input type="number" name="min_price" placeholder="Min"
                                       value="<?= e($filters['min_price'] ?? '') ?>"
                                       class="form-input" style="width: 50%;">
                                <input type="number" name="max_price" placeholder="Max"
                                       value="<?= e($filters['max_price'] ?? '') ?>"
                                       class="form-input" style="width: 50%;">
                            </div>
                        </div>

                        <!-- Availability -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium mb-3">Availability</h4>
                            <label class="form-check">
                                <input type="checkbox" name="in_stock" value="1"
                                       class="form-check-input"
                                       <?= !empty($filters['in_stock']) ? 'checked' : '' ?>
                                       onchange="this.form.submit()">
                                <span class="form-check-label">In Stock Only</span>
                            </label>
                        </div>

                        <!-- On Sale -->
                        <div class="mb-6">
                            <label class="form-check">
                                <input type="checkbox" name="on_sale" value="1"
                                       class="form-check-input"
                                       <?= !empty($filters['on_sale']) ? 'checked' : '' ?>
                                       onchange="this.form.submit()">
                                <span class="form-check-label">On Sale</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>

                        <?php if (array_filter($filters)): ?>
                        <a href="<?= url('/products') ?>" class="btn btn-outline btn-block mt-2">Clear Filters</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Product Grid -->
        <div class="flex-1">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-semibold">
                        <?php if ($query): ?>
                        Search: "<?= e($query) ?>"
                        <?php else: ?>
                        All Products
                        <?php endif; ?>
                    </h1>
                    <p class="text-muted text-sm"><?= $pagination['total'] ?> products found</p>
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-sm text-muted">Sort by:</label>
                    <select name="sort" class="form-select" style="width: auto;"
                            onchange="window.location.href = window.location.pathname + '?sort=' + this.value + '<?= $query ? '&q=' . urlencode($query) : '' ?>'">
                        <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                        <option value="rating" <?= ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                    </select>
                </div>
            </div>

            <?php if (empty($products)): ?>
            <!-- No Results -->
            <div class="text-center py-16">
                <svg class="mx-auto mb-4 text-muted" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="M21 21l-4.35-4.35"></path>
                </svg>
                <h2 class="text-xl font-semibold mb-2">No products found</h2>
                <p class="text-muted mb-6">Try adjusting your search or filters</p>
                <a href="<?= url('/products') ?>" class="btn btn-primary">View All Products</a>
            </div>
            <?php else: ?>
            <!-- Products Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($products as $product): ?>
                <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?= \App\Core\View::pagination($pagination, url('/products')) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
