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
                <!-- Mega Menu - Full Width Premium -->
                <div class="mega-menu" role="menu">
                    <div class="mega-menu-shimmer"></div>
                    <div class="mega-menu-inner">
                        <!-- Header -->
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
                                Browse All Categories
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>

                        <!-- Category Grid -->
                        <div class="mega-menu-category-grid">
                            <?php
                            $catIndex = 0;
                            foreach ($menuCategories as $category):
                                $catIcon = is_array($category) ? ($category['icon'] ?? '') : ($category->icon ?? '');
                                $catSlug = is_array($category) ? ($category['slug'] ?? '') : ($category->slug ?? '');
                                $catImage = is_array($category) ? ($category['image'] ?? '') : ($category->image ?? '');
                                $catName = is_array($category) ? ($category['name'] ?? '') : ($category->name ?? '');
                                $catCount = is_array($category) ? ($category['product_count'] ?? 0) : ($category->product_count ?? 0);
                            ?>
                            <a href="<?= url('/categories/' . $catSlug) ?>" class="mega-cat-card" role="menuitem" style="--cat-i: <?= $catIndex ?>">
                                <span class="mega-cat-ring">
                                    <span class="mega-cat-ring-inner">
                                        <?php if (!empty($catImage)): ?>
                                        <img src="<?= url('storage/uploads/' . e($catImage)) ?>" alt="" loading="lazy">
                                        <?php elseif (!empty($catIcon)): ?>
                                        <img src="<?= url('storage/uploads/' . e($catIcon)) ?>" alt="" loading="lazy">
                                        <?php else: ?>
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                            <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                            <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                                            <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                        </svg>
                                        <?php endif; ?>
                                    </span>
                                </span>
                                <span class="mega-cat-name"><?= e($catName) ?></span>
                                <?php if (!empty($catCount)): ?>
                                <span class="mega-cat-count"><?= $catCount ?> items</span>
                                <?php endif; ?>
                            </a>
                            <?php $catIndex++; endforeach; ?>
                        </div>

                        <!-- Bottom Promo Strip -->
                        <div class="mega-menu-promos">
                            <a href="<?= url('/categories') ?>" class="mega-promo-pill mega-promo-featured">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <span>New Collection</span>
                            </a>
                            <a href="<?= url('/categories') ?>" class="mega-promo-pill mega-promo-sale">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                                <span>Hot Deals</span>
                            </a>
                            <a href="<?= url('/categories') ?>" class="mega-promo-pill mega-promo-new">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>New Arrivals</span>
                            </a>
                            <a href="<?= url('/categories') ?>" class="mega-promo-pill mega-promo-popular">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <span>Best Sellers</span>
                            </a>
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
/* =========================================================================
   MEGA MENU - Premium Full-Width Redesign
   ========================================================================= */
.main-nav {
    position: relative;
}

.main-nav .container {
    position: static;
}

/* Override .nav-item position:relative for the mega menu parent */
.main-nav .nav-item:first-child {
    position: static;
}

/* Full-width dropdown - positioned relative to .main-nav */
.mega-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    padding: 0;
    background: linear-gradient(180deg, #1a1a24 0%, #14141c 60%, #111118 100%);
    border-top: none;
    border-bottom: 2px solid rgba(139, 43, 43, 0.3);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8), 0 0 60px rgba(139, 43, 43, 0.08);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: opacity 0.25s ease, visibility 0.25s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1000;
    overflow: hidden;
}

.nav-item:hover .mega-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Top shimmer line */
.mega-menu-shimmer {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(120, 120, 140, 0.3) 15%,
        rgba(200, 200, 220, 0.6) 30%,
        rgba(255, 255, 255, 0.9) 50%,
        rgba(200, 200, 220, 0.6) 70%,
        rgba(120, 120, 140, 0.3) 85%,
        transparent 100%);
    background-size: 200% 100%;
    animation: megaShimmer 4s linear infinite;
    z-index: 2;
}

@keyframes megaShimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}

/* Inner container */
.mega-menu-inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: var(--space-6) var(--space-8);
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}

/* Header row */
.mega-menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: var(--space-4);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.mega-menu-section-title {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0;
    letter-spacing: -0.01em;
}

.mega-menu-section-title svg {
    color: var(--color-primary);
}

.mega-menu-view-all-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-primary);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-full);
    border: 1px solid rgba(139, 43, 43, 0.3);
    transition: all 0.2s ease;
}

.mega-menu-view-all-link:hover {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary);
}

.mega-menu-view-all-link svg {
    transition: transform 0.2s ease;
}

.mega-menu-view-all-link:hover svg {
    transform: translateX(3px);
}

/* ---- Category Grid ---- */
.mega-menu-category-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-3);
}

@media (min-width: 1100px) {
    .mega-menu-category-grid {
        grid-template-columns: repeat(5, 1fr);
    }
}

@media (min-width: 1300px) {
    .mega-menu-category-grid {
        grid-template-columns: repeat(6, 1fr);
    }
}

/* Category Card */
.mega-cat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-4) var(--space-2);
    border-radius: var(--radius-xl);
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid transparent;
    transition: all 0.3s ease;
    text-align: center;
    opacity: 0;
    animation: megaCatFadeIn 0.4s ease forwards;
    animation-delay: calc(var(--cat-i, 0) * 0.03s);
}

@keyframes megaCatFadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.mega-cat-card:hover {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(139, 43, 43, 0.35);
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3), 0 0 20px rgba(139, 43, 43, 0.08);
}

/* Rotating metallic ring */
.mega-cat-ring {
    position: relative;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    padding: 2px;
    background: conic-gradient(
        from 0deg,
        rgba(80, 80, 100, 0.4),
        rgba(160, 160, 180, 0.7),
        rgba(220, 220, 240, 0.95),
        rgba(255, 255, 255, 1),
        rgba(220, 220, 240, 0.95),
        rgba(160, 160, 180, 0.7),
        rgba(80, 80, 100, 0.4),
        rgba(120, 120, 140, 0.5),
        rgba(80, 80, 100, 0.4)
    );
    animation: megaRingSpin 8s linear infinite;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.mega-cat-card:hover .mega-cat-ring {
    animation-duration: 3s;
    transform: scale(1.08);
}

@keyframes megaRingSpin {
    to { transform: rotate(360deg); }
}

.mega-cat-card:hover .mega-cat-ring {
    animation: megaRingSpinHover 3s linear infinite;
}

@keyframes megaRingSpinHover {
    to { transform: scale(1.08) rotate(360deg); }
}

.mega-cat-ring-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #1a1a24;
    overflow: hidden;
    color: var(--color-text-muted);
}

.mega-cat-ring-inner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mega-cat-card:hover .mega-cat-ring-inner {
    color: var(--color-primary);
}

/* Category Name */
.mega-cat-name {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    line-height: 1.3;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: color 0.2s ease;
}

.mega-cat-card:hover .mega-cat-name {
    color: #fff;
}

/* Product count */
.mega-cat-count {
    font-size: 11px;
    color: var(--color-text-muted);
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.mega-cat-card:hover .mega-cat-count {
    opacity: 1;
    color: var(--color-primary);
}

/* ---- Bottom Promo Strip ---- */
.mega-menu-promos {
    display: flex;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.mega-promo-pill {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-text-secondary);
    transition: all 0.2s ease;
}

.mega-promo-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.mega-promo-featured:hover {
    background: rgba(139, 43, 43, 0.2);
    border-color: rgba(139, 43, 43, 0.5);
    color: #e8a0a0;
}

.mega-promo-sale:hover {
    background: rgba(245, 158, 11, 0.15);
    border-color: rgba(245, 158, 11, 0.4);
    color: #f59e0b;
}

.mega-promo-new:hover {
    background: rgba(59, 130, 246, 0.15);
    border-color: rgba(59, 130, 246, 0.4);
    color: #60a5fa;
}

.mega-promo-popular:hover {
    background: rgba(236, 72, 153, 0.15);
    border-color: rgba(236, 72, 153, 0.4);
    color: #f472b6;
}

/* ---- Responsive ---- */
@media (max-width: 1024px) {
    .mega-menu-inner {
        padding: var(--space-5) var(--space-4);
    }

    .mega-menu-category-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .mega-menu-promos {
        flex-wrap: wrap;
    }
}

@media (max-width: 768px) {
    .mega-menu {
        display: none;
    }
}
</style>
