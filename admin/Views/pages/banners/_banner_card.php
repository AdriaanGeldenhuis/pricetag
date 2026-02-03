<!-- Banner Card Partial -->
<div class="banner-card" data-id="<?= $banner['id'] ?>">
    <div class="banner-image">
        <img src="<?= e($banner['image']) ?>" alt="<?= e($banner['title'] ?: 'Banner') ?>">
        <?php if ($banner['title'] || $banner['subtitle']): ?>
        <div class="banner-overlay">
            <?php if ($banner['title']): ?>
            <div class="banner-title"><?= e($banner['title']) ?></div>
            <?php endif; ?>
            <?php if ($banner['subtitle']): ?>
            <div class="banner-subtitle"><?= e(substr($banner['subtitle'], 0, 60)) ?><?= strlen($banner['subtitle']) > 60 ? '...' : '' ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="banner-status">
            <?php if ($banner['is_active']): ?>
            <span class="admin-badge active">Active</span>
            <?php else: ?>
            <span class="admin-badge inactive">Inactive</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="banner-body">
        <div class="banner-meta">
            <?php if ($banner['url']): ?>
            <span class="admin-badge neutral" title="<?= e($banner['url']) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                </svg>
                Link
            </span>
            <?php endif; ?>

            <?php if ($banner['button_text']): ?>
            <span class="admin-badge neutral"><?= e($banner['button_text']) ?></span>
            <?php endif; ?>

            <?php if ($banner['starts_at'] || $banner['expires_at']): ?>
            <span class="admin-badge <?= $this->isBannerScheduled($banner) ? 'warning' : 'neutral' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Scheduled
            </span>
            <?php endif; ?>

            <?php if ($banner['mobile_image']): ?>
            <span class="admin-badge neutral" title="Mobile image set">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                </svg>
                Mobile
            </span>
            <?php endif; ?>
        </div>

        <?php if ($banner['starts_at'] || $banner['expires_at']): ?>
        <div class="text-xs text-muted mb-2">
            <?php if ($banner['starts_at']): ?>
            <span>Starts: <?= date('M j, Y', strtotime($banner['starts_at'])) ?></span>
            <?php endif; ?>
            <?php if ($banner['expires_at']): ?>
            <span>Expires: <?= date('M j, Y', strtotime($banner['expires_at'])) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="banner-actions">
        <button type="button" onclick="toggleBanner(<?= $banner['id'] ?>, this)"
                class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Toggle Active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </button>

        <a href="<?= url('/admin/banners/' . $banner['id'] . '/edit') ?>"
           class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Edit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </a>

        <button type="button" onclick="deleteBanner(<?= $banner['id'] ?>)"
                class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon text-danger" title="Delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
        </button>
    </div>
</div>
