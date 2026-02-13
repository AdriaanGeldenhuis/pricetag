<section class="newsletter-section">
    <div class="newsletter-bg-effects">
        <div class="newsletter-orb newsletter-orb-1"></div>
        <div class="newsletter-orb newsletter-orb-2"></div>
        <div class="newsletter-orb newsletter-orb-3"></div>
        <div class="newsletter-grid-lines"></div>
    </div>
    <div class="container">
        <div class="newsletter-content">
            <div class="newsletter-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span>Free to join</span>
            </div>
            <h2 class="newsletter-title"><?= e($sectionTitle ?? 'Stay in the') ?> <span class="newsletter-title-accent"><?= e($sectionAccent ?? 'Loop') ?></span></h2>
            <p class="newsletter-text"><?= e($sectionSubtitle ?? 'Get exclusive deals, new arrivals, and insider-only discounts delivered straight to your inbox.') ?></p>
            <form id="newsletter-form" class="newsletter-form" action="<?= url('/newsletter/subscribe') ?>" method="POST">
                <?= csrfField() ?>
                <div class="newsletter-input-group">
                    <div class="newsletter-input-wrapper">
                        <svg class="newsletter-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input type="email" name="email" id="newsletter-email"
                               placeholder="Enter your email address" required
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                               class="newsletter-input" aria-label="Email address"
                               autocomplete="email">
                    </div>
                    <button type="submit" class="newsletter-btn">
                        <span class="newsletter-btn-text">Subscribe</span>
                        <svg class="newsletter-btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                        <span class="newsletter-btn-loading" style="display: none;">
                            <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" opacity="0.25"></circle>
                                <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"></path>
                            </svg>
                        </span>
                    </button>
                </div>
                <p class="newsletter-error" id="newsletter-error" style="display: none;"></p>
                <p class="newsletter-success" id="newsletter-success" style="display: none;">Thanks for subscribing!</p>
            </form>
            <div class="newsletter-trust">
                <div class="newsletter-trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>No spam, ever</span>
                </div>
                <div class="newsletter-trust-divider"></div>
                <div class="newsletter-trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Privacy respected</span>
                </div>
                <div class="newsletter-trust-divider"></div>
                <div class="newsletter-trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Unsubscribe anytime</span>
                </div>
            </div>
        </div>
    </div>
</section>
