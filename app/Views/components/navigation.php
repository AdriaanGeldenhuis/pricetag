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
                    <div class="mega-menu-grid">
                        <?php
                        // Get categories for mega menu
                        $categories = $categories ?? [];
                        $columns = array_chunk($categories, 5);
                        foreach ($columns as $column):
                        ?>
                        <div class="mega-menu-column">
                            <?php foreach ($column as $category): ?>
                            <a href="<?= url('/categories/' . $category['slug']) ?>" class="mega-menu-link" role="menuitem">
                                <?= e($category['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>

                        <!-- Promo section -->
                        <div class="mega-menu-promo">
                            <div class="mega-menu-promo-title">Featured Collection</div>
                            <p class="mega-menu-promo-text">Discover our latest arrivals</p>
                            <a href="<?= url('/products?featured=1') ?>" class="btn btn-sm btn-primary mt-4">Shop Now</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
