<!-- Home Page -->

<?php if (!empty($heroBanners)): ?>
<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-slider" id="hero-slider">
        <?php foreach ($heroBanners as $banner): ?>
        <div class="hero-slide">
            <picture>
                <?php if ($banner['mobile_image']): ?>
                <source media="(max-width: 768px)" srcset="<?= url('storage/uploads/' . e($banner['mobile_image'])) ?>">
                <?php endif; ?>
                <img src="<?= url('storage/uploads/' . e($banner['image'])) ?>" alt="<?= e($banner['title']) ?>" loading="eager">
            </picture>
            <div class="hero-content container" style="color: <?= e($banner['text_color'] ?? '#ffffff') ?>">
                <?php if ($banner['title']): ?>
                <h1 class="hero-title"><?= e($banner['title']) ?></h1>
                <?php endif; ?>
                <?php if ($banner['subtitle']): ?>
                <p class="hero-subtitle"><?= e($banner['subtitle']) ?></p>
                <?php endif; ?>
                <?php if ($banner['url']): ?>
                <a href="<?= e($banner['url']) ?>" class="btn btn-lg btn-accent">
                    <?= e($banner['button_text'] ?? 'Shop Now') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php else: ?>
<!-- Default Hero -->
<section class="hero-section py-16 bg-gradient-to-r from-primary-600 to-primary-800">
    <div class="container text-center" style="color: white;">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to <?= e(config('app.name')) ?></h1>
        <p class="text-xl mb-8 opacity-90">Premium products at unbeatable prices</p>
        <a href="<?= url('/products') ?>" class="btn btn-lg btn-accent">Shop Now</a>
    </div>
</section>
<?php endif; ?>

<!-- Trust Badges -->
<section class="trust-badges py-8 bg-background-alt">
    <div class="container">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="trust-badge">
                <svg class="mx-auto mb-2" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="1" y="3" width="15" height="13"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
                <h3 class="font-semibold text-sm">Free Delivery</h3>
                <p class="text-xs text-muted">Orders over R500</p>
            </div>
            <div class="trust-badge">
                <svg class="mx-auto mb-2" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <h3 class="font-semibold text-sm">Secure Payment</h3>
                <p class="text-xs text-muted">100% Protected</p>
            </div>
            <div class="trust-badge">
                <svg class="mx-auto mb-2" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    <polyline points="17 6 23 6 23 12"/>
                </svg>
                <h3 class="font-semibold text-sm">Best Prices</h3>
                <p class="text-xs text-muted">Guaranteed</p>
            </div>
            <div class="trust-badge">
                <svg class="mx-auto mb-2" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <h3 class="font-semibold text-sm">24/7 Support</h3>
                <p class="text-xs text-muted">Always here</p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featuredCategories)): ?>
<!-- Featured Categories -->
<section class="py-12">
    <div class="container">
        <h2 class="text-2xl font-semibold text-center mb-8">Shop by Category</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($featuredCategories as $category): ?>
            <a href="<?= url('/categories/' . $category->slug) ?>" class="category-card text-center p-4 rounded-xl bg-background-alt hover:shadow-md transition-all">
                <?php if ($category->icon): ?>
                <div class="category-icon mb-3">
                    <img src="<?= url('storage/uploads/' . e($category->icon)) ?>" alt="" class="mx-auto w-12 h-12">
                </div>
                <?php else: ?>
                <div class="category-icon mb-3 w-12 h-12 mx-auto bg-primary-100 rounded-full flex items-center justify-center">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                </div>
                <?php endif; ?>
                <h3 class="font-medium text-sm"><?= e($category->name) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($trendingProducts)): ?>
<!-- Trending Products -->
<section class="py-12 bg-background-alt">
    <div class="container">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-semibold">Trending Now</h2>
            <a href="<?= url('/products?sort=popular') ?>" class="text-primary font-medium text-sm">View All</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($trendingProducts as $product): ?>
            <?php include APP_PATH . '/Views/components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($newArrivals)): ?>
<!-- New Arrivals -->
<section class="py-12">
    <div class="container">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-semibold">New Arrivals</h2>
            <a href="<?= url('/products?sort=newest') ?>" class="text-primary font-medium text-sm">View All</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($newArrivals as $product): ?>
            <?php include APP_PATH . '/Views/components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($onSaleProducts)): ?>
<!-- On Sale -->
<section class="py-12 bg-danger-50">
    <div class="container">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-semibold text-danger">Hot Deals</h2>
            <a href="<?= url('/products?on_sale=1') ?>" class="text-danger font-medium text-sm">View All</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($onSaleProducts as $product): ?>
            <?php include APP_PATH . '/Views/components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter -->
<section class="py-16 bg-primary text-white">
    <div class="container text-center">
        <h2 class="text-2xl font-semibold mb-2">Stay Updated</h2>
        <p class="mb-6 opacity-90">Subscribe to our newsletter for exclusive deals and updates</p>
        <form action="<?= url('/newsletter/subscribe') ?>" method="POST" class="max-w-md mx-auto flex gap-3">
            <?= csrfField() ?>
            <input type="email" name="email" placeholder="Enter your email" required
                   class="flex-1 px-4 py-3 rounded-lg text-neutral-900 bg-white">
            <button type="submit" class="btn btn-accent btn-lg">Subscribe</button>
        </form>
    </div>
</section>

<style>
.hero-section {
    position: relative;
    min-height: 400px;
    background: var(--color-primary-600);
}
.hero-slide {
    position: relative;
}
.hero-slide img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}
@media (min-width: 768px) {
    .hero-slide img {
        height: 500px;
    }
}
.hero-content {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
}
.hero-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}
@media (min-width: 768px) {
    .hero-title {
        font-size: 3rem;
    }
}
.hero-subtitle {
    font-size: 1.125rem;
    margin-bottom: 1.5rem;
    opacity: 0.9;
}
</style>
