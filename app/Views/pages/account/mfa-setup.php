<!-- Account - MFA Setup -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <div class="mfa-setup-container">
                    <div class="mfa-setup-header">
                        <h1 class="account-page-title">Set Up Two-Factor Authentication</h1>
                        <p class="text-muted">Add an extra layer of security to your account using an authenticator app.</p>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="mfa-setup-steps">
                                <!-- Step 1 -->
                                <div class="mfa-setup-step">
                                    <div class="mfa-setup-step-number">1</div>
                                    <div class="mfa-setup-step-content">
                                        <h3>Download an authenticator app</h3>
                                        <p class="text-muted">
                                            Download and install an authenticator app on your phone if you haven't already.
                                        </p>
                                        <div class="mfa-app-links">
                                            <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="mfa-app-link">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M3.609 1.814L13.792 12 3.61 22.186a2.372 2.372 0 0 1-.473-.596L.473 16.28c-.632-1.04-.632-2.518 0-3.558L3.136 7.41c.123-.203.28-.396.473-.596zM14.707 12.915l3.014 3.014-11.35 6.528c-.474.274-.957.437-1.407.496l9.743-10.038zm3.014-3.844l-3.014 3.014-9.743-10.038c.45.06.933.223 1.407.496l11.35 6.528zm.915.915l3.014 3.014c.63.63.63 1.35 0 1.98l-3.014 3.014-3.93-3.93 3.93-4.078z"/>
                                                </svg>
                                                Google Authenticator
                                            </a>
                                            <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="mfa-app-link">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                                                </svg>
                                                Google Authenticator
                                            </a>
                                            <a href="https://authy.com/download/" target="_blank" class="mfa-app-link">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10zm-1-15v6l5 3-1 1.7-6-3.7V7h2z"/>
                                                </svg>
                                                Authy
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2 -->
                                <div class="mfa-setup-step">
                                    <div class="mfa-setup-step-number">2</div>
                                    <div class="mfa-setup-step-content">
                                        <h3>Scan the QR code</h3>
                                        <p class="text-muted">
                                            Open your authenticator app and scan the QR code below, or manually enter the setup key.
                                        </p>
                                        <div class="mfa-qr-container">
                                            <div class="mfa-qr-code">
                                                <img src="<?= e($qrCodeUrl) ?>" alt="QR Code for 2FA setup">
                                            </div>
                                            <div class="mfa-manual-key">
                                                <span class="text-sm text-muted">Or enter this code manually:</span>
                                                <code class="mfa-secret-key"><?= e(chunk_split($secret, 4, ' ')) ?></code>
                                                <button type="button" class="btn btn-sm btn-ghost" onclick="copySecret()">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                                    </svg>
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3 -->
                                <div class="mfa-setup-step">
                                    <div class="mfa-setup-step-number">3</div>
                                    <div class="mfa-setup-step-content">
                                        <h3>Enter verification code</h3>
                                        <p class="text-muted">
                                            Enter the 6-digit code from your authenticator app to verify setup.
                                        </p>
                                        <form action="<?= url('/account/mfa/enable') ?>" method="POST" class="mfa-verify-form">
                                            <?= csrfField() ?>
                                            <div class="form-group">
                                                <input type="text" name="code" class="form-input mfa-code-input"
                                                       placeholder="000000" maxlength="6" pattern="[0-9]{6}"
                                                       autocomplete="one-time-code" inputmode="numeric" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                Verify and Enable
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <a href="<?= url('/account/security') ?>" class="text-muted">Cancel and go back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
/* MFA Setup */
.mfa-setup-container {
    max-width: 600px;
}

.mfa-setup-header {
    margin-bottom: 1.5rem;
}

.mfa-setup-steps {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.mfa-setup-step {
    display: flex;
    gap: 1rem;
}

.mfa-setup-step-number {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary);
    color: white;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.mfa-setup-step-content h3 {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}

.mfa-setup-step-content p {
    margin: 0 0 1rem;
}

.mfa-app-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.mfa-app-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: var(--color-background-alt);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    color: var(--color-text);
    transition: all 0.2s;
}

.mfa-app-link:hover {
    background: var(--color-neutral-200);
}

.mfa-app-link svg {
    width: 1rem;
    height: 1rem;
}

.mfa-qr-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--color-background-alt);
    border-radius: var(--radius-lg);
}

.mfa-qr-code {
    padding: 1rem;
    background: white;
    border-radius: var(--radius-md);
}

.mfa-qr-code img {
    display: block;
    width: 180px;
    height: 180px;
}

.mfa-manual-key {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.mfa-secret-key {
    font-family: monospace;
    font-size: 1rem;
    letter-spacing: 0.1em;
    background: var(--color-background);
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
}

.mfa-verify-form {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.mfa-code-input {
    font-family: monospace;
    font-size: 1.5rem;
    letter-spacing: 0.3em;
    text-align: center;
    max-width: 180px;
}
</style>

<script>
function copySecret() {
    const secret = '<?= e($secret) ?>';
    navigator.clipboard.writeText(secret).then(() => {
        window.Pricetag?.Toast?.success('Secret key copied to clipboard');
    });
}

// Auto-submit when 6 digits entered
document.querySelector('.mfa-code-input')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length === 6) {
        this.form.submit();
    }
});
</script>
