<?php
$allCategories = $categories ?? $featuredCategories ?? [];
if (!empty($allCategories)):
?>
<section class="category-cards-bar">
    <div class="category-cards-container">
        <div class="category-cards-scroll">
            <?php foreach ($allCategories as $category):
                $catSlug = is_array($category) ? ($category['slug'] ?? '') : ($category->slug ?? '');
                $catName = is_array($category) ? ($category['name'] ?? '') : ($category->name ?? '');
                $catImage = is_array($category) ? ($category['image'] ?? '') : ($category->image ?? '');
                $catIcon = is_array($category) ? ($category['icon'] ?? '') : ($category->icon ?? '');
            ?>
            <a href="<?= url('/categories/' . $catSlug) ?>" class="category-card-item">
                <div class="category-card-ring">
                    <div class="category-card-ring-border"></div>
                    <div class="category-card-image">
                        <?php if (!empty($catImage)): ?>
                        <img src="<?= url('storage/uploads/' . e($catImage)) ?>" alt="<?= e($catName) ?>" loading="lazy">
                        <?php elseif (!empty($catIcon)): ?>
                        <img src="<?= url('storage/uploads/' . e($catIcon)) ?>" alt="<?= e($catName) ?>" loading="lazy">
                        <?php else: ?>
                        <div class="category-card-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="category-card-content">
                    <span class="category-card-name"><?= e($catName) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
            <a href="<?= url('/categories') ?>" class="category-card-item category-card-view-all">
                <div class="category-card-ring">
                    <div class="category-card-ring-border"></div>
                    <div class="category-card-image">
                        <div class="category-card-view-all-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="category-card-content">
                    <span class="category-card-name">View All</span>
                </div>
            </a>
        </div>
        <button class="category-scroll-btn category-scroll-prev" aria-label="Previous">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <button class="category-scroll-btn category-scroll-next" aria-label="Next">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
    </div>
</section>
<?php endif; ?>
