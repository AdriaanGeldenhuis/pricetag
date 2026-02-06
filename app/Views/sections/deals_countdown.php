<?php if (!empty($onSaleProducts)): ?>
<section class="py-12 deals-section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-badge badge-danger"><?= e($sectionBadge ?? 'Limited Time') ?></span>
                <h2 class="section-title"><?= e($sectionTitle ?? 'Hot Deals') ?></h2>
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <button class="carousel-btn carousel-btn-next" data-carousel-next aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>
