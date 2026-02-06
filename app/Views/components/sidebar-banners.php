<?php
/**
 * Sidebar Banners Component
 * Renders banners from the database for a given location.
 *
 * Usage: include this file with $sidebarLocation set, e.g.:
 *   $sidebarLocation = 'sidebar';
 *   include APP_PATH . '/Views/components/sidebar-banners.php';
 *
 * Supported locations: sidebar, product_sidebar, category_top, homepage_top, homepage_bottom
 */

$sidebarLocation = $sidebarLocation ?? 'sidebar';
$sidebarBanners = getBanners($sidebarLocation);
?>

<?php if (!empty($sidebarBanners)): ?>
<div class="sidebar-banners-widget">
    <?php foreach ($sidebarBanners as $sBanner): ?>
    <div class="sidebar-banner-item">
        <?php if (!empty($sBanner['url'])): ?>
        <a href="<?= e($sBanner['url']) ?>" class="sidebar-banner-link-wrap">
        <?php endif; ?>

            <?php if (!empty($sBanner['image'])): ?>
            <div class="sidebar-banner-image">
                <img src="<?= url('storage/uploads/' . e($sBanner['image'])) ?>"
                     alt="<?= e($sBanner['title'] ?: 'Promotional banner') ?>"
                     loading="lazy">
                <?php if ($sBanner['overlay_opacity'] > 0): ?>
                <div class="sidebar-banner-overlay" style="opacity: <?= ((int)$sBanner['overlay_opacity']) / 100 ?>"></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($sBanner['title']) || !empty($sBanner['subtitle'])): ?>
            <div class="sidebar-banner-body"<?= !empty($sBanner['text_color']) ? ' style="color: ' . e($sBanner['text_color']) . '"' : '' ?>>
                <?php if (!empty($sBanner['title'])): ?>
                <h4 class="sidebar-banner-title"><?= e($sBanner['title']) ?></h4>
                <?php endif; ?>
                <?php if (!empty($sBanner['subtitle'])): ?>
                <p class="sidebar-banner-text"><?= e($sBanner['subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($sBanner['button_text'])): ?>
                <span class="sidebar-banner-btn"><?= e($sBanner['button_text']) ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php if (!empty($sBanner['url'])): ?>
        </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
// Also show generic sidebar banners (location='sidebar') if we're on a specific page
// and this location is different from 'sidebar'
if ($sidebarLocation !== 'sidebar') {
    $genericBanners = getBanners('sidebar');
    if (!empty($genericBanners)):
?>
<div class="sidebar-banners-widget sidebar-banners-generic">
    <?php foreach ($genericBanners as $sBanner): ?>
    <div class="sidebar-banner-item">
        <?php if (!empty($sBanner['url'])): ?>
        <a href="<?= e($sBanner['url']) ?>" class="sidebar-banner-link-wrap">
        <?php endif; ?>

            <?php if (!empty($sBanner['image'])): ?>
            <div class="sidebar-banner-image">
                <img src="<?= url('storage/uploads/' . e($sBanner['image'])) ?>"
                     alt="<?= e($sBanner['title'] ?: 'Promotional banner') ?>"
                     loading="lazy">
                <?php if ($sBanner['overlay_opacity'] > 0): ?>
                <div class="sidebar-banner-overlay" style="opacity: <?= ((int)$sBanner['overlay_opacity']) / 100 ?>"></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($sBanner['title']) || !empty($sBanner['subtitle'])): ?>
            <div class="sidebar-banner-body"<?= !empty($sBanner['text_color']) ? ' style="color: ' . e($sBanner['text_color']) . '"' : '' ?>>
                <?php if (!empty($sBanner['title'])): ?>
                <h4 class="sidebar-banner-title"><?= e($sBanner['title']) ?></h4>
                <?php endif; ?>
                <?php if (!empty($sBanner['subtitle'])): ?>
                <p class="sidebar-banner-text"><?= e($sBanner['subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($sBanner['button_text'])): ?>
                <span class="sidebar-banner-btn"><?= e($sBanner['button_text']) ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php if (!empty($sBanner['url'])): ?>
        </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php
    endif;
}
?>
