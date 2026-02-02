<!-- Site Footer -->
<footer class="site-footer">
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <div class="footer-logo"><?= e(config('app.name')) ?></div>
                    <p class="footer-description">
                        Your trusted destination for premium products at competitive prices.
                        Fast delivery across South Africa.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="footer-social-link" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                            </svg>
                        </a>
                        <a href="#" class="footer-social-link" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="5"/>
                                <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                            </svg>
                        </a>
                        <a href="#" class="footer-social-link" aria-label="Twitter">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-column">
                    <h4 class="footer-column-title">Shop</h4>
                    <nav class="footer-links">
                        <a href="<?= url('/products') ?>" class="footer-link">All Products</a>
                        <a href="<?= url('/products?sort=newest') ?>" class="footer-link">New Arrivals</a>
                        <a href="<?= url('/products?on_sale=1') ?>" class="footer-link">Sale</a>
                        <a href="<?= url('/products?sort=popular') ?>" class="footer-link">Best Sellers</a>
                        <a href="<?= url('/categories') ?>" class="footer-link">Categories</a>
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
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <span>info@pricetag.co.za</span>
                        </div>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            <span>+27 (0) 10 000 0000</span>
                        </div>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>Mon - Fri: 8am - 5pm</span>
                        </div>
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
                    &copy; <?= date('Y') ?> <?= e(config('app.name')) ?>. All rights reserved.
                    <a href="<?= url('/page/privacy') ?>" class="footer-link">Privacy Policy</a> |
                    <a href="<?= url('/page/terms') ?>" class="footer-link">Terms of Service</a>
                </p>
                <div class="footer-payments">
                    <span class="text-sm text-muted">Secure payments with</span>
                    <svg class="footer-payment-icon" viewBox="0 0 50 30" width="40">
                        <rect fill="#1A1F71" width="50" height="30" rx="3"/>
                        <text fill="white" x="25" y="19" text-anchor="middle" font-size="10" font-weight="bold">VISA</text>
                    </svg>
                    <svg class="footer-payment-icon" viewBox="0 0 50 30" width="40">
                        <rect fill="#EB001B" width="50" height="30" rx="3"/>
                        <circle cx="20" cy="15" r="10" fill="#EB001B"/>
                        <circle cx="30" cy="15" r="10" fill="#F79E1B"/>
                        <path d="M25 8a10 10 0 000 14 10 10 0 000-14z" fill="#FF5F00"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</footer>
