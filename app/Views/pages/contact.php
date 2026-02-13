<!-- Contact Page -->

<!-- Breadcrumb Rings -->
<nav class="breadcrumb-rings container">
    <div class="breadcrumb-rings-list">
        <a href="<?= url('/') ?>" class="bc-ring-item">
            <div class="bc-ring-wrap">
                <div class="bc-ring-border"></div>
                <div class="bc-ring-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
            </div>
            <span class="bc-ring-label">Home</span>
        </a>
        <span class="bc-ring-sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
        <span class="bc-ring-item bc-ring-active">
            <div class="bc-ring-wrap">
                <div class="bc-ring-border"></div>
                <div class="bc-ring-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
            </div>
            <span class="bc-ring-label">Contact</span>
        </span>
    </div>
</nav>

<div class="container contact-page">
    <!-- Header -->
    <div class="contact-header">
        <h1 class="contact-title">Get in Touch</h1>
        <p class="contact-subtitle">Have a question or feedback? We'd love to hear from you.</p>
    </div>

    <!-- Info Cards -->
    <div class="contact-cards">
        <div class="contact-card">
            <div class="contact-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <h3 class="contact-card-title">Email</h3>
            <a href="mailto:info@pricetag.co.za" class="contact-card-link">info@pricetag.co.za</a>
            <p class="contact-card-note">We reply within 24 hours</p>
        </div>

        <div class="contact-card">
            <div class="contact-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                </svg>
            </div>
            <h3 class="contact-card-title">Phone</h3>
            <p class="contact-card-text">Coming soon</p>
            <p class="contact-card-note">Call us for quick support</p>
        </div>

        <div class="contact-card">
            <div class="contact-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <h3 class="contact-card-title">Business Hours</h3>
            <p class="contact-card-text">Mon – Fri: 9am – 5pm</p>
            <p class="contact-card-note">Sat: 9am – 1pm</p>
        </div>
    </div>

    <!-- Form -->
    <div class="contact-form-wrap">
        <div class="contact-form-header">
            <h2 class="contact-form-title">Send us a Message</h2>
            <div class="contact-form-shimmer"></div>
        </div>

        <?php if ($message = flash('error')): ?>
        <div class="contact-alert contact-alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($message = flash('success')): ?>
        <div class="contact-alert contact-alert-success"><?= e($message) ?></div>
        <?php endif; ?>

        <form action="<?= url('/contact') ?>" method="POST" class="contact-form">
            <?= csrfField() ?>

            <div class="contact-form-row">
                <div class="contact-form-group">
                    <label class="contact-label" for="name">Your Name *</label>
                    <input type="text" id="name" name="name" class="contact-input"
                           value="<?= e(old('name')) ?>" required placeholder="John Doe"
                           autocomplete="name">
                </div>
                <div class="contact-form-group">
                    <label class="contact-label" for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="contact-input"
                           value="<?= e(old('email')) ?>" required placeholder="john@example.com"
                           autocomplete="email">
                </div>
            </div>

            <div class="contact-form-row">
                <div class="contact-form-group">
                    <label class="contact-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="contact-input"
                           value="<?= e(old('phone')) ?>" placeholder="082 123 4567"
                           autocomplete="tel">
                </div>
                <div class="contact-form-group">
                    <label class="contact-label" for="subject">Subject</label>
                    <select id="subject" name="subject" class="contact-input contact-select">
                        <option value="">Select a topic</option>
                        <option value="Order Inquiry">Order Inquiry</option>
                        <option value="Product Question">Product Question</option>
                        <option value="Returns & Refunds">Returns & Refunds</option>
                        <option value="Technical Support">Technical Support</option>
                        <option value="Feedback">Feedback</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="contact-form-group">
                <label class="contact-label" for="message">Message *</label>
                <textarea id="message" name="message" rows="6" class="contact-input contact-textarea"
                          placeholder="How can we help you?" required><?= e(old('message')) ?></textarea>
            </div>

            <button type="submit" class="contact-submit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Send Message
            </button>
        </form>
    </div>
</div>

<style>
/* =========================================================================
   CONTACT PAGE
   ========================================================================= */
.contact-page {
    max-width: 900px;
    margin: 0 auto;
    padding-top: var(--space-6);
    padding-bottom: var(--space-12);
}

/* Header */
.contact-header {
    text-align: center;
    margin-bottom: var(--space-8);
}

.contact-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin-bottom: var(--space-2);
    letter-spacing: -0.02em;
}

.contact-subtitle {
    font-size: var(--text-lg);
    color: var(--color-text-muted);
}

/* Info Cards */
.contact-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-4);
    margin-bottom: var(--space-8);
}

.contact-card {
    text-align: center;
    padding: var(--space-6) var(--space-4);
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(255, 255, 255, 0.02);
    transition: all 0.3s ease;
}

.contact-card:hover {
    border-color: rgba(139, 43, 43, 0.3);
    background: rgba(255, 255, 255, 0.04);
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
}

.contact-card-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    padding: 2px;
    margin-bottom: var(--space-4);
    background: conic-gradient(
        from 0deg,
        rgba(80, 80, 100, 0.4),
        rgba(160, 160, 180, 0.7),
        rgba(220, 220, 240, 0.95),
        rgba(255, 255, 255, 1),
        rgba(220, 220, 240, 0.95),
        rgba(160, 160, 180, 0.7),
        rgba(80, 80, 100, 0.4),
        rgba(120, 120, 140, 0.5),
        rgba(80, 80, 100, 0.4)
    );
    animation: contactRingSpin 8s linear infinite;
}

@keyframes contactRingSpin {
    to { transform: rotate(360deg); }
}

.contact-card:hover .contact-card-icon {
    animation-duration: 3s;
}

.contact-card-icon svg {
    width: 52px;
    height: 52px;
    padding: 14px;
    border-radius: 50%;
    background: #1a1a24;
    color: var(--color-text-muted);
}

.contact-card:hover .contact-card-icon svg {
    color: var(--color-primary);
}

.contact-card-title {
    font-size: var(--text-base);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin-bottom: var(--space-2);
}

.contact-card-link {
    display: block;
    font-size: var(--text-sm);
    color: var(--color-primary);
    margin-bottom: var(--space-1);
    transition: color 0.2s ease;
}

.contact-card-link:hover {
    color: #e8a0a0;
}

.contact-card-text {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-1);
}

.contact-card-note {
    font-size: 12px;
    color: var(--color-text-muted);
}

/* Form Wrapper */
.contact-form-wrap {
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(255, 255, 255, 0.02);
    padding: var(--space-8);
    position: relative;
    overflow: hidden;
}

.contact-form-header {
    position: relative;
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-4);
}

.contact-form-title {
    font-size: var(--text-xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
}

.contact-form-shimmer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg,
        transparent,
        rgba(120, 120, 140, 0.3),
        rgba(200, 200, 220, 0.6),
        rgba(255, 255, 255, 0.9),
        rgba(200, 200, 220, 0.6),
        rgba(120, 120, 140, 0.3),
        transparent);
    background-size: 200% 100%;
    animation: contactShimmer 4s linear infinite;
}

@keyframes contactShimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}

/* Alerts */
.contact-alert {
    padding: var(--space-4);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-6);
    font-size: var(--text-sm);
    font-weight: 500;
}

.contact-alert-error {
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    color: #fca5a5;
}

.contact-alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #86efac;
}

/* Form */
.contact-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

.contact-form-group {
    margin-bottom: var(--space-5);
}

.contact-label {
    display: block;
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-2);
}

.contact-input {
    width: 100%;
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.04);
    color: var(--color-text);
    font-size: var(--text-sm);
    font-family: inherit;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    outline: none;
}

.contact-input::placeholder {
    color: var(--color-text-muted);
}

.contact-input:focus {
    border-color: rgba(139, 43, 43, 0.5);
    box-shadow: 0 0 0 3px rgba(139, 43, 43, 0.1);
}

.contact-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%2394a3b8' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
}

.contact-textarea {
    resize: vertical;
    min-height: 140px;
}

/* Submit Button */
.contact-submit {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-6);
    border-radius: var(--radius-lg);
    border: none;
    background: var(--color-primary);
    color: #fff;
    font-size: var(--text-sm);
    font-weight: var(--font-bold);
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
}

.contact-submit:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(139, 43, 43, 0.3);
}

.contact-submit:active {
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .contact-cards {
        grid-template-columns: 1fr;
        gap: var(--space-3);
    }

    .contact-card {
        display: flex;
        align-items: center;
        gap: var(--space-4);
        text-align: left;
        padding: var(--space-4) var(--space-5);
    }

    .contact-card-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .contact-card-icon svg {
        width: 44px;
        height: 44px;
        padding: 10px;
    }

    .contact-form-wrap {
        padding: var(--space-5);
    }

    .contact-form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .contact-title {
        font-size: var(--text-2xl);
    }
}
</style>
