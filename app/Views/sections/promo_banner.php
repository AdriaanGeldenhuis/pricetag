<?php
/**
 * Promotional banner section - configurable from admin
 * Shows banners from the 'homepage_middle' location
 */
$promoBanners = $promoBanners ?? [];
if (!empty($promoBanners)):
?>
<section class="py-8">
    <div class="container">
        <div class="promo-banner-grid">
            <?php foreach ($promoBanners as $banner): ?>
            <a href="<?= e($banner['url'] ?? '#') ?>" class="promo-banner-item">
                <img src="<?= url('storage/uploads/' . e($banner['image'])) ?>" alt="<?= e($banner['title'] ?? '') ?>" loading="lazy">
                <?php if (!empty($banner['title'])): ?>
                <div class="promo-banner-overlay">
                    <h3 class="promo-banner-title"><?= e($banner['title']) ?></h3>
                    <?php if (!empty($banner['button_text'])): ?>
                    <span class="promo-banner-btn"><?= e($banner['button_text']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
