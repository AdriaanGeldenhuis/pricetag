<?php
/**
 * Product grid section
 * Variables: $products, $sectionTitle, $sectionBadge, $badgeClass, $viewAllUrl, $bgClass
 */
if (!empty($products)):
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
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
            <?php include APP_PATH . '/Views/components/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
