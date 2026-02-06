<?php
$appearance = getAppearance();
if ($appearance['trust_badges_enabled'] ?? true):
$badges = [
    ['icon' => 'truck', 'title' => $appearance['trust_badge_1_title'] ?? 'Free Delivery', 'subtitle' => $appearance['trust_badge_1_subtitle'] ?? 'Orders over R500'],
    ['icon' => 'shield', 'title' => $appearance['trust_badge_2_title'] ?? 'Secure Payment', 'subtitle' => $appearance['trust_badge_2_subtitle'] ?? '100% Protected'],
    ['icon' => 'award', 'title' => $appearance['trust_badge_3_title'] ?? 'Quality Products', 'subtitle' => $appearance['trust_badge_3_subtitle'] ?? 'Best brands only'],
    ['icon' => 'headphones', 'title' => $appearance['trust_badge_4_title'] ?? '24/7 Support', 'subtitle' => $appearance['trust_badge_4_subtitle'] ?? 'Always here to help'],
];
?>
<section class="trust-badges-section">
    <div class="container">
        <div class="trust-badges-grid">
            <?php foreach ($badges as $badge): ?>
            <div class="trust-badge-item">
                <div class="trust-badge-icon">
                    <?php if ($badge['icon'] === 'truck'): ?>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    <?php elseif ($badge['icon'] === 'shield'): ?>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <?php elseif ($badge['icon'] === 'award'): ?>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                    <?php elseif ($badge['icon'] === 'headphones'): ?>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                    <?php elseif ($badge['icon'] === 'lock'): ?>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <?php else: ?>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <?php endif; ?>
                </div>
                <div class="trust-badge-text">
                    <strong><?= e($badge['title']) ?></strong>
                    <span><?= e($badge['subtitle']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
