<!-- Mobile Menu -->
<div class="mobile-menu" id="mobile-menu" aria-hidden="true">
    <div class="mobile-menu-backdrop" id="mobile-menu-backdrop"></div>
    <div class="mobile-menu-panel" role="dialog" aria-label="Mobile navigation">
        <!-- Header -->
        <div class="mobile-menu-header">
            <span class="header-logo-text"><?= e(config('app.name')) ?></span>
            <button type="button" class="mobile-menu-close" id="mobile-menu-close" aria-label="Close menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Search (Mobile) -->
        <div class="mobile-menu-search" style="padding: var(--space-4);">
            <form action="<?= url('/search') ?>" method="GET">
                <input
                    type="search"
                    name="q"
                    class="form-input"
                    placeholder="Search products..."
                    aria-label="Search products"
                >
            </form>
        </div>

        <!-- Navigation -->
        <div class="mobile-menu-body">
            <nav>
                <?php
                $mobileMenuItems = [
                    ['title' => 'Home', 'url' => '/'],
                    ['title' => 'All Products', 'url' => '/products'],
                    ['title' => 'Categories', 'url' => '/categories', 'children' => []],
                    ['title' => 'New Arrivals', 'url' => '/products?sort=newest'],
                    ['title' => 'Best Sellers', 'url' => '/products?sort=popular'],
                    ['title' => 'Sale', 'url' => '/products?on_sale=1'],
                    ['title' => 'Contact', 'url' => '/contact'],
                ];
                ?>

                <?php foreach ($mobileMenuItems as $item): ?>
                <div class="mobile-nav-item">
                    <?php if (!empty($item['children'])): ?>
                    <button class="mobile-nav-link" aria-expanded="false">
                        <span><?= e($item['title']) ?></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="mobile-nav-submenu">
                        <?php foreach ($item['children'] as $child): ?>
                        <a href="<?= url($child['url']) ?>" class="mobile-nav-sublink"><?= e($child['title']) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <a href="<?= url($item['url']) ?>" class="mobile-nav-link">
                        <span><?= e($item['title']) ?></span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Footer -->
        <div class="mobile-menu-footer">
            <?php if (auth()): ?>
            <a href="<?= url('/account') ?>" class="btn btn-outline btn-block mb-3">My Account</a>
            <form action="<?= url('/logout') ?>" method="POST">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-ghost btn-block">Logout</button>
            </form>
            <?php else: ?>
            <a href="<?= url('/login') ?>" class="btn btn-primary btn-block mb-3">Login</a>
            <a href="<?= url('/register') ?>" class="btn btn-outline btn-block">Create Account</a>
            <?php endif; ?>
        </div>
    </div>
</div>
