<!-- Mobile Menu - Fullscreen -->
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
        <div class="mobile-menu-search">
            <form action="<?= url('/search') ?>" method="GET">
                <input
                    type="search"
                    name="q"
                    class="form-input"
                    placeholder="Search products..."
                    aria-label="Search products"
                    autocomplete="off"
                >
            </form>
        </div>

        <!-- Body -->
        <div class="mobile-menu-body">
            <!-- Quick Links -->
            <div class="mobile-quick-links">
                <a href="<?= url('/products?featured=1') ?>" class="mobile-quick-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    Featured
                </a>
                <a href="<?= url('/products?on_sale=1') ?>" class="mobile-quick-pill mobile-quick-sale">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    Deals
                </a>
                <a href="<?= url('/products?sort=newest') ?>" class="mobile-quick-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    New
                </a>
                <a href="<?= url('/products?sort=popular') ?>" class="mobile-quick-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    Popular
                </a>
            </div>

            <!-- Categories Grid -->
            <div class="mobile-cat-section">
                <div class="mobile-cat-header">
                    <span class="mobile-cat-title">Categories</span>
                    <a href="<?= url('/categories') ?>" class="mobile-cat-view-all">View All</a>
                </div>
                <div class="mobile-cat-grid">
                    <?php
                    $mobileCategories = \App\Models\Category::forMenu();
                    $mCatIdx = 0;
                    foreach ($mobileCategories as $cat):
                        $catIcon = is_array($cat) ? ($cat['icon'] ?? '') : ($cat->icon ?? '');
                        $catSlug = is_array($cat) ? ($cat['slug'] ?? '') : ($cat->slug ?? '');
                        $catImage = is_array($cat) ? ($cat['image'] ?? '') : ($cat->image ?? '');
                        $catName = is_array($cat) ? ($cat['name'] ?? '') : ($cat->name ?? '');
                        $catCount = is_array($cat) ? ($cat['product_count'] ?? 0) : ($cat->product_count ?? 0);
                    ?>
                    <a href="<?= url('/categories/' . $catSlug) ?>" class="mobile-cat-card" style="--mci: <?= $mCatIdx ?>">
                        <span class="mobile-cat-ring">
                            <span class="mobile-cat-ring-inner">
                                <?php if (!empty($catImage)): ?>
                                <img src="<?= url('storage/uploads/' . e($catImage)) ?>" alt="" loading="lazy">
                                <?php elseif (!empty($catIcon)): ?>
                                <img src="<?= url('storage/uploads/' . e($catIcon)) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                    <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                </svg>
                                <?php endif; ?>
                            </span>
                        </span>
                        <span class="mobile-cat-name"><?= e($catName) ?></span>
                    </a>
                    <?php $mCatIdx++; endforeach; ?>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="mobile-nav-links">
                <a href="<?= url('/') ?>" class="mobile-nav-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span>Home</span>
                </a>
                <a href="<?= url('/products') ?>" class="mobile-nav-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span>All Products</span>
                </a>
                <a href="<?= url('/contact') ?>" class="mobile-nav-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span>Contact</span>
                </a>
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

<style>
/* =========================================================================
   MOBILE MENU - Fullscreen Premium Redesign
   ========================================================================= */

/* Override base mobile menu panel to fullscreen */
.mobile-menu-panel {
    width: 100% !important;
    background: linear-gradient(180deg, #1a1a24 0%, #14141c 60%, #111118 100%) !important;
    transform: translateX(-100%);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.mobile-menu.is-open .mobile-menu-panel {
    transform: translateX(0);
}

/* Header */
.mobile-menu-header {
    padding: var(--space-4) var(--space-5);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    position: relative;
}

.mobile-menu-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg,
        transparent,
        rgba(120, 120, 140, 0.3),
        rgba(200, 200, 220, 0.6),
        rgba(255, 255, 255, 0.9),
        rgba(200, 200, 220, 0.6),
        rgba(120, 120, 140, 0.3),
        transparent);
    background-size: 200% 100%;
    animation: mobileShimmer 4s linear infinite;
}

@keyframes mobileShimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}

.mobile-menu-close {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Search */
.mobile-menu-search {
    padding: var(--space-3) var(--space-5);
}

.mobile-menu-search .form-input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-full);
    padding: var(--space-3) var(--space-4);
    color: var(--color-text);
    width: 100%;
    font-size: var(--text-sm);
}

/* Body */
.mobile-menu-body {
    flex: 1;
    padding: 0;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

/* Quick Links Strip */
.mobile-quick-links {
    display: flex;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-5);
    overflow-x: auto;
    scrollbar-width: none;
}

.mobile-quick-links::-webkit-scrollbar { display: none; }

.mobile-quick-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-text-secondary);
    transition: all 0.2s ease;
}

.mobile-quick-pill:active {
    background: rgba(139, 43, 43, 0.2);
    border-color: rgba(139, 43, 43, 0.4);
    color: #e8a0a0;
}

.mobile-quick-sale {
    border-color: rgba(245, 158, 11, 0.3);
    color: #f59e0b;
}

/* ---- Category Section ---- */
.mobile-cat-section {
    padding: var(--space-3) var(--space-5) var(--space-4);
}

.mobile-cat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-3);
}

.mobile-cat-title {
    font-size: var(--text-base);
    font-weight: 700;
    color: var(--color-text);
}

.mobile-cat-view-all {
    font-size: 12px;
    font-weight: 600;
    color: var(--color-primary);
}

/* Category Grid - 3 cols on mobile */
.mobile-cat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-3);
}

@media (min-width: 480px) {
    .mobile-cat-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Category Card */
.mobile-cat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: var(--space-2) 4px;
    border-radius: var(--radius-lg);
    text-align: center;
    opacity: 0;
    animation: mobileCatIn 0.35s ease forwards;
    animation-delay: calc(var(--mci, 0) * 0.04s);
}

.mobile-menu.is-open .mobile-cat-card {
    animation: mobileCatIn 0.35s ease forwards;
    animation-delay: calc(var(--mci, 0) * 0.04s + 0.15s);
}

@keyframes mobileCatIn {
    from { opacity: 0; transform: scale(0.85); }
    to { opacity: 1; transform: scale(1); }
}

.mobile-cat-card:active {
    transform: scale(0.95);
}

/* Ring */
.mobile-cat-ring {
    width: 56px;
    height: 56px;
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
    animation: mobileRingSpin 8s linear infinite;
    flex-shrink: 0;
}

@keyframes mobileRingSpin {
    to { transform: rotate(360deg); }
}

.mobile-cat-ring-inner {
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

.mobile-cat-ring-inner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mobile-cat-name {
    font-size: 11px;
    font-weight: 600;
    color: var(--color-text-secondary);
    line-height: 1.2;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ---- Nav Links ---- */
.mobile-nav-links {
    padding: 0 var(--space-5);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.mobile-nav-row {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4) 0;
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--color-text-secondary);
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.mobile-nav-row svg {
    color: var(--color-text-muted);
    flex-shrink: 0;
}

.mobile-nav-row:active {
    color: var(--color-text);
}

.mobile-nav-row:last-child {
    border-bottom: none;
}

/* Footer */
.mobile-menu-footer {
    padding: var(--space-4) var(--space-5);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
</style>
