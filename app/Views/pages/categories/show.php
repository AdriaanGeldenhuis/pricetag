<!-- Category Detail Page -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <?php foreach ($breadcrumbs as $i => $crumb): ?>
    <div class="breadcrumb-item">
        <?php if ($i === count($breadcrumbs) - 1): ?>
        <span class="breadcrumb-current"><?= e($crumb['name']) ?></span>
        <?php else: ?>
        <a href="<?= $crumb['url'] ?>" class="breadcrumb-link"><?= e($crumb['name']) ?></a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</nav>

<div class="container py-8">
    <!-- Category Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-semibold mb-2"><?= e($category->name) ?></h1>
        <?php if ($category->description): ?>
        <p class="text-muted"><?= e($category->description) ?></p>
        <?php endif; ?>
    </div>

    <!-- Subcategories -->
    <?php if (!empty($subcategories)): ?>
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-4">Subcategories</h2>
        <div class="flex flex-wrap gap-3">
            <?php foreach ($subcategories as $sub): ?>
            <a href="<?= url('/categories/' . $sub->slug) ?>"
               class="btn btn-outline btn-sm">
                <?= e($sub->name) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:w-64 flex-shrink-0">
            <div class="card">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Filters</h3>

                    <form action="<?= url('/categories/' . $category->slug) ?>" method="GET" id="filter-form">
                        <!-- Price Range Slider -->
                        <?php if ($availableFilters['price_range']['min_price'] !== null):
                            $priceMin = (int)$availableFilters['price_range']['min_price'];
                            $priceMax = (int)$availableFilters['price_range']['max_price'];
                            $currentMin = (int)($filters['min_price'] ?? $priceMin);
                            $currentMax = (int)($filters['max_price'] ?? $priceMax);
                        ?>
                        <div class="mb-6">
                            <h4 class="text-sm font-medium mb-3">Price</h4>
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
                                <span>-</span>
                                <span id="priceMaxDisplay"><?= formatPrice($currentMax) ?></span>
                            </div>
                            <input type="hidden" name="min_price" id="minPriceInput" value="<?= $currentMin ?>">
                            <input type="hidden" name="max_price" id="maxPriceInput" value="<?= $currentMax ?>">
                        </div>
                        <?php endif; ?>

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

                        <!-- Attributes -->
                        <?php foreach ($availableFilters['attributes'] ?? [] as $attr): ?>
                        <?php if (!empty($attr['values'])): ?>
                        <div class="mb-6">
                            <h4 class="text-sm font-medium mb-3"><?= e($attr['name']) ?></h4>
                            <?php foreach ($attr['values'] as $val): ?>
                            <label class="form-check">
                                <input type="checkbox" name="attr[<?= $attr['id'] ?>][]"
                                       value="<?= $val['id'] ?>"
                                       class="form-check-input"
                                       <?= in_array($val['id'], $filters['attributes'][$attr['id']] ?? []) ? 'checked' : '' ?>
                                       onchange="this.form.submit()">
                                <span class="form-check-label">
                                    <?php if ($attr['type'] === 'color' && $val['color_code']): ?>
                                    <span class="inline-block w-4 h-4 rounded-full mr-1" style="background-color: <?= e($val['color_code']) ?>"></span>
                                    <?php endif; ?>
                                    <?= e($val['value']) ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>

                        <?php if (array_filter($filters, fn($v) => !empty($v) && $v !== 'newest')): ?>
                        <a href="<?= url('/categories/' . $category->slug) ?>" class="btn btn-outline btn-block mt-2">Clear Filters</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Product Grid -->
        <div class="flex-1">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <p class="text-muted text-sm"><?= $pagination['total'] ?> products found</p>

                <div class="flex items-center gap-3">
                    <label class="text-sm text-muted">Sort by:</label>
                    <select name="sort" class="form-select" style="width: auto;"
                            onchange="window.location.href = '<?= url('/categories/' . $category->slug) ?>?sort=' + this.value">
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
                <p class="text-muted mb-6">Try adjusting your filters or browse other categories</p>
                <a href="<?= url('/categories/' . $category->slug) ?>" class="btn btn-primary">Clear Filters</a>
            </div>
            <?php else: ?>
            <!-- Products Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($products as $product): ?>
                <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?= \App\Core\View::pagination($pagination, url('/categories/' . $category->slug)) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($availableFilters['price_range']['min_price'] !== null): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    const progress = document.getElementById('priceProgress');
    const minDisplay = document.getElementById('priceMinDisplay');
    const maxDisplay = document.getElementById('priceMaxDisplay');
    const minInput = document.getElementById('minPriceInput');
    const maxInput = document.getElementById('maxPriceInput');

    if (!priceMin || !priceMax) return;

    const min = parseInt(priceMin.min);
    const max = parseInt(priceMin.max);
    const gap = Math.max(1, Math.floor((max - min) * 0.01)); // 1% minimum gap

    function formatPrice(value) {
        return 'R' + parseInt(value).toLocaleString('en-ZA');
    }

    function updateSlider() {
        let minVal = parseInt(priceMin.value);
        let maxVal = parseInt(priceMax.value);

        // Prevent overlap
        if (maxVal - minVal < gap) {
            if (this === priceMin) {
                priceMin.value = maxVal - gap;
                minVal = maxVal - gap;
            } else {
                priceMax.value = minVal + gap;
                maxVal = minVal + gap;
            }
        }

        // Update progress bar
        const leftPercent = ((minVal - min) / (max - min)) * 100;
        const rightPercent = ((maxVal - min) / (max - min)) * 100;
        progress.style.left = leftPercent + '%';
        progress.style.width = (rightPercent - leftPercent) + '%';

        // Update display
        minDisplay.textContent = formatPrice(minVal);
        maxDisplay.textContent = formatPrice(maxVal);

        // Update hidden inputs
        minInput.value = minVal;
        maxInput.value = maxVal;
    }

    priceMin.addEventListener('input', updateSlider);
    priceMax.addEventListener('input', updateSlider);

    // Initialize
    updateSlider.call(priceMin);
});
</script>
<?php endif; ?>
