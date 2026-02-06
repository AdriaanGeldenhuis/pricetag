<?php
/**
 * Reusable product carousel section
 * Variables: $products, $sectionTitle, $sectionSubtitle, $sectionBadge, $badgeClass, $viewAllUrl, $carouselId, $bgClass
 */
if (!empty($products)):
$carouselId = $carouselId ?? 'products';
$bgClass = $bgClass ?? '';
$badgeClass = $badgeClass ?? 'badge-primary';
?>
<section class="py-12 <?= $bgClass ?>">
    <div class="container">
        <div class="section-header">
            <div>
                <?php if (!empty($sectionBadge)): ?>
                <span class="section-badge <?= $badgeClass ?>"><?= e($sectionBadge) ?></span>
                <?php endif; ?>
                <h2 class="section-title"><?= e($sectionTitle ?? 'Products') ?></h2>
                <?php if (!empty($sectionSubtitle)): ?>
                <p class="section-subtitle"><?= e($sectionSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($viewAllUrl)): ?>
            <a href="<?= url($viewAllUrl) ?>" class="section-link">
                View All
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
            <?php endif; ?>
        </div>
        <div class="product-carousel" data-carousel="<?= $carouselId ?>">
            <div class="product-carousel-track">
                <?php foreach ($products as $product): ?>
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
