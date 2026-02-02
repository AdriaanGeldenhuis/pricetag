<!-- Account - Security -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <!-- Page Header -->
                <div class="account-page-header">
                    <div>
                        <h1 class="account-page-title">Security</h1>
                        <p class="text-muted text-sm">Manage your account security and active sessions</p>
                    </div>
                </div>

                <!-- Security Warnings -->
                <?php if (!empty($warnings)): ?>
                <?php foreach ($warnings as $warning): ?>
                <div class="security-warning">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div class="security-warning-content">
                        <p class="security-warning-title"><?= e($warning['title']) ?></p>
                        <p class="security-warning-text"><?= e($warning['message']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- Two-Factor Authentication -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold">Two-Factor Authentication</h2>
                            <p class="text-sm text-muted">Add an extra layer of security to your account</p>
                        </div>
                        <?php if ($mfaEnabled): ?>
                        <span class="badge badge-success">Enabled</span>
                        <?php else: ?>
                        <span class="badge badge-warning">Disabled</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($mfaEnabled): ?>
                        <div class="mfa-status">
                            <div class="mfa-status-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    <polyline points="9 12 12 15 22 5" style="transform-origin: 12px 12px; transform: scale(0.6) translate(2px, 5px);"></polyline>
                                </svg>
                            </div>
                            <div class="mfa-status-content">
                                <h3>Two-factor authentication is enabled</h3>
                                <p class="text-muted text-sm">Your account is protected with an authenticator app.</p>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-4">
                            <button type="button" class="btn btn-outline" onclick="openBackupCodesModal()">
                                Regenerate Backup Codes
                            </button>
                            <button type="button" class="btn btn-ghost text-danger" onclick="openDisableMfaModal()">
                                Disable 2FA
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="mfa-promo">
                            <div class="mfa-promo-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <div class="mfa-promo-content">
                                <h3>Protect your account with 2FA</h3>
                                <p class="text-muted text-sm mb-4">
                                    Use an authenticator app like Google Authenticator or Authy to add an extra layer of security.
                                </p>
                                <a href="<?= url('/account/mfa/setup') ?>" class="btn btn-primary">
                                    Enable Two-Factor Authentication
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Active Sessions -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold">Active Sessions</h2>
                            <p class="text-sm text-muted">Manage your active login sessions</p>
                        </div>
                        <?php if (count($sessions) > 1): ?>
                        <form action="<?= url('/account/sessions/terminate-others') ?>" method="POST">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-sm btn-ghost text-danger">
                                Sign Out Other Sessions
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="sessions-list">
                            <?php foreach ($sessions as $session): ?>
                            <div class="session-item <?= $session['is_current'] ? 'current' : '' ?>">
                                <div class="session-icon">
                                    <?php if (strpos($session['device_type'] ?? '', 'mobile') !== false): ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                    </svg>
                                    <?php else: ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                        <line x1="8" y1="21" x2="16" y2="21"></line>
                                        <line x1="12" y1="17" x2="12" y2="21"></line>
                                    </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="session-content">
                                    <p class="session-title">
                                        <?= e($session['browser'] ?? 'Unknown Browser') ?> on <?= e($session['os'] ?? 'Unknown OS') ?>
                                        <?php if ($session['is_current']): ?>
                                        <span class="session-current-badge">Current</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="session-details">
                                        <?= e($session['ip_address'] ?? 'Unknown IP') ?>
                                        &middot;
                                        <?php if ($session['is_current']): ?>
                                        Active now
                                        <?php else: ?>
                                        Last active <?= timeAgo($session['last_activity']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($session['location'])): ?>
                                        &middot; <?= e($session['location']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php if (!$session['is_current']): ?>
                                <form action="<?= url('/account/sessions/' . $session['id'] . '/terminate') ?>" method="POST">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-ghost">
                                        Sign Out
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Login Activity -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold">Recent Login Activity</h2>
                            <p class="text-sm text-muted">Your recent sign-in history</p>
                        </div>
                        <a href="<?= url('/account/activity') ?>" class="text-primary text-sm">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="login-history">
                            <?php if (empty($loginHistory)): ?>
                            <div class="empty-state py-6">
                                <p class="text-muted">No login history available</p>
                            </div>
                            <?php else: ?>
                            <table class="login-history-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>IP Address</th>
                                        <th>Device</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($loginHistory as $login): ?>
                                    <tr>
                                        <td><?= date('d M Y, H:i', strtotime($login['created_at'])) ?></td>
                                        <td><?= e($login['ip_address']) ?></td>
                                        <td><?= e($login['browser'] ?? 'Unknown') ?></td>
                                        <td>
                                            <?php if ($login['success']): ?>
                                            <span class="text-success text-sm">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                                Success
                                            </span>
                                            <?php else: ?>
                                            <span class="text-danger text-sm">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                                Failed
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable MFA Modal -->
<div class="modal-overlay" id="disable-mfa-modal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Disable Two-Factor Authentication</h2>
            <button type="button" class="modal-close" onclick="closeDisableMfaModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form action="<?= url('/account/mfa/disable') ?>" method="POST">
            <?= csrfField() ?>
            <div class="modal-body">
                <p class="text-muted mb-4">
                    This will remove the extra layer of security from your account. Enter your password to confirm.
                </p>
                <div class="form-group mb-0">
                    <label for="mfa_password" class="form-label required">Password</label>
                    <input type="password" id="mfa_password" name="password" class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeDisableMfaModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Disable 2FA</button>
            </div>
        </form>
    </div>
</div>

<!-- Regenerate Backup Codes Modal -->
<div class="modal-overlay" id="backup-codes-modal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Regenerate Backup Codes</h2>
            <button type="button" class="modal-close" onclick="closeBackupCodesModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form action="<?= url('/account/mfa/backup-codes') ?>" method="POST">
            <?= csrfField() ?>
            <div class="modal-body">
                <p class="text-muted mb-4">
                    This will invalidate your current backup codes and generate new ones. Enter your password to confirm.
                </p>
                <div class="form-group mb-0">
                    <label for="backup_password" class="form-label required">Password</label>
                    <input type="password" id="backup_password" name="password" class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeBackupCodesModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate New Codes</button>
            </div>
        </form>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
/* MFA Status */
.mfa-status,
.mfa-promo {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.mfa-status-icon,
.mfa-promo-icon {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    flex-shrink: 0;
}

.mfa-status-icon {
    background: var(--color-success-100);
    color: var(--color-success);
}

.mfa-promo-icon {
    background: var(--color-primary-100);
    color: var(--color-primary);
}

.mfa-status-icon svg,
.mfa-promo-icon svg {
    width: 1.5rem;
    height: 1.5rem;
}

.mfa-status-content h3,
.mfa-promo-content h3 {
    font-weight: 600;
    margin: 0 0 0.25rem;
}

/* Login History Table */
.login-history-table {
    width: 100%;
    border-collapse: collapse;
}

.login-history-table th,
.login-history-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.login-history-table th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--color-text-muted);
    background: var(--color-background-alt);
}

/* Badge */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: var(--color-success-100);
    color: var(--color-success-700);
}

.badge-warning {
    background: var(--color-warning-100);
    color: var(--color-warning-700);
}
</style>

<script>
function openDisableMfaModal() {
    document.getElementById('disable-mfa-modal').classList.add('open');
}

function closeDisableMfaModal() {
    document.getElementById('disable-mfa-modal').classList.remove('open');
}

function openBackupCodesModal() {
    document.getElementById('backup-codes-modal').classList.add('open');
}

function closeBackupCodesModal() {
    document.getElementById('backup-codes-modal').classList.remove('open');
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('open');
        }
    });
});

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(modal => {
            modal.classList.remove('open');
        });
    }
});
</script>
