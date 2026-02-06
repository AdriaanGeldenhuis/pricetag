<?php if (!empty($featuredCategories)): ?>
<section class="py-12">
    <div class="container">
        <div class="section-header text-center mb-8">
            <h2 class="section-title"><?= e($sectionTitle ?? 'Shop by Category') ?></h2>
            <p class="section-subtitle"><?= e($sectionSubtitle ?? 'Browse our most popular categories') ?></p>
        </div>
        <div class="category-spotlight-grid">
            <?php foreach ($featuredCategories as $category): ?>
            <a href="<?= url('/categories/' . $category->slug) ?>" class="category-spotlight-item">
                <div class="category-spotlight-icon">
                    <?php if (!empty($category->icon)): ?>
                    <img src="<?= url('storage/uploads/' . e($category->icon)) ?>" alt="" loading="lazy">
                    <?php else: ?>
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <?php endif; ?>
                </div>
                <h3 class="category-spotlight-name"><?= e($category->name) ?></h3>
                <?php if (!empty($category->product_count)): ?>
                <span class="category-spotlight-count"><?= $category->product_count ?> products</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
