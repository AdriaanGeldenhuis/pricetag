<!-- Site Footer -->
<?php
$footerAppearance = getAppearance();
$footerBranding = getBranding();
$socialFacebook = getSetting('social_facebook', 'social', '');
$socialInstagram = getSetting('social_instagram', 'social', '');
$socialTwitter = getSetting('social_twitter', 'social', '');
$socialLinkedin = getSetting('social_linkedin', 'social', '');
$socialYoutube = getSetting('social_youtube', 'social', '');
$hasSocial = !empty($socialFacebook) || !empty($socialInstagram) || !empty($socialTwitter) || !empty($socialLinkedin) || !empty($socialYoutube);
?>
<footer class="site-footer">
    <!-- Decorative top edge -->
    <div class="footer-edge"></div>

    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <a href="<?= url('/') ?>" class="footer-logo-link">
                        <?php if (!empty($footerBranding['logo'])): ?>
                        <img src="<?= url($footerBranding['logo']) ?>" alt="<?= e(config('app.name')) ?>"
                             class="footer-logo-img" style="height: <?= e($footerBranding['logo_height']) ?>px; width: auto;">
                        <?php else: ?>
                        <span class="footer-logo-text"><?= e(config('app.name')) ?></span>
                        <?php endif; ?>
                    </a>
                    <p class="footer-description">
                        <?= e($footerAppearance['footer_description']) ?>
                    </p>
                    <?php if ($hasSocial): ?>
                    <div class="footer-social">
                        <?php if (!empty($socialFacebook)): ?>
                        <a href="<?= e($socialFacebook) ?>" class="footer-social-link" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($socialInstagram)): ?>
                        <a href="<?= e($socialInstagram) ?>" class="footer-social-link" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($socialTwitter)): ?>
                        <a href="<?= e($socialTwitter) ?>" class="footer-social-link" aria-label="X / Twitter" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($socialLinkedin)): ?>
                        <a href="<?= e($socialLinkedin) ?>" class="footer-social-link" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($socialYoutube)): ?>
                        <a href="<?= e($socialYoutube) ?>" class="footer-social-link" aria-label="YouTube" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Links -->
                <div class="footer-column">
                    <h4 class="footer-column-title">Shop</h4>
                    <nav class="footer-links">
                        <a href="<?= url('/categories') ?>" class="footer-link">All Categories</a>
                        <a href="<?= url('/categories') ?>" class="footer-link">New Arrivals</a>
                        <a href="<?= url('/categories') ?>" class="footer-link">Sale</a>
                        <a href="<?= url('/categories') ?>" class="footer-link">Best Sellers</a>
                    </nav>
                </div>

                <!-- Customer Service -->
                <div class="footer-column">
                    <h4 class="footer-column-title">Support</h4>
                    <nav class="footer-links">
                        <a href="<?= url('/page/shipping') ?>" class="footer-link">Shipping Info</a>
                        <a href="<?= url('/page/returns') ?>" class="footer-link">Returns & Refunds</a>
                        <a href="<?= url('/page/faq') ?>" class="footer-link">FAQ</a>
                        <a href="<?= url('/contact') ?>" class="footer-link">Contact Us</a>
                        <a href="<?= url('/page/track-order') ?>" class="footer-link">Track Order</a>
                    </nav>
                </div>

                <!-- Contact Info -->
                <div class="footer-column">
                    <h4 class="footer-column-title">Contact</h4>
                    <div class="footer-contact">
                        <?php if (!empty($footerAppearance['footer_email'])): ?>
                        <a href="mailto:<?= e($footerAppearance['footer_email']) ?>" class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <span><?= e($footerAppearance['footer_email']) ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($footerAppearance['footer_phone'])): ?>
                        <a href="tel:<?= e(preg_replace('/[^+0-9]/', '', $footerAppearance['footer_phone'])) ?>" class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            <span><?= e($footerAppearance['footer_phone']) ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($footerAppearance['footer_hours'])): ?>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span><?= e($footerAppearance['footer_hours']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner">
                <p class="footer-copyright">
                    <?php if (!empty($footerAppearance['footer_copyright'])): ?>
                    <?= e($footerAppearance['footer_copyright']) ?>
                    <?php else: ?>
                    &copy; <?= date('Y') ?> <?= e(config('app.name')) ?>. All rights reserved.
                    <?php endif; ?>
                </p>
                <div class="footer-bottom-links">
                    <a href="<?= url('/page/privacy') ?>" class="footer-bottom-link">Privacy Policy</a>
                    <span class="footer-bottom-sep"></span>
                    <a href="<?= url('/page/terms') ?>" class="footer-bottom-link">Terms of Service</a>
                </div>
                <div class="footer-payments">
                    <span class="footer-payments-label">Secure payments</span>
                    <div class="footer-payment-icons">
                        <svg class="footer-payment-icon" viewBox="0 0 50 30" width="40">
                            <rect fill="#1A1F71" width="50" height="30" rx="4"/>
                            <text fill="white" x="25" y="19" text-anchor="middle" font-size="10" font-weight="bold">VISA</text>
                        </svg>
                        <svg class="footer-payment-icon" viewBox="0 0 50 30" width="40">
                            <rect fill="#252525" width="50" height="30" rx="4"/>
                            <circle cx="20" cy="15" r="9" fill="#EB001B"/>
                            <circle cx="30" cy="15" r="9" fill="#F79E1B"/>
                            <path d="M25 8.5a9 9 0 000 13 9 9 0 000-13z" fill="#FF5F00"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
