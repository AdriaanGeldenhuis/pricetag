<!-- Site Header -->
<header class="site-header" id="site-header">
    <div class="container">
        <div class="header-inner">
            <!-- Mobile Menu Toggle -->
            <button type="button" class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Open menu" aria-expanded="false">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <!-- Logo -->
            <?php $headerBranding = getBranding(); ?>
            <a href="<?= url('/') ?>" class="header-logo" aria-label="<?= e(config('app.name')) ?> - Home">
                <?php if (!empty($headerBranding['logo'])): ?>
                <img src="<?= url($headerBranding['logo']) ?>" alt="<?= e(config('app.name')) ?>" class="header-logo-img" style="max-height: 50px; width: auto;">
                <?php else: ?>
                <span class="header-logo-text"><?= e(config('app.name')) ?></span>
                <?php endif; ?>
            </a>

            <!-- Search -->
            <div class="header-search">
                <form action="<?= url('/search') ?>" method="GET" class="search-form" role="search">
                    <input
                        type="search"
                        name="q"
                        class="search-input"
                        placeholder="Search products..."
                        autocomplete="off"
                        id="header-search-input"
                        aria-label="Search products"
                    >
                    <button type="submit" class="search-btn" aria-label="Search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                    </button>
                    <!-- Search Suggestions -->
                    <div class="search-suggestions" id="search-suggestions" role="listbox"></div>
                </form>
            </div>

            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Account -->
                <?php if (auth()): ?>
                <a href="<?= url('/account') ?>" class="header-action-btn" aria-label="My Account">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
                <?php else: ?>
                <a href="<?= url('/login') ?>" class="header-action-btn" aria-label="Login">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
                <?php endif; ?>

                <!-- Wishlist -->
                <a href="<?= url('/wishlist') ?>" class="header-action-btn" aria-label="Wishlist">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    <span class="header-action-count" id="wishlist-count" data-count="<?= wishlistCount() ?>"><?= wishlistCount() > 0 ? wishlistCount() : '' ?></span>
                </a>

                <!-- Cart -->
                <button type="button" class="header-action-btn" id="cart-toggle" aria-label="Shopping cart" aria-haspopup="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="header-action-count" id="cart-count" data-count="<?= cartCount() ?>"><?= cartCount() > 0 ? cartCount() : '' ?></span>
                </button>
            </div>
        </div>
    </div>
</header>
