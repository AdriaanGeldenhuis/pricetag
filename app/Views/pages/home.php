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

<!-- Category Cards Bar -->
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
            <!-- View All Card -->
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
                    <span class="category-card-name">View All Categories</span>
                </div>
            </a>
        </div>
        <!-- Scroll Buttons -->
        <button class="category-scroll-btn category-scroll-prev" aria-label="Previous">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        <button class="category-scroll-btn category-scroll-next" aria-label="Next">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>
</section>
<?php endif; ?>

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

/* Category Cards Bar */
.category-cards-bar {
    background-color: var(--color-background-secondary);
    border-bottom: var(--border-1) solid var(--color-border);
    position: relative;
    padding: var(--space-8) 0;
}

.category-cards-container {
    max-width: 1920px;
    margin: 0 auto;
    padding: 0 var(--space-4);
    position: relative;
}

@media (min-width: 768px) {
    .category-cards-container {
        padding: 0 var(--space-8);
    }
}

.category-cards-scroll {
    display: flex;
    gap: var(--space-6);
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: var(--space-4) var(--space-2);
    align-items: flex-start;
}

@media (min-width: 768px) {
    .category-cards-scroll {
        gap: var(--space-8);
    }
}

.category-cards-scroll::-webkit-scrollbar {
    display: none;
}

/* Card items - now vertical layout with circle on top */
.category-card-item {
    flex: 0 0 auto;
    scroll-snap-align: start;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    padding: var(--space-2);
    border-radius: var(--radius-2xl);
    border: none;
    background: transparent;
    width: 130px;
}

@media (min-width: 640px) {
    .category-card-item {
        width: 150px;
    }
}

@media (min-width: 1024px) {
    .category-card-item {
        width: 160px;
    }
}

.category-card-item:hover {
    transform: translateY(-6px);
}

/* 3D Ring container */
.category-card-ring {
    position: relative;
    width: 110px;
    height: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--space-3);
}

@media (min-width: 640px) {
    .category-card-ring {
        width: 120px;
        height: 120px;
    }
}

@media (min-width: 1024px) {
    .category-card-ring {
        width: 130px;
        height: 130px;
    }
}

/* 3D Ring border effect */
.category-card-ring-border {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    padding: 3px;
    background: conic-gradient(
        from 0deg,
        rgba(239, 68, 68, 0.9),
        rgba(249, 115, 22, 0.9),
        rgba(234, 179, 8, 0.9),
        rgba(34, 197, 94, 0.9),
        rgba(59, 130, 246, 0.9),
        rgba(168, 85, 247, 0.9),
        rgba(239, 68, 68, 0.9)
    );
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    animation: ringRotate 4s linear infinite;
    filter: blur(0.5px);
    opacity: 0.85;
    transition: opacity 0.3s ease, filter 0.3s ease;
}

.category-card-item:hover .category-card-ring-border {
    opacity: 1;
    filter: blur(0px);
    animation-duration: 2s;
}

/* Outer glow effect */
.category-card-ring::before {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: conic-gradient(
        from 0deg,
        rgba(239, 68, 68, 0.15),
        rgba(249, 115, 22, 0.15),
        rgba(234, 179, 8, 0.15),
        rgba(34, 197, 94, 0.15),
        rgba(59, 130, 246, 0.15),
        rgba(168, 85, 247, 0.15),
        rgba(239, 68, 68, 0.15)
    );
    filter: blur(8px);
    animation: ringRotate 4s linear infinite;
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: -1;
}

.category-card-item:hover .category-card-ring::before {
    opacity: 1;
}

/* Inner shadow for 3D depth */
.category-card-ring::after {
    content: '';
    position: absolute;
    inset: 3px;
    border-radius: 50%;
    box-shadow:
        inset 0 2px 8px rgba(0, 0, 0, 0.4),
        inset 0 -1px 4px rgba(255, 255, 255, 0.05),
        0 2px 12px rgba(0, 0, 0, 0.3);
    pointer-events: none;
    z-index: 1;
}

@keyframes ringRotate {
    to {
        transform: rotate(360deg);
    }
}

/* Circular image inside the ring */
.category-card-image {
    position: relative;
    width: calc(100% - 8px);
    height: calc(100% - 8px);
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(145deg, var(--color-background-alt) 0%, var(--color-background) 100%);
    z-index: 2;
}

.category-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    border-radius: 50%;
}

.category-card-item:hover .category-card-image img {
    transform: scale(1.15);
}

.category-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
}

/* Card text content */
.category-card-content {
    padding: 0 var(--space-1);
    text-align: center;
    width: 100%;
}

.category-card-name {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.3s ease;
}

@media (min-width: 768px) {
    .category-card-name {
        font-size: var(--text-base);
    }
}

.category-card-item:hover .category-card-name {
    color: var(--color-primary);
}

/* View All Card */
.category-card-view-all .category-card-image {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-700) 100%);
}

.category-card-view-all-icon {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text);
}

.category-card-view-all:hover .category-card-image {
    background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-800) 100%);
}

.category-card-view-all .category-card-name {
    color: var(--color-primary);
}

.category-card-view-all .category-card-ring-border {
    background: conic-gradient(
        from 0deg,
        var(--color-primary),
        var(--color-primary-600),
        var(--color-primary-400),
        var(--color-primary-600),
        var(--color-primary)
    );
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
}

/* Scroll Buttons */
.category-scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-full);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    color: var(--color-text);
    cursor: pointer;
    transition: var(--transition-all);
    opacity: 0;
}

.category-cards-container:hover .category-scroll-btn {
    opacity: 1;
}

.category-scroll-btn:hover {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.category-scroll-prev {
    left: var(--space-2);
}

.category-scroll-next {
    right: var(--space-2);
}

@media (min-width: 768px) {
    .category-scroll-prev {
        left: var(--space-4);
    }

    .category-scroll-next {
        right: var(--space-4);
    }
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
    background: rgba(139, 43, 43, 0.2);
    color: var(--color-primary-400);
}

.badge-success {
    background: rgba(34, 197, 94, 0.15);
    color: var(--color-success);
}

.badge-danger {
    background: rgba(239, 68, 68, 0.15);
    color: var(--color-danger);
}

.badge-warning {
    background: rgba(245, 158, 11, 0.15);
    color: var(--color-warning);
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
    color: var(--color-accent);
    transition: var(--transition-colors);
}

.section-link:hover {
    color: var(--color-text);
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
    background: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-2xl);
    text-align: center;
    transition: var(--transition-all);
}

.category-spotlight-item:hover {
    background: var(--color-background-hover);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    transform: translateY(-4px);
    border-color: var(--color-primary);
}

.category-spotlight-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-background-hover);
    color: var(--color-text-secondary);
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
    background: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-full);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
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

/* Deals Section - Dark Theme */
.deals-section {
    background: linear-gradient(180deg, rgba(239, 68, 68, 0.1) 0%, var(--color-background) 100%);
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

    // Category Cards Scroll
    initCategoryCardsScroll();

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

// Category Cards Scroll
function initCategoryCardsScroll() {
    const container = document.querySelector('.category-cards-container');
    if (!container) return;

    const scroll = container.querySelector('.category-cards-scroll');
    const prevBtn = container.querySelector('.category-scroll-prev');
    const nextBtn = container.querySelector('.category-scroll-next');

    if (!scroll) return;

    const scrollAmount = 250;

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            scroll.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            scroll.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }
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
