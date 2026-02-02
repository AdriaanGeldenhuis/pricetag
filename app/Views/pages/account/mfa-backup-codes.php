<!-- Account - MFA Backup Codes -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <div class="backup-codes-container">
                    <?php if (isset($regenerated) && $regenerated): ?>
                    <div class="backup-codes-header">
                        <div class="backup-codes-icon success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <h1 class="account-page-title">New Backup Codes Generated</h1>
                        <p class="text-muted">Your previous backup codes have been invalidated. Save these new codes in a safe place.</p>
                    </div>
                    <?php else: ?>
                    <div class="backup-codes-header">
                        <div class="backup-codes-icon success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                <polyline points="9 12 12 15 22 5" style="transform-origin: 12px 12px; transform: scale(0.6) translate(2px, 5px);"></polyline>
                            </svg>
                        </div>
                        <h1 class="account-page-title">Two-Factor Authentication Enabled!</h1>
                        <p class="text-muted">Your account is now more secure. Save these backup codes in case you lose access to your authenticator app.</p>
                    </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Backup Codes</h2>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-4">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                    <line x1="12" y1="9" x2="12" y2="13"></line>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                                <span>
                                    <strong>Save these codes now!</strong><br>
                                    These codes will not be shown again. Store them somewhere safe.
                                </span>
                            </div>

                            <div class="backup-codes-grid" id="backup-codes">
                                <?php foreach ($backupCodes as $code): ?>
                                <div class="backup-code"><?= e($code) ?></div>
                                <?php endforeach; ?>
                            </div>

                            <div class="backup-codes-actions">
                                <button type="button" class="btn btn-outline" onclick="copyBackupCodes()">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                    Copy Codes
                                </button>
                                <button type="button" class="btn btn-outline" onclick="downloadBackupCodes()">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    Download
                                </button>
                                <button type="button" class="btn btn-outline" onclick="printBackupCodes()">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print
                                </button>
                            </div>

                            <div class="backup-codes-info">
                                <h4>How to use backup codes:</h4>
                                <ul>
                                    <li>Each code can only be used once</li>
                                    <li>Use a backup code if you can't access your authenticator app</li>
                                    <li>When you run low on codes, generate new ones from Security settings</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="<?= url('/account/security') ?>" class="btn btn-primary">
                            Continue to Security Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
/* Backup Codes */
.backup-codes-container {
    max-width: 600px;
}

.backup-codes-header {
    text-align: center;
    margin-bottom: 2rem;
}

.backup-codes-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.backup-codes-icon.success {
    background: var(--color-success-100);
    color: var(--color-success);
}

.backup-codes-icon svg {
    width: 2rem;
    height: 2rem;
}

.backup-codes-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 480px) {
    .backup-codes-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.backup-code {
    font-family: monospace;
    font-size: 1rem;
    letter-spacing: 0.05em;
    padding: 0.75rem 1rem;
    background: var(--color-background-alt);
    border-radius: var(--radius-md);
    text-align: center;
}

.backup-codes-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.backup-codes-info {
    padding: 1rem;
    background: var(--color-background-alt);
    border-radius: var(--radius-md);
}

.backup-codes-info h4 {
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}

.backup-codes-info ul {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.875rem;
    color: var(--color-text-muted);
}

.backup-codes-info li {
    margin-bottom: 0.25rem;
}

@media print {
    .backup-codes-actions,
    .account-sidebar,
    .mt-6 {
        display: none !important;
    }

    .account-layout {
        display: block;
    }

    .backup-codes-container {
        max-width: none;
    }

    .card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<script>
const backupCodes = <?= json_encode($backupCodes) ?>;

function copyBackupCodes() {
    const text = backupCodes.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        window.Pricetag?.Toast?.success('Backup codes copied to clipboard');
    });
}

function downloadBackupCodes() {
    const text = `<?= e(config('app.name')) ?> Backup Codes\n${'='.repeat(30)}\n\nSave these codes in a safe place.\nEach code can only be used once.\n\n${backupCodes.join('\n')}\n\nGenerated: ${new Date().toISOString()}`;

    const blob = new Blob([text], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'pricetag-backup-codes.txt';
    a.click();
    URL.revokeObjectURL(url);
}

function printBackupCodes() {
    window.print();
}
</script>
