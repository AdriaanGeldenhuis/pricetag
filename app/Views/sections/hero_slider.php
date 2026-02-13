<?php if (!empty($heroBanners)): ?>
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
    <button class="hero-slider-btn hero-slider-prev" aria-label="Previous slide">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <button class="hero-slider-btn hero-slider-next" aria-label="Next slide">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>
    <div class="hero-slider-dots">
        <?php foreach ($heroBanners as $index => $banner): ?>
        <button class="hero-dot <?= $index === 0 ? 'is-active' : '' ?>" data-slide="<?= $index ?>" aria-label="Go to slide <?= $index + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
<?php else: ?>
<section class="hero-section hero-default">
    <div class="container text-center" style="color: white;">
        <h1 class="hero-title"><?= e(config('app.name')) ?></h1>
        <p class="hero-subtitle">Premium products at unbeatable prices</p>
        <a href="<?= url('/categories') ?>" class="btn btn-lg btn-accent">Shop Now</a>
    </div>
</section>
<?php endif; ?>
