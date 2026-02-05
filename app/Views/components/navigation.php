<!-- Main Navigation -->
<nav class="main-nav" aria-label="Main navigation">
    <div class="container">
        <ul class="nav-list" role="menubar">
            <?php
            // Get menu items from database or use defaults
            $menuItems = $menuItems ?? [
                [
                    'title' => 'All Categories',
                    'url' => '/categories',
                    'icon' => 'grid',
                    'is_mega' => true,
                    'children' => []
                ],
                ['title' => 'New Arrivals', 'url' => '/products?sort=newest', 'badge' => 'New'],
                ['title' => 'Best Sellers', 'url' => '/products?sort=popular'],
                ['title' => 'Deals', 'url' => '/products?on_sale=1', 'badge' => 'Sale'],
                ['title' => 'Contact', 'url' => '/contact'],
            ];

            // Get categories that should show in menu
            $menuCategories = \App\Models\Category::forMenu();
            ?>

            <?php foreach ($menuItems as $item): ?>
            <li class="nav-item" role="none">
                <a href="<?= url($item['url']) ?>" class="nav-link" role="menuitem"
                   <?php if (!empty($item['is_mega'])): ?>aria-haspopup="true" aria-expanded="false"<?php endif; ?>>
                    <?php if (!empty($item['icon'])): ?>
                    <span class="nav-link-icon">
                        <?php if ($item['icon'] === 'grid'): ?>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                    <span><?= e($item['title']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                    <span class="nav-link-badge"><?= e($item['badge']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item['is_mega']) || !empty($item['children'])): ?>
                    <svg class="nav-link-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <?php endif; ?>
                </a>

                <?php if (!empty($item['is_mega'])): ?>
                <!-- Mega Menu - Contained Width -->
                <div class="mega-menu" role="menu">
                    <div class="mega-menu-inner">
                        <!-- Categories Section -->
                        <div class="mega-menu-categories">
                            <div class="mega-menu-header">
                                <h3 class="mega-menu-section-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                    Shop by Category
                                </h3>
                                <a href="<?= url('/categories') ?>" class="mega-menu-view-all-link">
                                    View All
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </a>
                            </div>
                            <div class="mega-menu-category-grid">
                                <?php
                                // Use menu categories (filtered by show_in_menu)
                                foreach ($menuCategories as $category):
                                    $catIcon = is_array($category) ? ($category['icon'] ?? '') : ($category->icon ?? '');
                                    $catSlug = is_array($category) ? ($category['slug'] ?? '') : ($category->slug ?? '');
                                    $catImage = is_array($category) ? ($category['image'] ?? '') : ($category->image ?? '');
                                    $catName = is_array($category) ? ($category['name'] ?? '') : ($category->name ?? '');
                                    $catCount = is_array($category) ? ($category['product_count'] ?? 0) : ($category->product_count ?? 0);
                                ?>
                                <a href="<?= url('/categories/' . $catSlug) ?>" class="mega-menu-category-item" role="menuitem">
                                    <span class="mega-menu-category-icon">
                                        <?php if (!empty($catImage)): ?>
                                        <img src="<?= url('storage/uploads/' . e($catImage)) ?>" alt="" width="32" height="32">
                                        <?php elseif (!empty($catIcon)): ?>
                                        <img src="<?= url('storage/uploads/' . e($catIcon)) ?>" alt="" width="32" height="32">
                                        <?php else: ?>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="14" width="7" height="7"></rect>
                                            <rect x="3" y="14" width="7" height="7"></rect>
                                        </svg>
                                        <?php endif; ?>
                                    </span>
                                    <span class="mega-menu-category-name"><?= e($catName) ?></span>
                                    <?php if (!empty($catCount)): ?>
                                    <span class="mega-menu-category-count"><?= $catCount ?></span>
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Promo Banners Section -->
                        <div class="mega-menu-promos">
                            <?php if (!empty($megaMenuPromos)): ?>
                            <?php foreach (array_slice($megaMenuPromos, 0, 2) as $promo): ?>
                            <div class="mega-menu-promo-card" style="<?= !empty($promo['bg_color']) ? 'background-color:' . e($promo['bg_color']) : '' ?>">
                                <?php if (!empty($promo['image'])): ?>
                                <img src="<?= e($promo['image']) ?>" alt="" class="mega-menu-promo-img">
                                <?php endif; ?>
                                <div class="mega-menu-promo-content">
                                    <?php if (!empty($promo['badge'])): ?>
                                    <span class="mega-menu-promo-badge"><?= e($promo['badge']) ?></span>
                                    <?php endif; ?>
                                    <div class="mega-menu-promo-title"><?= e($promo['title']) ?></div>
                                    <p class="mega-menu-promo-text"><?= e($promo['subtitle'] ?? '') ?></p>
                                    <a href="<?= url($promo['url'] ?? '/products') ?>" class="btn btn-sm btn-primary">Shop Now</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <!-- Default promos -->
                            <div class="mega-menu-promo-card mega-menu-promo-featured">
                                <div class="mega-menu-promo-glow"></div>
                                <div class="mega-menu-promo-content">
                                    <span class="mega-menu-promo-badge">Featured</span>
                                    <div class="mega-menu-promo-title">New Collection</div>
                                    <p class="mega-menu-promo-text">Discover our latest arrivals</p>
                                    <a href="<?= url('/products?featured=1') ?>" class="btn btn-sm btn-primary">Shop Now</a>
                                </div>
                            </div>
                            <div class="mega-menu-promo-card mega-menu-promo-sale">
                                <div class="mega-menu-promo-content">
                                    <span class="mega-menu-promo-badge badge-danger">Sale</span>
                                    <div class="mega-menu-promo-title">Up to 50% Off</div>
                                    <p class="mega-menu-promo-text">Limited time deals</p>
                                    <a href="<?= url('/products?on_sale=1') ?>" class="btn btn-sm btn-accent">View Deals</a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<style>
/* Mega Menu Styles - Fixed Overflow */
.main-nav {
    position: relative;
}

.main-nav .container {
    position: relative;
}

.mega-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    padding: 0;
    background-color: var(--color-background-secondary);
    border: var(--border-1) solid var(--color-border);
    border-top: none;
    border-radius: 0 0 var(--radius-xl) var(--radius-xl);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all var(--duration-200) var(--ease-out);
    z-index: 1000;
    overflow: hidden;
}

.nav-item:hover .mega-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.mega-menu-inner {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: var(--space-6);
    padding: var(--space-6);
    max-width: 100%;
}

/* Categories Section */
.mega-menu-categories {
    min-width: 0;
    overflow: hidden;
}

.mega-menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-3);
    border-bottom: var(--border-2) solid var(--color-primary);
}

.mega-menu-section-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-base);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0;
}

.mega-menu-view-all-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-primary);
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-lg);
    transition: var(--transition-all);
}

.mega-menu-view-all-link:hover {
    background-color: var(--color-background-hover);
    color: var(--color-accent);
}

.mega-menu-view-all-link:hover svg {
    transform: translateX(4px);
}

.mega-menu-view-all-link svg {
    transition: transform var(--duration-200);
}

/* Category Grid */
.mega-menu-category-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-2);
    max-height: 320px;
    overflow-y: auto;
    padding-right: var(--space-2);
}

@media (min-width: 1200px) {
    .mega-menu-category-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Custom scrollbar */
.mega-menu-category-grid::-webkit-scrollbar {
    width: 5px;
}

.mega-menu-category-grid::-webkit-scrollbar-track {
    background: var(--color-background);
    border-radius: var(--radius-full);
}

.mega-menu-category-grid::-webkit-scrollbar-thumb {
    background: var(--color-primary);
    border-radius: var(--radius-full);
}

.mega-menu-category-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2);
    border-radius: var(--radius-lg);
    transition: var(--transition-all);
    background-color: transparent;
}

.mega-menu-category-item:hover {
    background-color: var(--color-background-hover);
}

.mega-menu-category-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--color-background-elevated);
    border-radius: var(--radius-lg);
    flex-shrink: 0;
    color: var(--color-text-muted);
    transition: var(--transition-all);
    overflow: hidden;
}

.mega-menu-category-item:hover .mega-menu-category-icon {
    background-color: var(--color-primary);
    color: var(--color-text);
}

.mega-menu-category-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mega-menu-category-name {
    flex: 1;
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--color-text);
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mega-menu-category-count {
    font-size: 10px;
    color: var(--color-text-muted);
    background-color: var(--color-background-alt);
    padding: 2px var(--space-2);
    border-radius: var(--radius-full);
    flex-shrink: 0;
}

/* Promo Banners Section */
.mega-menu-promos {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.mega-menu-promo-card {
    position: relative;
    background: linear-gradient(135deg, var(--color-background-elevated) 0%, var(--color-background-hover) 100%);
    border-radius: var(--radius-xl);
    padding: var(--space-5);
    overflow: hidden;
    border: var(--border-1) solid var(--color-border);
    transition: var(--transition-all);
}

.mega-menu-promo-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.mega-menu-promo-featured {
    background: linear-gradient(135deg, var(--color-primary) 0%, #991b1b 50%, #7f1d1d 100%);
    border-color: var(--color-primary);
}

.mega-menu-promo-glow {
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
    pointer-events: none;
}

.mega-menu-promo-sale {
    background: linear-gradient(135deg, var(--color-background-elevated) 0%, var(--color-background-alt) 100%);
}

.mega-menu-promo-img {
    position: absolute;
    top: 0;
    right: 0;
    width: 80px;
    height: 100%;
    object-fit: cover;
    opacity: 0.3;
    mask-image: linear-gradient(to left, rgba(0,0,0,0.5), transparent);
    -webkit-mask-image: linear-gradient(to left, rgba(0,0,0,0.5), transparent);
}

.mega-menu-promo-content {
    position: relative;
    z-index: 1;
}

.mega-menu-promo-badge {
    display: inline-block;
    padding: var(--space-1) var(--space-2);
    font-size: 10px;
    font-weight: var(--font-bold);
    text-transform: uppercase;
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--color-text);
    border-radius: var(--radius-full);
    margin-bottom: var(--space-2);
}

.mega-menu-promo-badge.badge-danger {
    background-color: var(--color-danger);
}

.mega-menu-promo-title {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin-bottom: var(--space-1);
}

.mega-menu-promo-text {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-3);
    line-height: var(--leading-relaxed);
}

.mega-menu-promo-featured .mega-menu-promo-text {
    color: rgba(255, 255, 255, 0.8);
}

/* Responsive */
@media (max-width: 1024px) {
    .mega-menu-inner {
        grid-template-columns: 1fr;
    }

    .mega-menu-promos {
        flex-direction: row;
    }

    .mega-menu-promo-card {
        flex: 1;
    }

    .mega-menu-category-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .mega-menu {
        display: none;
    }
}
</style>
