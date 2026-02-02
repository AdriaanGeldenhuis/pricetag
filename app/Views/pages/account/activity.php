<!-- Account - Activity Log -->
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
                        <div class="account-breadcrumb">
                            <a href="<?= url('/account') ?>">Account</a>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                            <a href="<?= url('/account/security') ?>">Security</a>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                            <span>Activity Log</span>
                        </div>
                        <h1 class="account-page-title">Activity Log</h1>
                        <p class="text-muted text-sm">Your complete account activity history</p>
                    </div>
                </div>

                <!-- Activity List -->
                <div class="card">
                    <div class="card-body p-0">
                        <?php if (empty($activities)): ?>
                        <div class="empty-state py-8">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <h3>No activity yet</h3>
                            <p class="text-muted">Your account activity will appear here</p>
                        </div>
                        <?php else: ?>
                        <div class="activity-list">
                            <?php
                            $currentDate = null;
                            foreach ($activities as $activity):
                                $activityDate = date('Y-m-d', strtotime($activity['created_at']));
                                if ($activityDate !== $currentDate):
                                    $currentDate = $activityDate;
                            ?>
                            <div class="activity-date-header">
                                <?php if ($activityDate === date('Y-m-d')): ?>
                                    Today
                                <?php elseif ($activityDate === date('Y-m-d', strtotime('-1 day'))): ?>
                                    Yesterday
                                <?php else: ?>
                                    <?= date('l, d F Y', strtotime($activity['created_at'])) ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <div class="activity-item">
                                <div class="activity-icon <?= getActivityIconClass($activity['type']) ?>">
                                    <?= getActivityIcon($activity['type']) ?>
                                </div>
                                <div class="activity-content">
                                    <p class="activity-title"><?= e($activity['description']) ?></p>
                                    <p class="activity-meta">
                                        <span class="activity-time"><?= date('H:i', strtotime($activity['created_at'])) ?></span>
                                        <?php if (!empty($activity['ip_address'])): ?>
                                        <span class="activity-ip"><?= e($activity['ip_address']) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function getActivityIconClass($type) {
    $classes = [
        'login' => 'icon-success',
        'login_failed' => 'icon-danger',
        'logout' => 'icon-neutral',
        'password_changed' => 'icon-warning',
        'mfa_enabled' => 'icon-success',
        'mfa_disabled' => 'icon-warning',
        'sessions_terminated' => 'icon-danger',
        'profile_updated' => 'icon-primary',
        'address_added' => 'icon-primary',
        'address_deleted' => 'icon-danger',
    ];
    return $classes[$type] ?? 'icon-neutral';
}

function getActivityIcon($type) {
    $icons = [
        'login' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>',
        'login_failed' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        'logout' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>',
        'password_changed' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
        'mfa_enabled' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
        'mfa_disabled' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
        'sessions_terminated' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
        'profile_updated' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        'address_added' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
        'address_deleted' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
    ];
    return $icons[$type] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg>';
}
?>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
}

.activity-date-header {
    padding: 0.75rem 1.5rem;
    background: var(--color-background-alt);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--color-text-muted);
    border-bottom: 1px solid var(--color-border);
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--color-border);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    flex-shrink: 0;
}

.activity-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}

.activity-icon.icon-success {
    background: var(--color-success-100);
    color: var(--color-success);
}

.activity-icon.icon-danger {
    background: var(--color-danger-100);
    color: var(--color-danger);
}

.activity-icon.icon-warning {
    background: var(--color-warning-100);
    color: var(--color-warning);
}

.activity-icon.icon-primary {
    background: var(--color-primary-100);
    color: var(--color-primary);
}

.activity-icon.icon-neutral {
    background: var(--color-neutral-100);
    color: var(--color-text-muted);
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    margin: 0 0 0.25rem;
}

.activity-meta {
    display: flex;
    gap: 0.75rem;
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin: 0;
}
</style>
