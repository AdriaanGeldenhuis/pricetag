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
                <!-- Mega Menu -->
                <div class="mega-menu" role="menu">
                    <div class="mega-menu-inner">
                        <!-- Categories with icons -->
                        <div class="mega-menu-categories">
                            <h3 class="mega-menu-section-title">Shop by Category</h3>
                            <div class="mega-menu-category-grid">
                                <?php
                                $categories = $categories ?? [];
                                foreach (array_slice($categories, 0, 12) as $category):
                                    $iconClass = $category['icon'] ?? 'default';
                                ?>
                                <a href="<?= url('/categories/' . $category['slug']) ?>" class="mega-menu-category-item" role="menuitem">
                                    <span class="mega-menu-category-icon" data-icon="<?= e($iconClass) ?>">
                                        <?php if (!empty($category['image'])): ?>
                                        <img src="<?= e($category['image']) ?>" alt="" width="32" height="32">
                                        <?php else: ?>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="14" width="7" height="7"></rect>
                                            <rect x="3" y="14" width="7" height="7"></rect>
                                        </svg>
                                        <?php endif; ?>
                                    </span>
                                    <span class="mega-menu-category-name"><?= e($category['name']) ?></span>
                                    <?php if (!empty($category['product_count'])): ?>
                                    <span class="mega-menu-category-count"><?= $category['product_count'] ?></span>
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <a href="<?= url('/categories') ?>" class="mega-menu-view-all">
                                View All Categories
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>

                        <!-- Promo Banners -->
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
                            <!-- Default promo -->
                            <div class="mega-menu-promo-card">
                                <div class="mega-menu-promo-content">
                                    <span class="mega-menu-promo-badge">Featured</span>
                                    <div class="mega-menu-promo-title">New Collection</div>
                                    <p class="mega-menu-promo-text">Discover our latest arrivals</p>
                                    <a href="<?= url('/products?featured=1') ?>" class="btn btn-sm btn-primary">Shop Now</a>
                                </div>
                            </div>
                            <div class="mega-menu-promo-card" style="background-color: var(--color-accent-100)">
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
