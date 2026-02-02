<!-- Home Page -->

<?php if (!empty($heroBanners)): ?>
<!-- Hero Section with Slider -->
<section class="hero-section">
    <div class="hero-slider" id="hero-slider" data-autoplay="5000">
        <?php foreach ($heroBanners as $index => $banner): ?>
        <div class="hero-slide <?= $index === 0 ? 'is-active' : '' ?>" data-slide="<?= $index ?>">
            <picture>
                <?php if (!empty($banner['mobile_image'])): ?>
                <source media="(max-width: 768px)" srcset="<?= url('storage/uploads/' . e($banner['mobile_image'])) ?>">
                <?php endif; ?>
                <img src="<?= url('storage/uploads/' . e($banner['image'])) ?>" alt="<?= e($banner['title']) ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>">
            </picture>
            <div class="hero-overlay" style="background: <?= e($banner['overlay_color'] ?? 'linear-gradient(90deg, rgba(0,0,0,0.5) 0%, transparent 100%)') ?>"></div>
            <div class="hero-content container" style="color: <?= e($banner['text_color'] ?? '#ffffff') ?>">
                <?php if (!empty($banner['badge'])): ?>
                <span class="hero-badge"><?= e($banner['badge']) ?></span>
                <?php endif; ?>
                <?php if (!empty($banner['title'])): ?>
                <h1 class="hero-title"><?= e($banner['title']) ?></h1>
                <?php endif; ?>
                <?php if (!empty($banner['subtitle'])): ?>
                <p class="hero-subtitle"><?= e($banner['subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($banner['url'])): ?>
                <div class="hero-cta">
                    <a href="<?= e($banner['url']) ?>" class="btn btn-lg btn-accent">
                        <?= e($banner['button_text'] ?? 'Shop Now') ?>
                    </a>
                    <?php if (!empty($banner['secondary_url'])): ?>
                    <a href="<?= e($banner['secondary_url']) ?>" class="btn btn-lg btn-outline-light">
                        <?= e($banner['secondary_button_text'] ?? 'Learn More') ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($heroBanners) > 1): ?>
    <!-- Slider Controls -->
    <button class="hero-slider-btn hero-slider-prev" aria-label="Previous slide">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </button>
    <button class="hero-slider-btn hero-slider-next" aria-label="Next slide">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
    </button>

    <!-- Slider Dots -->
    <div class="hero-slider-dots">
        <?php foreach ($heroBanners as $index => $banner): ?>
        <button class="hero-dot <?= $index === 0 ? 'is-active' : '' ?>" data-slide="<?= $index ?>" aria-label="Go to slide <?= $index + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
<?php else: ?>
<!-- Default Hero -->
<section class="hero-section hero-default">
    <div class="container text-center" style="color: white;">
        <h1 class="hero-title"><?= e(config('app.name')) ?></h1>
        <p class="hero-subtitle">Premium products at unbeatable prices</p>
        <a href="<?= url('/products') ?>" class="btn btn-lg btn-accent">Shop Now</a>
    </div>
</section>
<?php endif; ?>

<!-- Trust Badges -->
<section class="trust-badges py-8 bg-background-alt">
    <div class="container">
        <div class="trust-badges-grid">
            <div class="trust-badge">
                <div class="trust-badge-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="1" y="3" width="15" height="13"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                </div>
                <div class="trust-badge-content">
                    <h3>Free Delivery</h3>
                    <p>Orders over R500</p>
                </div>
            </div>
            <div class="trust-badge">
                <div class="trust-badge-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <div class="trust-badge-content">
                    <h3>Secure Payment</h3>
                    <p>100% Protected</p>
                </div>
            </div>
            <div class="trust-badge">
                <div class="trust-badge-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                </div>
                <div class="trust-badge-content">
                    <h3>Quality Products</h3>
                    <p>Best brands only</p>
                </div>
            </div>
            <div class="trust-badge">
                <div class="trust-badge-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div class="trust-badge-content">
                    <h3>24/7 Support</h3>
                    <p>Always here to help</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featuredCategories)): ?>
<!-- Category Spotlight -->
<section class="py-12">
    <div class="container">
        <div class="section-header text-center mb-8">
            <h2 class="section-title">Shop by Category</h2>
            <p class="section-subtitle">Browse our most popular categories</p>
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

<?php if (!empty($newArrivals)): ?>
<!-- New Arrivals Carousel -->
<section class="py-12 bg-background-alt">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-badge badge-primary">Just In</span>
                <h2 class="section-title">New Arrivals</h2>
            </div>
            <a href="<?= url('/products?sort=newest') ?>" class="section-link">
                View All
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
        <div class="product-carousel" data-carousel="new-arrivals">
            <div class="product-carousel-track">
                <?php foreach ($newArrivals as $product): ?>
                <div class="product-carousel-slide">
                    <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-btn carousel-btn-prev" data-carousel-prev aria-label="Previous">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-btn carousel-btn-next" data-carousel-next aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($bestSellers)): ?>
<!-- Best Sellers Carousel -->
<section class="py-12">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-badge badge-success">Top Rated</span>
                <h2 class="section-title">Best Sellers</h2>
            </div>
            <a href="<?= url('/products?sort=popular') ?>" class="section-link">
                View All
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
        <div class="product-carousel" data-carousel="best-sellers">
            <div class="product-carousel-track">
                <?php foreach ($bestSellers as $product): ?>
                <div class="product-carousel-slide">
                    <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-btn carousel-btn-prev" data-carousel-prev aria-label="Previous">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-btn carousel-btn-next" data-carousel-next aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($onSaleProducts)): ?>
<!-- Hot Deals with Countdown -->
<section class="py-12 deals-section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-badge badge-danger">Limited Time</span>
                <h2 class="section-title">Hot Deals</h2>
            </div>
            <?php if (!empty($flashSale)): ?>
            <div class="countdown-timer" data-countdown="<?= e($flashSale['ends_at']) ?>">
                <span class="countdown-label">Ends in:</span>
                <div class="countdown-units">
                    <div class="countdown-unit">
                        <span class="countdown-value" data-days>00</span>
                        <span class="countdown-text">Days</span>
                    </div>
                    <span class="countdown-separator">:</span>
                    <div class="countdown-unit">
                        <span class="countdown-value" data-hours>00</span>
                        <span class="countdown-text">Hours</span>
                    </div>
                    <span class="countdown-separator">:</span>
                    <div class="countdown-unit">
                        <span class="countdown-value" data-minutes>00</span>
                        <span class="countdown-text">Mins</span>
                    </div>
                    <span class="countdown-separator">:</span>
                    <div class="countdown-unit">
                        <span class="countdown-value" data-seconds>00</span>
                        <span class="countdown-text">Secs</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <a href="<?= url('/products?on_sale=1') ?>" class="section-link">
                View All
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
        <div class="product-carousel" data-carousel="deals">
            <div class="product-carousel-track">
                <?php foreach ($onSaleProducts as $product): ?>
                <div class="product-carousel-slide">
                    <?php include APP_PATH . '/Views/components/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-btn carousel-btn-prev" data-carousel-prev aria-label="Previous">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-btn carousel-btn-next" data-carousel-next aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($trendingProducts)): ?>
<!-- Trending Products -->
<section class="py-12 bg-background-alt">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-badge badge-warning">Trending</span>
                <h2 class="section-title">What's Hot</h2>
            </div>
            <a href="<?= url('/products?sort=popular') ?>" class="section-link">
                View All
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
        <div class="product-grid">
            <?php foreach ($trendingProducts as $product): ?>
            <?php include APP_PATH . '/Views/components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recently Viewed (JavaScript Rendered) -->
<section class="py-12 recently-viewed-section" id="recently-viewed-section" style="display: none;">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Recently Viewed</h2>
            </div>
            <button class="section-link" id="clear-recently-viewed">
                Clear All
            </button>
        </div>
        <div class="product-carousel" data-carousel="recently-viewed">
            <div class="product-carousel-track" id="recently-viewed-products"></div>
            <button class="carousel-btn carousel-btn-prev" data-carousel-prev aria-label="Previous">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-btn carousel-btn-next" data-carousel-next aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter-section py-16">
    <div class="container">
        <div class="newsletter-content text-center">
            <div class="newsletter-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <h2 class="newsletter-title">Stay in the Loop</h2>
            <p class="newsletter-text">Subscribe for exclusive deals, new arrivals, and insider-only discounts</p>
            <form id="newsletter-form" class="newsletter-form" action="<?= url('/newsletter/subscribe') ?>" method="POST">
                <?= csrfField() ?>
                <div class="newsletter-input-group">
                    <input type="email"
                           name="email"
                           id="newsletter-email"
                           placeholder="Enter your email address"
                           required
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                           class="newsletter-input"
                           aria-label="Email address">
                    <button type="submit" class="btn btn-accent btn-lg newsletter-btn">
                        <span class="newsletter-btn-text">Subscribe</span>
                        <span class="newsletter-btn-loading" style="display: none;">
                            <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" opacity="0.25"></circle>
                                <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"></path>
                            </svg>
                        </span>
                    </button>
                </div>
                <p class="newsletter-error" id="newsletter-error" style="display: none;"></p>
                <p class="newsletter-success" id="newsletter-success" style="display: none;">Thanks for subscribing!</p>
                <p class="newsletter-privacy">We respect your privacy. Unsubscribe at any time.</p>
            </form>
        </div>
    </div>
</section>

<style>
/* Hero Section */
.hero-section {
    position: relative;
    overflow: hidden;
    background: var(--color-primary-700);
}

.hero-section.hero-default {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-800) 100%);
}

.hero-slider {
    position: relative;
}

.hero-slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
}

.hero-slide.is-active {
    position: relative;
    opacity: 1;
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

.hero-overlay {
    position: absolute;
    inset: 0;
}

.hero-content {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
    padding: 0 var(--space-4);
}

.hero-badge {
    display: inline-block;
    padding: var(--space-1) var(--space-3);
    background: var(--color-accent);
    color: var(--color-neutral-900);
    font-size: var(--text-xs);
    font-weight: var(--font-bold);
    text-transform: uppercase;
    border-radius: var(--radius-full);
    margin-bottom: var(--space-4);
}

.hero-title {
    font-size: clamp(1.75rem, 5vw, 3.5rem);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-4);
    line-height: var(--leading-tight);
    max-width: 600px;
}

.hero-subtitle {
    font-size: clamp(1rem, 2vw, 1.25rem);
    margin-bottom: var(--space-6);
    opacity: 0.9;
    max-width: 500px;
}

.hero-cta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-3);
}

.hero-slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.9);
    border-radius: var(--radius-full);
    color: var(--color-neutral-900);
    transition: var(--transition-all);
    opacity: 0;
}

.hero-section:hover .hero-slider-btn {
    opacity: 1;
}

.hero-slider-prev {
    left: var(--space-4);
}

.hero-slider-next {
    right: var(--space-4);
}

.hero-slider-btn:hover {
    background: white;
    box-shadow: var(--shadow-lg);
}

.hero-slider-dots {
    position: absolute;
    bottom: var(--space-6);
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: var(--space-2);
    z-index: 10;
}

.hero-dot {
    width: 10px;
    height: 10px;
    border-radius: var(--radius-full);
    background: rgba(255, 255, 255, 0.5);
    transition: var(--transition-all);
}

.hero-dot.is-active,
.hero-dot:hover {
    background: white;
    transform: scale(1.2);
}

/* Trust Badges */
.trust-badges-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 768px) {
    .trust-badges-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.trust-badge {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    background: var(--color-background);
    border-radius: var(--radius-xl);
    transition: var(--transition-all);
}

.trust-badge:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.trust-badge-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary-50);
    color: var(--color-primary);
    border-radius: var(--radius-lg);
    flex-shrink: 0;
}

.trust-badge-content h3 {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    margin-bottom: 2px;
}

.trust-badge-content p {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
}

/* Section Headers */
.section-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: var(--space-4);
    margin-bottom: var(--space-8);
}

.section-badge {
    display: inline-block;
    padding: var(--space-1) var(--space-3);
    font-size: 10px;
    font-weight: var(--font-bold);
    text-transform: uppercase;
    border-radius: var(--radius-full);
    margin-bottom: var(--space-2);
}

.badge-primary {
    background: var(--color-primary-100);
    color: var(--color-primary-700);
}

.badge-success {
    background: var(--color-success-100, #dcfce7);
    color: var(--color-success-700, #15803d);
}

.badge-danger {
    background: var(--color-danger-100, #fee2e2);
    color: var(--color-danger-700, #b91c1c);
}

.badge-warning {
    background: var(--color-warning-100, #fef3c7);
    color: var(--color-warning-700, #b45309);
}

.section-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-semibold);
}

.section-subtitle {
    font-size: var(--text-base);
    color: var(--color-text-muted);
    margin-top: var(--space-2);
}

.section-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--color-primary);
    transition: var(--transition-colors);
}

.section-link:hover {
    color: var(--color-primary-700);
}

.section-link svg {
    transition: transform var(--duration-200);
}

.section-link:hover svg {
    transform: translateX(4px);
}

/* Category Spotlight */
.category-spotlight-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .category-spotlight-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1024px) {
    .category-spotlight-grid {
        grid-template-columns: repeat(6, 1fr);
    }
}

.category-spotlight-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--space-6);
    background: var(--color-background-alt);
    border-radius: var(--radius-2xl);
    text-align: center;
    transition: var(--transition-all);
}

.category-spotlight-item:hover {
    background: var(--color-background);
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}

.category-spotlight-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary-50);
    color: var(--color-primary);
    border-radius: var(--radius-xl);
    margin-bottom: var(--space-3);
    transition: var(--transition-all);
}

.category-spotlight-item:hover .category-spotlight-icon {
    background: var(--color-primary);
    color: white;
}

.category-spotlight-icon img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.category-spotlight-name {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-1);
}

.category-spotlight-count {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
}

/* Product Carousel */
.product-carousel {
    position: relative;
    overflow: hidden;
}

.product-carousel-track {
    display: flex;
    gap: var(--space-4);
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: var(--space-2);
    margin: calc(var(--space-2) * -1);
}

.product-carousel-track::-webkit-scrollbar {
    display: none;
}

.product-carousel-slide {
    flex: 0 0 calc(50% - var(--space-2));
    scroll-snap-align: start;
    min-width: 0;
}

@media (min-width: 640px) {
    .product-carousel-slide {
        flex: 0 0 calc(33.333% - var(--space-3));
    }
}

@media (min-width: 1024px) {
    .product-carousel-slide {
        flex: 0 0 calc(25% - var(--space-3));
    }
}

.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-md);
    color: var(--color-text);
    transition: var(--transition-all);
    opacity: 0;
}

.product-carousel:hover .carousel-btn {
    opacity: 1;
}

.carousel-btn:hover {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.carousel-btn-prev {
    left: 0;
}

.carousel-btn-next {
    right: 0;
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .product-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1024px) {
    .product-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Countdown Timer */
.countdown-timer {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.countdown-label {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--color-danger);
}

.countdown-units {
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.countdown-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: var(--color-danger);
    color: white;
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-lg);
    min-width: 50px;
}

.countdown-value {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    line-height: 1;
}

.countdown-text {
    font-size: 9px;
    text-transform: uppercase;
    opacity: 0.8;
}

.countdown-separator {
    font-size: var(--text-xl);
    font-weight: var(--font-bold);
    color: var(--color-danger);
}

/* Deals Section */
.deals-section {
    background: linear-gradient(180deg, var(--color-danger-50, #fef2f2) 0%, var(--color-background) 100%);
}

/* Newsletter */
.newsletter-section {
    background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-800) 100%);
    color: white;
}

.newsletter-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-full);
    margin-bottom: var(--space-4);
}

.newsletter-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-2);
}

.newsletter-text {
    font-size: var(--text-base);
    opacity: 0.9;
    margin-bottom: var(--space-6);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.newsletter-form {
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-input-group {
    display: flex;
    gap: var(--space-3);
}

.newsletter-input {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    font-size: var(--text-base);
    background: white;
    border: none;
    border-radius: var(--radius-lg);
    color: var(--color-neutral-900);
}

.newsletter-input::placeholder {
    color: var(--color-neutral-400);
}

.newsletter-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
}

.newsletter-btn {
    flex-shrink: 0;
    padding: var(--space-3) var(--space-6);
}

.newsletter-error {
    margin-top: var(--space-3);
    font-size: var(--text-sm);
    color: var(--color-accent);
}

.newsletter-success {
    margin-top: var(--space-3);
    font-size: var(--text-sm);
    color: var(--color-accent);
}

.newsletter-privacy {
    margin-top: var(--space-4);
    font-size: var(--text-xs);
    opacity: 0.7;
}

@media (max-width: 640px) {
    .newsletter-input-group {
        flex-direction: column;
    }

    .countdown-timer {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* Animation */
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hero Slider
    initHeroSlider();

    // Product Carousels
    initProductCarousels();

    // Countdown Timer
    initCountdownTimers();

    // Recently Viewed
    initRecentlyViewed();

    // Newsletter Form
    initNewsletterForm();
});

// Hero Slider
function initHeroSlider() {
    const slider = document.getElementById('hero-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    const prevBtn = document.querySelector('.hero-slider-prev');
    const nextBtn = document.querySelector('.hero-slider-next');

    if (slides.length <= 1) return;

    let currentSlide = 0;
    let autoplayInterval = null;
    const autoplayDelay = parseInt(slider.dataset.autoplay) || 5000;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('is-active', i === index);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === index);
        });
        currentSlide = index;
    }

    function nextSlide() {
        showSlide((currentSlide + 1) % slides.length);
    }

    function prevSlide() {
        showSlide((currentSlide - 1 + slides.length) % slides.length);
    }

    function startAutoplay() {
        if (autoplayInterval) clearInterval(autoplayInterval);
        autoplayInterval = setInterval(nextSlide, autoplayDelay);
    }

    function stopAutoplay() {
        if (autoplayInterval) clearInterval(autoplayInterval);
    }

    // Event listeners
    if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startAutoplay(); });
    if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startAutoplay(); });

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => { showSlide(index); startAutoplay(); });
    });

    // Touch support
    let touchStartX = 0;
    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
        stopAutoplay();
    }, { passive: true });

    slider.addEventListener('touchend', (e) => {
        const touchEndX = e.changedTouches[0].clientX;
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) nextSlide();
            else prevSlide();
        }
        startAutoplay();
    }, { passive: true });

    // Start autoplay
    startAutoplay();

    // Pause on hover
    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', startAutoplay);
}

// Product Carousels
function initProductCarousels() {
    document.querySelectorAll('.product-carousel').forEach(carousel => {
        const track = carousel.querySelector('.product-carousel-track');
        const prevBtn = carousel.querySelector('[data-carousel-prev]');
        const nextBtn = carousel.querySelector('[data-carousel-next]');

        if (!track) return;

        const scrollAmount = track.clientWidth * 0.8;

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }
    });
}

// Countdown Timer
function initCountdownTimers() {
    document.querySelectorAll('[data-countdown]').forEach(timer => {
        const endDate = new Date(timer.dataset.countdown).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const distance = endDate - now;

            if (distance < 0) {
                timer.innerHTML = '<span class="countdown-expired">Sale Ended</span>';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const daysEl = timer.querySelector('[data-days]');
            const hoursEl = timer.querySelector('[data-hours]');
            const minutesEl = timer.querySelector('[data-minutes]');
            const secondsEl = timer.querySelector('[data-seconds]');

            if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    });
}

// Recently Viewed
function initRecentlyViewed() {
    const section = document.getElementById('recently-viewed-section');
    const container = document.getElementById('recently-viewed-products');
    const clearBtn = document.getElementById('clear-recently-viewed');

    if (!section || !container) return;

    function getRecentlyViewed() {
        try {
            return JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        } catch {
            return [];
        }
    }

    function renderRecentlyViewed() {
        const products = getRecentlyViewed();
        if (products.length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';
        container.innerHTML = products.map(product => `
            <div class="product-carousel-slide">
                <article class="product-card">
                    <div class="product-card-image">
                        <a href="${product.url}">
                            ${product.image ? `<img src="${product.image}" alt="${product.name}" class="product-card-img" loading="lazy">` : '<div class="product-card-img" style="background: var(--color-neutral-100);"></div>'}
                        </a>
                    </div>
                    <div class="product-card-body">
                        <h3 class="product-card-title">
                            <a href="${product.url}">${product.name}</a>
                        </h3>
                        <div class="product-card-price">
                            <span class="product-card-price-current">${product.price}</span>
                        </div>
                    </div>
                </article>
            </div>
        `).join('');

        // Reinitialize carousel for this section
        initProductCarousels();
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            localStorage.removeItem('recentlyViewed');
            section.style.display = 'none';
        });
    }

    renderRecentlyViewed();
}

// Newsletter Form
function initNewsletterForm() {
    const form = document.getElementById('newsletter-form');
    if (!form) return;

    const emailInput = document.getElementById('newsletter-email');
    const errorEl = document.getElementById('newsletter-error');
    const successEl = document.getElementById('newsletter-success');
    const btnText = form.querySelector('.newsletter-btn-text');
    const btnLoading = form.querySelector('.newsletter-btn-loading');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = emailInput.value.trim();

        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorEl.textContent = 'Please enter a valid email address';
            errorEl.style.display = 'block';
            successEl.style.display = 'none';
            return;
        }

        // Show loading
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        errorEl.style.display = 'none';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                successEl.style.display = 'block';
                emailInput.value = '';
            } else {
                errorEl.textContent = data.message || 'Something went wrong. Please try again.';
                errorEl.style.display = 'block';
            }
        } catch (err) {
            errorEl.textContent = 'Something went wrong. Please try again.';
            errorEl.style.display = 'block';
        } finally {
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        }
    });
}
</script>
